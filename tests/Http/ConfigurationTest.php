<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Http;

use NiekNijland\RDW\Http\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function test_defaults_match_documented_constants(): void
    {
        $config = new Configuration();

        self::assertNull($config->appToken);
        self::assertSame(Configuration::DEFAULT_BASE_URL, $config->baseUrl);
        self::assertSame(Configuration::DEFAULT_USER_AGENT, $config->userAgent);
        self::assertSame(Configuration::DEFAULT_TIMEOUT_SECONDS, $config->timeoutSeconds);
    }

    public function test_accepts_overrides(): void
    {
        $config = new Configuration(
            appToken: 'token-123',
            baseUrl: 'https://example.test',
            userAgent: 'agent/1.0',
            timeoutSeconds: 2.5,
        );

        self::assertSame('token-123', $config->appToken);
        self::assertSame('https://example.test', $config->baseUrl);
        self::assertSame('agent/1.0', $config->userAgent);
        self::assertSame(2.5, $config->timeoutSeconds);
    }
}
