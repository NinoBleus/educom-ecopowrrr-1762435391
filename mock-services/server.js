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

const basePayload = loadDataset(datasetPath);
if (!basePayload.devices || basePayload.devices.length === 0) {
  throw new Error(`${datasetPath} must contain a devices array.`);
}

const templates = Array.isArray(basePayload) ? basePayload : [basePayload];
if (templates.length === 0) {
  throw new Error(`Dataset at ${datasetPath} is empty, cannot mock devices.`);
}

const simulationConfig = {
  startDate: parseDateInput(process.env.SIMULATION_START_DATE),
  monthStep: parsePositiveInt(process.env.SIMULATION_MONTH_STEP),
  variance:
    process.env.SIMULATION_MONTH_VARIANCE !== undefined
      ? clampVariance(Number(process.env.SIMULATION_MONTH_VARIANCE))
      : null
};

const deviceRegistry = new Map(
  templates.map(template => {
    const id = String(template.device_id ?? crypto.randomUUID());
    return [id, createInitialState(template, id)];
  })
);
const defaultDeviceId = [...deviceRegistry.keys()][0];

app.get("/devices", (_req, res) => {
  res.json(
    Array.from(deviceRegistry.values()).flatMap(device =>
      device.devices.map(sub => ({
        device_id: sub.device_id,
        serial_number: sub.serial_number,
        device_type: sub.device_type,
        device_status: sub.device_status
      }))
    )
  );
});

app.post("/activate", (req, res) => {
  const ctx = resolveDevice(req, res);
  if (!ctx) return;
  ctx.device_status = "active";
  ctx.lastActivation = new Date().toISOString();
  res.json({ ack: true, status: ctx.device_status, device_id: ctx.device_id });
});

app.post("/deactivate", (req, res) => {
  const ctx = resolveDevice(req, res);
  if (!ctx) return;
  ctx.device_status = "inactive";
  ctx.lastDeactivation = new Date().toISOString();
  res.json({ ack: true, status: ctx.device_status, device_id: ctx.device_id });
});

app.get("/telemetry", (req, res) => {
  const ctx = resolveDevice(req, res);
  if (!ctx) return;

  const monthsToAdvance = resolveMonthStep(req.query, ctx);
  const { now, since } = resolveCycleWindow(ctx, monthsToAdvance, req.query?.since);
  const shouldAdvance = ctx.bootstrapped === true;
  if (shouldAdvance) {
    const minutes = Math.max(1, Math.floor((now - since) / 60000));
    advanceDevice(ctx, minutes, now);
  }
  const usageSinceLastFetch = ctx.cycle_usage ?? 0;

  res.json({
    message_id: crypto.randomBytes(8).toString("hex"),
    date: now.toISOString(),
    total_usage: usageSinceLastFetch.toFixed(3),
    devices: ctx.devices.map(device => {
      const base = {
        device_id: device.device_id,
        serial_number: device.serial_number,
        device_type: device.device_type,
        device_status: device.device_status
      };

      if (device.device_type === "smart_meter") {
        return {
          ...base,
          device_total_usage: device.device_total_usage.toFixed(3),
          device_month_usage: device.device_month_usage.toFixed(3)
        };
      }

      return {
        ...base,
        device_total_yield: device.device_total_yield.toFixed(3),
        device_month_yield: device.device_month_yield.toFixed(3)
      };
    })
  });

  ctx.lastReadingAt = now;
  ctx.nextReadingAt = addMonths(now, monthsToAdvance);
  ctx.bootstrapped = true;
  ctx.cycle_usage = 0;
});

function resolveDevice(req, res) {
  const requestedId =
    req.params?.deviceId ||
    req.params?.device_id ||
    req.body?.device_id ||
    req.query.device_id ||
    req.query.deviceId ||
    defaultDeviceId;

  const ctx = deviceRegistry.get(String(requestedId));
  if (!ctx) {
    res.status(404).json({ message: `Unknown device: ${requestedId}` });
    return null;
  }
  return ctx;
}

function advanceDevice(ctx, minutes, now) {
  const variance = typeof ctx.variance === "number" ? ctx.variance : 0.15;
  ctx.devices.forEach(device => {
    if (device.month_index !== now.getUTCMonth()) {
      device.month_index = now.getUTCMonth();
      if (device.device_type === "smart_meter") {
        device.device_month_usage = 0;
      } else {
        device.device_month_yield = 0;
      }
    }

    if (device.device_type === "smart_meter") {
      const baseConsumption =
        typeof device.consumptionRate === "number"
          ? device.consumptionRate
          : randomBetween(0.003, 0.006);
      const spread = variance > 0 ? 1 + randomBetween(-variance, variance) : 1;
      const usageDelta = Math.max(0, baseConsumption * spread * minutes);
      device.device_total_usage += usageDelta;
      device.device_month_usage += usageDelta;
      ctx.cycle_usage = (ctx.cycle_usage ?? 0) + usageDelta;
      return;
    }

    const baseRate =
      typeof device.productionRate === "number"
        ? device.productionRate
        : randomBetween(0.003, 0.006);
    const spread = variance > 0 ? 1 + randomBetween(-variance, variance) : 1;
    const delta = Math.max(0, baseRate * spread * minutes);
    device.device_total_yield += delta;
    device.device_month_yield += delta;
  });

  ctx.lastReadingAt = now;
}

