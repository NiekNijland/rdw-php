<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Exceptions;

use Throwable;

class HttpException extends RdwException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?string $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
