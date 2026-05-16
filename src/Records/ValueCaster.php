<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

use Carbon\CarbonImmutable;
use NiekNijland\RDW\Schema\CastType;
use Throwable;

/**
 * Pure transformation of a single raw RDW payload value into its
 * public-facing form.
 *
 * Dates are parsed with exact formats and normalized to midnight UTC.
 * Decimals stay as strings to avoid silent precision loss. Boolean values
 * accept the canonical "Ja"/"Nee" pair RDW uses for binary indicators;
 * everything else returns null so unexpected vocabularies surface as
 * "unknown" rather than being coerced to false.
 */
final class ValueCaster
{
    public static function cast(CastType $type, mixed $raw): mixed
    {
        if ($raw === null) {
            return null;
        }

        if (is_string($raw) && $raw === '') {
            return null;
        }

        return match ($type) {
            CastType::String, CastType::Decimal => is_scalar($raw) ? (string) $raw : null,
            CastType::Integer => is_numeric($raw) ? (int) $raw : null,
            CastType::Boolean => self::toBool($raw),
            CastType::CalendarDate => is_scalar($raw) ? self::parseCalendarDate((string) $raw) : null,
            CastType::NumericDate => is_scalar($raw) ? self::parseNumericDate((string) $raw) : null,
            CastType::Excluded => null,
        };
    }

    private static function toBool(mixed $raw): ?bool
    {
        if (! is_scalar($raw)) {
            return null;
        }

        return match (strtolower((string) $raw)) {
            'ja' => true,
            'nee' => false,
            default => null,
        };
    }

    /**
     * Socrata's calendar_date arrives as either "2024-05-16" or
     * "2024-05-16T00:00:00.000". We only care about the date semantics,
     * so we anchor on the first ten characters and parse with an exact
     * Y-m-d format. Anything that does not match returns null.
     */
    private static function parseCalendarDate(string $raw): ?CarbonImmutable
    {
        if (strlen($raw) < 10) {
            return null;
        }

        $datePart = substr($raw, 0, 10);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePart) !== 1) {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $datePart, 'UTC');
        } catch (Throwable) {
            return null;
        }

        return $date instanceof CarbonImmutable ? $date : null;
    }

    private static function parseNumericDate(string $raw): ?CarbonImmutable
    {
        if (! ctype_digit($raw) || strlen($raw) !== 8) {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Ymd', $raw, 'UTC');
        } catch (Throwable) {
            return null;
        }

        return $date instanceof CarbonImmutable ? $date : null;
    }
}
