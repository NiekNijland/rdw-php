<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use NiekNijland\RDW\Exceptions\HttpException;
use NiekNijland\RDW\Exceptions\RateLimitException;
use NiekNijland\RDW\Exceptions\RdwException;
use Psr\Http\Message\ResponseInterface;

class SocrataClient
{
    private readonly ClientInterface $httpClient;

    public function __construct(
        private readonly Configuration $configuration = new Configuration(),
        ?ClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => rtrim($this->configuration->baseUrl, '/') . '/',
            'timeout' => $this->configuration->timeoutSeconds,
        ]);
    }

    /**
     * Fetch rows from a Socrata dataset.
     *
     * @param array<string, scalar|null> $query
     * @return list<array<string, mixed>>
     */
    public function getRows(string $datasetId, array $query = []): array
    {
        $payload = $this->request('GET', "resource/{$datasetId}.json", $query);

        if (! array_is_list($payload)) {
            throw new RdwException('Expected a list of rows from Socrata, got an object.');
        }

        /** @var list<array<string, mixed>> $payload */
        return $payload;
    }

    /**
     * Fetch a dataset's metadata document.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(string $datasetId): array
    {
        $payload = $this->request('GET', "api/views/{$datasetId}.json");

        if (array_is_list($payload)) {
            throw new RdwException('Expected a metadata object from Socrata, got a list.');
        }

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<int|string, mixed>
     */
    private function request(string $method, string $path, array $query = []): array
    {
        try {
            $response = $this->httpClient->request($method, $path, [
                'query' => $query,
                'headers' => $this->headers(),
                'http_errors' => false,
            ]);
        } catch (GuzzleException $exception) {
            throw new HttpException(
                sprintf('RDW request failed: %s', $exception->getMessage()),
                statusCode: 0,
                previous: $exception,
            );
        }

        return $this->decode($response);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function decode(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status === 429) {
            throw new RateLimitException(
                responseBody: $body,
                retryAfterSeconds: $this->readRetryAfter($response),
            );
        }

        if ($status < 200 || $status >= 300) {
            throw new HttpException(
                sprintf('RDW returned HTTP %d.', $status),
                statusCode: $status,
                responseBody: $body,
            );
        }

        try {
            $decoded = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RdwException('Failed to decode RDW JSON response.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RdwException('Expected a JSON object or list from RDW, got a scalar.');
        }

        /** @var array<int|string, mixed> $decoded */
        return $decoded;
    }

    private function readRetryAfter(ResponseInterface $response): ?int
    {
        $header = $response->getHeaderLine('Retry-After');

        if ($header === '') {
            return null;
        }

        if (ctype_digit($header)) {
            return (int) $header;
        }

        $timestamp = strtotime($header);
        if ($timestamp === false) {
            return null;
        }

        return max(0, $timestamp - time());
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => $this->configuration->userAgent,
        ];

        if ($this->configuration->appToken !== null) {
            $headers['X-App-Token'] = $this->configuration->appToken;
        }

        return $headers;
    }
}