function loadDataset(filePath) {
  try {
    const raw = fs.readFileSync(filePath, "utf-8");
    return JSON.parse(raw);
  } catch (err) {
    console.error(`Unable to load dataset at ${filePath}`, err);
    process.exit(1);
  }
}

function createInitialState(template, id) {
  const startDate =
    simulationConfig.startDate ??
    parseDateInput(template.simulation_start ?? template.date) ??
    new Date();
  const defaultMonthStep =
    simulationConfig.monthStep ?? parsePositiveInt(template.simulation_month_step) ?? 1;
  const variance =
    simulationConfig.variance ??
    (template.simulation_month_variance !== undefined
      ? clampVariance(Number(template.simulation_month_variance))
      : 0.15);

  const baselineMinutes = Math.max(
    1,
    Math.floor((startDate - addMonths(startDate, -1)) / 60000)
  );

  return {
    device_id: id,
    device_status: template.device_status ?? "active",
    devices: (template.devices ?? []).map((device, index) => {
      const type = device.device_type ?? "unknown";
      const base = {
        device_id: String(device.device_id ?? `${id}-${index + 1}`),
        serial_number: device.serial_number ?? `SERIAL-${id}-${index + 1}`,
        device_type: type,
        device_status: device.device_status ?? "active",
        month_index: startDate.getUTCMonth()
      };

      if (type === "smart_meter") {
        const monthlyUsage =
          toNumber(device.device_month_usage ?? device.device_month_yield) ||
          randomBetween(150, 250);
        const consumptionRate =
          monthlyUsage > 0 && baselineMinutes > 0
            ? monthlyUsage / baselineMinutes
            : randomBetween(0.003, 0.006);

        return {
          ...base,
          device_total_usage: toNumber(device.device_total_usage ?? device.device_total_yield),
          device_month_usage: monthlyUsage,
          consumptionRate
        };
      }

      const monthlyYield =
        toNumber(device.device_month_yield ?? device.device_month_usage) ||
        randomBetween(150, 250);
      const productionRate =
        monthlyYield > 0 && baselineMinutes > 0
          ? monthlyYield / baselineMinutes
          : randomBetween(0.003, 0.006);

      return {
        ...base,
        device_total_yield: toNumber(device.device_total_yield ?? device.device_total_usage),
        device_month_yield: monthlyYield,
        productionRate
      };
    }),
    lastReadingAt: null,
    nextReadingAt: startDate,
    monthStep: defaultMonthStep,
    variance,
    bootstrapped: false,
    cycle_usage: 0
  };
}

function resolveMonthStep(query, ctx) {
  const queryValue = query?.months ?? query?.step ?? query?.advance;
  const parsed = parsePositiveInt(queryValue);
  if (parsed) {
    return parsed;
  }
  if (ctx.monthStep && ctx.monthStep > 0) {
    return ctx.monthStep;
  }
  return 1;
}

function resolveCycleWindow(ctx, monthsToAdvance, sinceRaw) {
  const now = ctx.nextReadingAt ?? new Date();
  let since = null;
  if (sinceRaw) {
    const sinceCandidate = new Date(sinceRaw);
    if (!Number.isNaN(sinceCandidate.getTime())) {
      since = sinceCandidate;
    }
  }

  if (!since || Number.isNaN(since.getTime())) {
    since = ctx.lastReadingAt;
  }

  if (!since || Number.isNaN(since.getTime()) || since >= now) {
    since = addMonths(now, -monthsToAdvance);
  }

  return { now, since };
}

function addMonths(date, amount) {
  const target = new Date(date.getTime());
  const day = target.getUTCDate();
  target.setUTCDate(1);
  target.setUTCMonth(target.getUTCMonth() + amount);
  const daysInMonth = new Date(
    Date.UTC(target.getUTCFullYear(), target.getUTCMonth() + 1, 0)
  ).getUTCDate();
  target.setUTCDate(Math.min(day, daysInMonth));
  return target;
}

function parseDateInput(value) {
  if (!value) {
    return null;
  }
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? null : date;
}

function parsePositiveInt(value) {
  if (value === undefined || value === null || value === "") {
    return null;
  }
  const parsed = Number.parseInt(value, 10);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
}

function clampVariance(value) {
  if (!Number.isFinite(value)) {
    return null;
  }
  if (value < 0) {
    return 0;
  }
  return Math.min(0.95, value);
}

function randomBetween(min, max) {
  return min + Math.random() * (max - min);
}

function toNumber(value) {
  const num = Number(value);
  return Number.isFinite(num) ? num : 0;
}

const port = process.env.PORT || 3000;
app.listen(port, () => console.log(`Mock device listening on :${port}`));
