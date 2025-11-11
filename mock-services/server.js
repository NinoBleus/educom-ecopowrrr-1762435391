// server.js
import express from "express";
import crypto from "crypto";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const app = express();
app.use(express.json());

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const datasetPath =
  process.env.DATASET_PATH || path.join(__dirname, "data", "devices.json");

const deviceConfigs = loadDataset(datasetPath);
if (deviceConfigs.length === 0) {
  throw new Error(`Dataset at ${datasetPath} is empty, cannot mock devices.`);
}

const deviceRegistry = new Map(
  deviceConfigs.map(cfg => [cfg.device_id, createInitialState(cfg)])
);
const defaultDeviceId = deviceConfigs[0].device_id;

app.get("/devices", (_req, res) => {
  res.json(
    deviceConfigs.map(cfg => ({
      device_id: cfg.device_id,
      postcode: cfg.postcode,
      house_number: cfg.house_number,
      municipality: cfg.municipality,
      tariffs: cfg.tariffs,
      device_count: cfg.devices.length
    }))
  );
});

app.post("/activate", (req, res) => {
  const ctx = resolveDevice(req, res);
  if (!ctx) return;
  ctx.status = "active";
  ctx.lastActivation = new Date().toISOString();
  res.json({ ack: true, status: ctx.status, device_id: ctx.config.device_id });
});

app.post("/deactivate", (req, res) => {
  const ctx = resolveDevice(req, res);
  if (!ctx) return;
  ctx.status = "inactive";
  ctx.lastDeactivation = new Date().toISOString();
  res.json({ ack: true, status: ctx.status, device_id: ctx.config.device_id });
});

app.get("/telemetry", (req, res) => {
  const ctx = resolveDevice(req, res);
  if (!ctx) return;

  const now = new Date();
  const sinceCandidate = req.query.since ? new Date(req.query.since) : ctx.lastReadingAt;
  const since =
    sinceCandidate && !Number.isNaN(sinceCandidate.getTime())
      ? sinceCandidate
      : new Date(now.getTime() - 15 * 60 * 1000);
  const minutes = Math.max(1, Math.floor((now - since) / 60000));

  const periodStats = simulatePeriod(ctx, minutes, now);

  const payload = {
    message_id: crypto.randomBytes(8).toString("hex"),
    device_id: ctx.config.device_id,
    device_status: ctx.status,
    date: now.toISOString(),
    total_usage: ctx.lastTotalUsage.toFixed(3),
    total_production: ctx.metrics.totalProduction.toFixed(3),
    total_consumption: ctx.metrics.totalConsumption.toFixed(3),
    net_surplus: ctx.metrics.netSurplus.toFixed(3),
    lifetime_revenue_eur: ctx.metrics.revenue.toFixed(2),
    period: {
      minutes,
      production_kwh: periodStats.production.toFixed(3),
      consumption_kwh: periodStats.consumption.toFixed(3),
      net_surplus_kwh: periodStats.net.toFixed(3),
      revenue_eur: periodStats.revenue.toFixed(2)
    },
    pricing: ctx.config.tariffs,
    address: {
      postcode: ctx.config.postcode,
      house_number: ctx.config.house_number,
      municipality: ctx.config.municipality,
      geo: ctx.config.geo
    },
    devices: ctx.devices.map(sub => ({
      serial_number: sub.serial_number,
      device_type: sub.device_type,
      role: sub.role,
      device_status: sub.status,
      device_total_yield: sub.total_kwh.toFixed(3),
      device_month_yield: sub.month_kwh.toFixed(3)
    }))
  };

  res.json(payload);
});

function resolveDevice(req, res) {
  const deviceId = extractDeviceId(req);
  const ctx = deviceRegistry.get(deviceId);
  if (!ctx) {
    res.status(404).json({ message: `Unknown device: ${deviceId}` });
    return null;
  }
  return ctx;
}

function extractDeviceId(req) {
  const paramId = req.params?.deviceId || req.params?.device_id;
  const bodyId = req.body?.device_id;
  const queryId = req.query.device_id || req.query.deviceId;
  const postcode = req.body?.postcode || req.query.postcode;
  const houseNumber =
    req.body?.huisnr ||
    req.body?.house_number ||
    req.query.house_number ||
    req.query.huisnr;

  const resolvedByAddress =
    postcode && houseNumber ? findDeviceIdByAddress(postcode, houseNumber) : null;

  return paramId || bodyId || queryId || resolvedByAddress || defaultDeviceId;
}

function findDeviceIdByAddress(postcode, houseNumber) {
  const normalizedPostcode = String(postcode).replace(/\s+/g, "").toUpperCase();
  const normalizedHouseNumber = String(houseNumber).toUpperCase();
  const match = deviceConfigs.find(
    cfg =>
      cfg.postcode.replace(/\s+/g, "").toUpperCase() === normalizedPostcode &&
      String(cfg.house_number).toUpperCase() === normalizedHouseNumber
  );
  return match?.device_id;
}

function simulatePeriod(ctx, minutes, now) {
  const hours = minutes / 60;
  let production = 0;
  let consumption = 0;

  ctx.devices.forEach(sub => {
    rollMonth(sub, now);
    if (sub.role === "producer") {
      const delta = Math.max(0, computeProducerDelta(sub, ctx, hours, now));
      sub.total_kwh += delta;
      sub.month_kwh += delta;
      production += delta;
    } else {
      const delta = Math.max(0, computeConsumerDelta(sub, ctx, hours, now));
      sub.total_kwh += delta;
      sub.month_kwh += delta;
      consumption += delta;
    }
  });

  ctx.metrics.totalProduction += production;
  ctx.metrics.totalConsumption += consumption;
  ctx.lastTotalUsage += consumption;

  const net = production - consumption;
  ctx.metrics.netSurplus += net;

  const tariff =
    net >= 0 ? ctx.config.tariffs.sell_eur_per_kwh : ctx.config.tariffs.buy_eur_per_kwh;
  const revenue = net * tariff;
  ctx.metrics.revenue += revenue;
  ctx.lastReadingAt = now;

  return { production, consumption, net, revenue };
}

