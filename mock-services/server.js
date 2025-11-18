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

  const now = new Date();
  const sinceCandidate = req.query.since ? new Date(req.query.since) : ctx.lastReadingAt;
  const since =
    sinceCandidate && !Number.isNaN(sinceCandidate.getTime())
      ? sinceCandidate
      : new Date(now.getTime() - 5 * 60 * 1000);
  const minutes = Math.max(1, Math.floor((now - since) / 60000));

  advanceDevice(ctx, minutes, now);

  res.json({
    message_id: crypto.randomBytes(8).toString("hex"),
    date: now.toISOString(),
    total_usage: ctx.total_usage.toFixed(3),
    devices: ctx.devices.map(device => ({
      device_id: device.device_id,
      serial_number: device.serial_number,
      device_type: device.device_type,
      device_status: device.device_status,
      device_total_yield: device.device_total_yield.toFixed(3),
      device_month_yield: device.device_month_yield.toFixed(3)
    }))
  });
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
  ctx.devices.forEach(device => {
    if (device.month_index !== now.getUTCMonth()) {
      device.month_index = now.getUTCMonth();
      device.device_month_yield = 0;
    }

    const delta = randomBetween(0.05, 0.25) * minutes;
    device.device_total_yield += delta;
    device.device_month_yield += delta;
  });

  ctx.total_usage += randomBetween(0.1, 0.3) * minutes;
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
  const now = new Date();
  return {
    device_id: id,
    device_status: template.device_status ?? "active",
    total_usage: toNumber(template.total_usage),
    devices: (template.devices ?? []).map((device, index) => ({
      device_id: String(device.device_id ?? `${id}-${index + 1}`),
      serial_number: device.serial_number ?? `SERIAL-${id}-${index + 1}`,
      device_type: device.device_type ?? "unknown",
      device_status: device.device_status ?? "active",
      device_total_yield: toNumber(device.device_total_yield),
      device_month_yield: toNumber(device.device_month_yield),
      month_index: now.getUTCMonth()
    })),
    lastReadingAt: null
  };
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
