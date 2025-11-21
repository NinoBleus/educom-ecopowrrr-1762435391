<?php
namespace App\Service;

use App\Repository\DeviceReadingRepository;
use App\Infrastructure\DeviceTelemetryClient;
use App\Service\DeviceService;
use App\Service\BuyPricePeriodService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DeviceReadingService
{
    public function __construct(
        private readonly DeviceReadingRepository $deviceReadingRepository,
        private readonly DeviceService $deviceService,
        private readonly DeviceTelemetryClient $deviceTelemetryClient,
        private readonly BuyPricePeriodService $buyPricePeriod,
    ) {}

    public function ingestLatestTelemetry() {
        $payload = $this->deviceTelemetryClient->fetch();
        if (!isset($payload['date']) || !isset($payload['devices']) || !is_array($payload['devices'])) {
            throw new BadRequestHttpException('Telemetry payload missing required fields.');
        }

        $timestamp = new \DateTimeImmutable($payload['date']);
        $pricePeriod = $this->buyPricePeriod->getPeriodFor($timestamp);

        foreach ($payload['devices'] as $entry) {
            $device = $this->deviceService->findDeviceBySerial($entry['serial_number']);
            if (!$device) {
                continue;
            }

            $deviceType = $entry['device_type'] ?? 'unknown';
            $rawYield = $entry['device_month_yield'] ?? null;
            $kwhGenerated = $rawYield !== null ? (float) $rawYield : 0.0;
            $kwhUsed = null;

            if ($deviceType === 'smart_meter') {
                $rawUsage = $entry['device_month_usage'] ?? null;
                $kwhUsed = $rawUsage !== null ? number_format((float) $rawUsage, 2, '.', '') : null;
                $kwhGenerated = 0.0;
            }

            $payloadForDevice = [
                'readingTimestamp'  => $timestamp,
                'kwhGenerated'      => number_format($kwhGenerated, 4, '.', ''),
                'device_id'         => $device,
                'pricePeriodId'     => $pricePeriod,
            ];

            if ($kwhUsed !== null) {
                $payloadForDevice['kwhUsed'] = $kwhUsed;
            }

            $this->deviceReadingRepository->saveDeviceReading($payloadForDevice);
        }

    }

    
}
