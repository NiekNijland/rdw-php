<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Records;

use Carbon\CarbonImmutable;
use NiekNijland\RDW\Records\ValueCaster;
use NiekNijland\RDW\Schema\CastType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValueCaster::class)]
final class ValueCasterTest extends TestCase
{
    public function test_null_and_empty_string_become_null_for_any_type(): void
    {
        foreach (CastType::cases() as $type) {
            self::assertNull(ValueCaster::cast($type, null), "null → null for {$type->value}");
            self::assertNull(ValueCaster::cast($type, ''), "empty → null for {$type->value}");
        }
    }

    public function test_string_cast(): void
    {
        self::assertSame('hello', ValueCaster::cast(CastType::String, 'hello'));
        self::assertSame('42', ValueCaster::cast(CastType::String, 42));
    }

    public function test_integer_cast(): void
    {
        self::assertSame(1050, ValueCaster::cast(CastType::Integer, '1050'));
        self::assertSame(0, ValueCaster::cast(CastType::Integer, '0'));
        self::assertNull(ValueCaster::cast(CastType::Integer, 'not-a-number'));
    }

    public function test_decimal_cast_keeps_string(): void
    {
        self::assertSame('16890.50', ValueCaster::cast(CastType::Decimal, '16890.50'));
        self::assertSame('0.00000000000001', ValueCaster::cast(CastType::Decimal, '0.00000000000001'));
    }

    /**
     * @return array<string, array{0: string, 1: bool|null}>
     */
    public static function booleanCases(): array
    {
        return [
            'ja → true' => ['Ja', true],
            'lowercase ja → true' => ['ja', true],
            'nee → false' => ['Nee', false],
            'lowercase nee → false' => ['nee', false],
            'random text → null' => ['Misschien', null],
            'numeric 1 → null' => ['1', null],
        ];
    }

    #[DataProvider('booleanCases')]
    public function test_boolean_cast(string $raw, ?bool $expected): void
    {
        self::assertSame($expected, ValueCaster::cast(CastType::Boolean, $raw));
    }

    public function test_calendar_date_parses_iso_payload_to_midnight_utc(): void
    {
        $date = ValueCaster::cast(CastType::CalendarDate, '2024-05-16T00:00:00.000');

        self::assertInstanceOf(CarbonImmutable::class, $date);
        self::assertSame('2024-05-16T00:00:00+00:00', $date->toIso8601String());
        self::assertSame('UTC', $date->getTimezone()->getName());
    }

    public function test_calendar_date_accepts_plain_y_m_d_input(): void
    {
        $date = ValueCaster::cast(CastType::CalendarDate, '2024-05-16');

        self::assertInstanceOf(CarbonImmutable::class, $date);
        self::assertSame('2024-05-16T00:00:00+00:00', $date->toIso8601String());
    }

    public function test_calendar_date_rejects_garbage(): void
    {
        self::assertNull(ValueCaster::cast(CastType::CalendarDate, 'not-a-date'));
        self::assertNull(ValueCaster::cast(CastType::CalendarDate, 'abc'));
    }

    public function test_calendar_date_rejects_overflowing_dates(): void
    {
        // PHP would silently roll 2024-02-30 to 2024-03-01; we reject.
        self::assertNull(ValueCaster::cast(CastType::CalendarDate, '2024-02-30'));
        self::assertNull(ValueCaster::cast(CastType::CalendarDate, '2024-13-01'));
    }

    public function test_numeric_date_parses_ymd(): void
    {
        $date = ValueCaster::cast(CastType::NumericDate, '20240516');

        self::assertInstanceOf(CarbonImmutable::class, $date);
        self::assertSame('2024-05-16T00:00:00+00:00', $date->toIso8601String());
    }

    public function test_numeric_date_rejects_non_eight_digit_strings(): void
    {
        self::assertNull(ValueCaster::cast(CastType::NumericDate, '20240516T'));
        self::assertNull(ValueCaster::cast(CastType::NumericDate, '202405'));
        self::assertNull(ValueCaster::cast(CastType::NumericDate, 'YYYYMMDD'));
    }

    public function test_numeric_date_rejects_overflowing_dates(): void
    {
        self::assertNull(ValueCaster::cast(CastType::NumericDate, '20240230'));
        self::assertNull(ValueCaster::cast(CastType::NumericDate, '20241301'));
    }

    public function test_excluded_always_returns_null(): void
    {
        self::assertNull(ValueCaster::cast(CastType::Excluded, 'anything'));
    }
}