function computeProducerDelta(sub, ctx, hours, now) {
  switch (sub.device_type) {
    case "solar":
      return solarYield(sub, ctx, hours, now);
    case "wind":
      return windYield(sub, ctx, hours, now);
    case "battery":
      return batteryDischarge(sub, ctx, hours, now);
    default:
      return hours *
        (sub.peak_kw || 0.2) *
        jitter(
          `${ctx.config.device_id}:${sub.serial_number}:${now.getUTCHours()}`,
          0.5,
          0.9
        );
  }
}

function computeConsumerDelta(sub, ctx, hours, now) {
  const demand = demandFactor(now.getUTCHours());
  const season = consumptionSeasonFactor(now.getUTCMonth());
  const base = sub.baseline_kw ?? 0.4;
  const swing = sub.swing_kw ?? 0.2;
  const usageKw =
    base * demand * season +
    swing *
      jitter(
        `${ctx.config.device_id}:${sub.serial_number}:${now.toISOString().slice(0, 13)}`,
        0.3,
        1.1
      );
  return usageKw * hours;
}

function solarYield(sub, ctx, hours, now) {
  const daylight = solarDaylightFactor(now, ctx.config.geo?.lat ?? 52);
  const seasonal = productionSeasonFactor(now.getUTCMonth());
  const weather = jitter(
    `${ctx.config.device_id}:solar:${now.toISOString().slice(0, 10)}`,
    0.7,
    1.0
  );
  const peak = sub.peak_kw ?? 4;
  return peak * daylight * seasonal * weather * hours;
}

function windYield(sub, ctx, hours, now) {
  const gust = jitter(
    `${ctx.config.device_id}:wind:${now.toISOString().slice(0, 13)}`,
    0.4,
    1.1
  );
  const seasonal = 0.8 + 0.2 * Math.cos(((now.getUTCMonth() + 1) / 12) * Math.PI * 2);
  const peak = sub.peak_kw ?? 2.5;
  return peak * gust * seasonal * hours;
}

function batteryDischarge(sub, ctx, hours, now) {
  const hour = now.getUTCHours();
  const dischargeWindow = hour >= 18 || hour <= 5 ? 1 : 0.3;
  const maxDischarge = sub.max_discharge_kw ?? 1.5;
  const base = maxDischarge * dischargeWindow * hours;
  const amount =
    base *
    jitter(
      `${ctx.config.device_id}:battery:${hour}`,
      0.6,
      1.0
    );
  const capacity = sub.capacity_kwh ?? 10;
  return Math.min(capacity * 0.1, amount);
}

function solarDaylightFactor(now, latitude) {
  const hour = now.getUTCHours() + now.getUTCMinutes() / 60;
  const daylightWindow = 12 + Math.cos((latitude / 90) * Math.PI) * 4;
  const sunrise = 12 - daylightWindow / 2;
  const normalized = (hour - sunrise) / daylightWindow;
  if (normalized <= 0 || normalized >= 1) return 0;
  return Math.sin(Math.PI * normalized);
}

function demandFactor(hour) {
  if (hour < 6) return 0.4;
  if (hour < 9) return 0.9;
  if (hour < 17) return 0.6;
  if (hour < 21) return 1.0;
  return 0.7;
}

function productionSeasonFactor(month) {
  return 0.6 + 0.4 * Math.cos(((month - 6) / 6) * Math.PI);
}

function consumptionSeasonFactor(month) {
  return 0.9 + 0.2 * Math.cos(((month + 2) / 6) * Math.PI);
}

function rollMonth(device, now) {
  if (device.month_index !== now.getUTCMonth()) {
    device.month_index = now.getUTCMonth();
    device.month_kwh = 0;
  }
}

function loadDataset(filePath) {
  try {
    const raw = fs.readFileSync(filePath, "utf-8");
    const parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) {
      throw new Error("Dataset must be an array.");
    }
    return parsed;
  } catch (err) {
    console.error(`Unable to load dataset at ${filePath}`, err);
    process.exit(1);
  }
}

function createInitialState(config) {
  const now = new Date();
  const devices = config.devices.map(device => {
    const base = device.role === "producer" ? 350 : 520;
    const swing = device.role === "producer" ? 220 : 330;
    return {
      ...device,
      status: "active",
      total_kwh: base + Math.random() * swing,
      month_kwh: 15 + Math.random() * 25,
      month_index: now.getUTCMonth()
    };
  });

  const totals = devices.reduce(
    (acc, dev) => {
      if (dev.role === "producer") acc.production += dev.total_kwh;
      else acc.consumption += dev.total_kwh;
      return acc;
    },
    { production: 0, consumption: 0 }
  );

  return {
    config,
    status: "inactive",
    devices,
    lastTotalUsage: totals.consumption,
    metrics: {
      totalProduction: totals.production,
      totalConsumption: totals.consumption,
      netSurplus: totals.production - totals.consumption,
      revenue: 0
    },
    lastReadingAt: null
  };
}

function seededRand(seed) {
  const h = crypto.createHash("sha256").update(seed).digest("hex").slice(0, 8);
  return parseInt(h, 16) / 0xffffffff;
}

function jitter(seed, min = 0, max = 1) {
  return min + seededRand(seed) * (max - min);
}

const port = process.env.PORT || 3000;
app.listen(port, () => console.log(`Mock device listening on :${port}`));
