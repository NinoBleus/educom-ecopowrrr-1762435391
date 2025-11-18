<?php

namespace App\Infrastructure;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeviceTelemetryClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
    ) {
    }

    public function fetch(?string $deviceId = null): array
    {
        $options = [
            'timeout' => 10,
        ];

        if ($deviceId !== null) {
            $options['query']['device_id'] = $deviceId;
        }

        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf('%s/telemetry', rtrim($this->baseUrl, '/')),
                $options
            );
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Telemetry service unavailable', 0, $e);
        }

        try {
            $payload = $response->toArray(false);
        } catch (DecodingExceptionInterface $e) {
            throw new \RuntimeException('Telemetry service returned invalid JSON', 0, $e);
        }

        $status = $response->getStatusCode();

        if ($status >= 400) {
            $message = $payload['message'] ?? $payload['error'] ?? 'Telemetry service rejected the request';
            throw new \RuntimeException(sprintf('Telemetry fetch failed (%d): %s', $status, $message), $status);
        }

        return $payload;
    }
}
