<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Http;

final readonly class Configuration
{
    public const string DEFAULT_BASE_URL = 'https://opendata.rdw.nl';

    public const string DEFAULT_USER_AGENT = 'nieknijland/rdw-opendata-php';

    public const float DEFAULT_TIMEOUT_SECONDS = 10.0;

    public function __construct(
        public ?string $appToken = null,
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public string $userAgent = self::DEFAULT_USER_AGENT,
        public float $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS,
    ) {
    }
}
