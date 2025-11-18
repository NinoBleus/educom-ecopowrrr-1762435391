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

            $this->deviceReadingRepository->saveDeviceReading([
                'readingTimestamp'  => $timestamp,
                'kwhGenerated'      => $entry['device_month_yield'],
                'device_id'         => $device,
                'pricePeriodId'     => $pricePeriod,
            ]);
        }

    }

    
}
