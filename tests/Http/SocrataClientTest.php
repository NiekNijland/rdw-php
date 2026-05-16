<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use NiekNijland\RDW\Exceptions\HttpException;
use NiekNijland\RDW\Exceptions\RateLimitException;
use NiekNijland\RDW\Exceptions\RdwException;
use NiekNijland\RDW\Http\Configuration;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Tests\Support\RequestSpy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SocrataClient::class)]
final class SocrataClientTest extends TestCase
{
    private RequestSpy $spy;

    protected function setUp(): void
    {
        $this->spy = new RequestSpy();
    }

    public function test_get_rows_decodes_a_json_array(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode([
                ['kenteken' => 'AB-123-C'],
                ['kenteken' => 'XY-987-Z'],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $rows = $client->getRows('m9d7-ebf2', ['handelsbenaming' => 'GSX-R 1100']);

        self::assertSame([
            ['kenteken' => 'AB-123-C'],
            ['kenteken' => 'XY-987-Z'],
        ], $rows);

        $request = $this->spy->last();
        self::assertSame('GET', $request->getMethod());
        self::assertStringEndsWith('/resource/m9d7-ebf2.json?handelsbenaming=GSX-R%201100', (string) $request->getUri());
    }

    public function test_get_metadata_returns_an_object(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode(['id' => 'm9d7-ebf2', 'name' => 'Test'], JSON_THROW_ON_ERROR)),
        ]);

        $metadata = $client->getMetadata('m9d7-ebf2');

        self::assertSame(['id' => 'm9d7-ebf2', 'name' => 'Test'], $metadata);
        self::assertStringEndsWith('/api/views/m9d7-ebf2.json', (string) $this->spy->last()->getUri());
    }

    public function test_get_rows_rejects_an_object_response(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode(['unexpected' => true], JSON_THROW_ON_ERROR)),
        ]);

        $this->expectException(RdwException::class);
        $client->getRows('m9d7-ebf2');
    }

    public function test_sends_app_token_and_user_agent_when_configured(): void
    {
        $client = $this->makeClient(
            [new Response(200, [], '[]')],
            new Configuration(appToken: 'tok-abc', userAgent: 'custom-agent/2.1'),
        );

        $client->getRows('m9d7-ebf2');

        $request = $this->spy->last();
        self::assertSame('tok-abc', $request->getHeaderLine('X-App-Token'));
        self::assertSame('custom-agent/2.1', $request->getHeaderLine('User-Agent'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
    }

    public function test_omits_app_token_header_when_not_configured(): void
    {
        $client = $this->makeClient([new Response(200, [], '[]')]);

        $client->getRows('m9d7-ebf2');

        self::assertSame('', $this->spy->last()->getHeaderLine('X-App-Token'));
    }

    public function test_translates_http_429_to_rate_limit_exception(): void
    {
        $client = $this->makeClient([
            new Response(429, ['Retry-After' => '17'], 'slow down'),
        ]);

        try {
            $client->getRows('m9d7-ebf2');
            self::fail('Expected RateLimitException.');
        } catch (RateLimitException $exception) {
            self::assertSame(429, $exception->statusCode);
            self::assertSame(17, $exception->retryAfterSeconds);
            self::assertSame('slow down', $exception->responseBody);
        }
    }

    public function test_translates_other_non_2xx_to_http_exception(): void
    {
        $client = $this->makeClient([
            new Response(500, [], 'boom'),
        ]);

        try {
            $client->getRows('m9d7-ebf2');
            self::fail('Expected HttpException.');
        } catch (HttpException $exception) {
            self::assertSame(500, $exception->statusCode);
            self::assertSame('boom', $exception->responseBody);
        }
    }

    public function test_invalid_json_response_throws_rdw_exception(): void
    {
        $client = $this->makeClient([
            new Response(200, [], '{not-json'),
        ]);

        $this->expectException(RdwException::class);
        $client->getRows('m9d7-ebf2');
    }

    public function test_top_level_json_scalar_response_throws_rdw_exception(): void
    {
        $client = $this->makeClient([
            new Response(200, [], '"unexpected"'),
        ]);

        $this->expectException(RdwException::class);
        $this->expectExceptionMessage('JSON object or list');
        $client->getRows('m9d7-ebf2');
    }

    public function test_retry_after_accepts_an_http_date(): void
    {
        $future = gmdate('D, d M Y H:i:s', time() + 30) . ' GMT';

        $client = $this->makeClient([
            new Response(429, ['Retry-After' => $future], 'slow'),
        ]);

        try {
            $client->getRows('m9d7-ebf2');
            self::fail('Expected RateLimitException.');
        } catch (RateLimitException $exception) {
            self::assertNotNull($exception->retryAfterSeconds);
            self::assertGreaterThan(0, $exception->retryAfterSeconds);
            self::assertLessThanOrEqual(60, $exception->retryAfterSeconds);
        }
    }

    public function test_retry_after_is_null_when_header_is_missing(): void
    {
        $client = $this->makeClient([
            new Response(429, [], 'slow'),
        ]);

        try {
            $client->getRows('m9d7-ebf2');
            self::fail('Expected RateLimitException.');
        } catch (RateLimitException $exception) {
            self::assertNull($exception->retryAfterSeconds);
        }
    }

    /**
     * @param list<Response> $responses
     */
    private function makeClient(array $responses, ?Configuration $configuration = null): SocrataClient
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        $this->spy->attach($handler);

        $guzzle = new Client([
            'handler' => $handler,
            'base_uri' => 'https://opendata.rdw.nl/',
        ]);

        return new SocrataClient($configuration ?? new Configuration(), $guzzle);
    }
}
