<?php
namespace App\Infrastructure;

use App\Infrastructure\Exception\AddressLookupException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AddressLookupClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $shortUrl,
        private string $fullUrl,
    ) {
    }

    public function fetchShort(string $postcode, string $number): array
    {
        return $this->request($this->shortUrl, $postcode, $number);
    }

    public function fetchFull(string $postcode, string $number): array
    {
        return $this->request($this->fullUrl, $postcode, $number);
    }

    private function request(string $endpoint, string $postcode, string $number): array
    {
        try {
            $response = $this->httpClient->request('GET', $endpoint, [
                'query' => [
                    'postcode' => $postcode,
                    'number' => $number,
                ],
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $this->apiKey),
                    'Accept' => 'application/json',
                ],
                'timeout' => 5,
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new AddressLookupException('Address lookup unavailable', 0, $e);
        }

        try {
            $payload = $response->toArray(false);
        } catch (DecodingExceptionInterface $e) {
            throw new AddressLookupException('Address lookup returned invalid response', 0, $e);
        }

        $status = $response->getStatusCode();

        if ($status === 404) {
            throw new AddressLookupException('Unknown postcode/house number combination', 404);
        }

        if ($status >= 400) {
            $message = $payload['message'] ?? $payload['error'] ?? 'Postcode service rejected the request';
            throw new AddressLookupException(sprintf('Postcode lookup failed (%d): %s', $status, $message), $status);
        }

        return $payload;
    }
}
