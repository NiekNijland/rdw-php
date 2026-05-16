<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema;

/**
 * How an RDW field is transformed before it reaches the consumer.
 *
 * Decimal stays as string by design to avoid silent precision loss.
 * Excluded fields are intentionally hidden from the public API.
 */
enum CastType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Decimal = 'decimal';
    case Boolean = 'boolean';
    case CalendarDate = 'calendar_date';
    case NumericDate = 'numeric_date';
    case Excluded = 'excluded';

    public function phpType(): string
    {
        return match ($this) {
            self::String => '?string',
            self::Integer => '?int',
            self::Decimal => '?string',
            self::Boolean => '?bool',
            self::CalendarDate, self::NumericDate => '?\\Carbon\\CarbonImmutable',
            self::Excluded => '?string',
        };
    }
}
