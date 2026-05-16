<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Exceptions;

use Throwable;

class RateLimitException extends HttpException
{
    public function __construct(
        string $message = 'RDW rate limit exceeded (HTTP 429).',
        ?string $responseBody = null,
        public readonly ?int $retryAfterSeconds = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $responseBody, $previous);
    }
}
