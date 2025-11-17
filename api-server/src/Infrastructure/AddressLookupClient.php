<?php
namespace App\Infrastructure;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

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
            throw new \RuntimeException('Address lookup unavailable', 0, $e);
        }

        try {
            return $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new \RuntimeException('Address lookup failed: '.$e->getMessage(), 0, $e);
        }
    }
}
