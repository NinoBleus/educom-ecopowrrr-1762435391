// server.js
import express from "express";
import crypto from "crypto";

const app = express();
app.use(express.json());

const cfg = {
  postcode: process.env.POSTCODE || "1011AB",
  huisnr: process.env.HUISNR || "12",
  deviceType: process.env.DEVICE_TYPE || "solar",
  serials: (process.env.SERIALS || "SOL-001,SOL-002").split(","),
};

let state = {
  deviceId: null,
  status: "inactive",
  lastTotalUsage: 0.0,
  subdevices: cfg.serials.map(sn => ({
    serial_number: sn,
    device_type: cfg.deviceType,
    status: "active",
    total_kwh: 100 + Math.random() * 50,
    month_kwh: 10 + Math.random() * 5
  }))
};

function seededRand(seed) {
  const h = crypto.createHash("sha256").update(seed).digest("hex").slice(0, 8);
  return parseInt(h, 16) / 0xffffffff;
}

function stepYield(minutes = 15) {
  const now = new Date();
  const hour = now.getUTCHours();
  let factor = 0.1;
  if (cfg.deviceType === "solar") {
    factor = Math.max(0, Math.sin(((hour - 6) / 12) * Math.PI));
  }
  const base = 0.05 * (minutes / 15); 
  return base * factor * (0.8 + seededRand(cfg.postcode + cfg.huisnr) * 0.4);
}

// Activate/deactivate
app.post("/activate", (req, res) => {
  state.status = "active";
  state.deviceId = req.body?.device_id || state.deviceId || crypto.randomUUID();
  res.json({ ack: true, status: state.status, device_id: state.deviceId });
});

app.post("/deactivate", (_req, res) => {
  state.status = "inactive";
  res.json({ ack: true, status: state.status });
});

// Telemetry
app.get("/telemetry", (req, res) => {
  const since = req.query.since ? new Date(req.query.since) : new Date(Date.now() - 15 * 60 * 1000);
  const now = new Date();

  const minutes = Math.max(1, Math.floor((now - since) / 60000));
  let totalUsageDelta = 0;
  state.subdevices.forEach(sd => {
    const delta = stepYield(minutes);
    sd.total_kwh += delta;
    if (now.getUTCDate() === 1 && now.getUTCHours() === 0) sd.month_kwh = 0;
    sd.month_kwh += delta;
    totalUsageDelta += delta;
  });
  state.lastTotalUsage += totalUsageDelta;

  const payload = {
    message_id: crypto.randomBytes(8).toString("hex"),
    device_id: state.deviceId || "unassigned",
    device_status: state.status,
    date: now.toISOString(),
    total_usage: state.lastTotalUsage.toFixed(3),
    devices: state.subdevices.map(sd => ({
      serial_number: sd.serial_number,
      device_type: sd.device_type,
      device_status: sd.status,
      device_total_yield: sd.total_kwh.toFixed(3),
      device_month_yield: sd.month_kwh.toFixed(3)
    }))
  };
  res.json(payload);
});

const port = process.env.PORT || 3000;
app.listen(port, () => console.log(`Mock device listening on :${port}`));
