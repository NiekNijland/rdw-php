<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Query;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use LogicException;
use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleField;
use NiekNijland\RDW\Fields\RegisteredVehicleFuelField;
use NiekNijland\RDW\Http\Configuration;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Query\QueryBuilder;
use NiekNijland\RDW\Query\SortDirection;
use NiekNijland\RDW\Records\RegisteredVehicle;
use NiekNijland\RDW\Schema\SchemaRegistry;
use NiekNijland\RDW\Tests\Support\RequestSpy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueryBuilder::class)]
final class QueryBuilderTest extends TestCase
{
    private RequestSpy $spy;

    protected function setUp(): void
    {
        $this->spy = new RequestSpy();
    }

    public function test_typed_where_translates_to_rdw_field_keys(): void
    {
        $params = $this->newBuilder()
            ->where(RegisteredVehicleField::CommercialName, 'GSX-R 1100')
            ->where(RegisteredVehicleField::CanBeTransferred, true)
            ->toSoqlParams();

        self::assertSame(
            "(handelsbenaming = 'GSX-R 1100') AND (tenaamstellen_mogelijk = 'Ja')",
            $params['$where'],
        );
    }

    public function test_bool_to_ja_nee_only_kicks_in_for_boolean_typed_fields(): void
    {
        $params = $this->newBuilder()
            ->where(RegisteredVehicleField::SeatCount, true)
            ->toSoqlParams();

        // SeatCount is integer-typed, so true stays as the SoQL literal "true".
        self::assertSame('aantal_zitplaatsen = true', $params['$where']);
    }

    public function test_string_values_escape_single_quotes(): void
    {
        $params = $this->newBuilder()
            ->where(RegisteredVehicleField::CommercialName, "L'EXCEPTION")
            ->toSoqlParams();

        self::assertSame("handelsbenaming = 'L''EXCEPTION'", $params['$where']);
    }

    public function test_datetime_values_are_iso8601_with_milliseconds(): void
    {
        $date = CarbonImmutable::parse('1991-06-15', 'UTC');

        $params = $this->newBuilder()
            ->where(RegisteredVehicleField::FirstAdmissionDate, $date, '>=')
            ->toSoqlParams();

        self::assertSame(
            "datum_eerste_toelating_dt >= '1991-06-15T00:00:00.000'",
            $params['$where'],
        );
    }

    public function test_datetime_values_are_normalized_to_utc_before_serialization(): void
    {
        // 12:00 in Amsterdam (CEST, +02:00) is 10:00 in UTC.
        $date = CarbonImmutable::parse('2024-07-01 12:00:00', 'Europe/Amsterdam');

        $params = $this->newBuilder()
            ->where(RegisteredVehicleField::FirstAdmissionDate, $date, '>=')
            ->toSoqlParams();

        self::assertSame(
            "datum_eerste_toelating_dt >= '2024-07-01T10:00:00.000'",
            $params['$where'],
        );
    }

    public function test_where_in_emits_comma_separated_list(): void
    {
        $params = $this->newBuilder()
            ->whereIn(RegisteredVehicleField::LicensePlate, ['AB-12-CD', 'XY-34-ZW'])
            ->toSoqlParams();

        self::assertSame(
            "kenteken IN ('AB-12-CD', 'XY-34-ZW')",
            $params['$where'],
        );
    }

    public function test_select_order_group_and_limit_round_trip(): void
    {
        $params = $this->newBuilder()
            ->select(RegisteredVehicleField::Brand, RegisteredVehicleField::CommercialName)
            ->groupBy(RegisteredVehicleField::Brand)
            ->orderBy(RegisteredVehicleField::Brand, SortDirection::Desc)
            ->limit(50)
            ->offset(100)
            ->toSoqlParams();

        self::assertSame('merk, handelsbenaming', $params['$select']);
        self::assertSame('merk', $params['$group']);
        self::assertSame('merk DESC', $params['$order']);
        self::assertSame('50', $params['$limit']);
        self::assertSame('100', $params['$offset']);
    }

    public function test_count_aggregate_adds_a_select_expression(): void
    {
        $params = $this->newBuilder()
            ->select(RegisteredVehicleField::Brand)
            ->count(null, 'total')
            ->groupBy(RegisteredVehicleField::Brand)
            ->toSoqlParams();

        self::assertSame('merk, count(*) AS total', $params['$select']);
    }

    public function test_where_like_emits_a_sql_like_clause_with_quoted_pattern(): void
    {
        $params = $this->newBuilder()
            ->whereLike(RegisteredVehicleField::CommercialName, 'UP%')
            ->toSoqlParams();

        self::assertSame("handelsbenaming LIKE 'UP%'", $params['$where']);
    }

    public function test_where_starts_with_emits_the_soql_starts_with_function(): void
    {
        $params = $this->newBuilder()
            ->whereStartsWith(RegisteredVehicleField::CommercialName, 'UP')
            ->toSoqlParams();

        self::assertSame("starts_with(handelsbenaming, 'UP')", $params['$where']);
    }

    public function test_where_contains_emits_the_soql_contains_function(): void
    {
        $params = $this->newBuilder()
            ->whereContains(RegisteredVehicleField::CommercialName, 'GTI')
            ->toSoqlParams();

        self::assertSame("contains(handelsbenaming, 'GTI')", $params['$where']);
    }

    public function test_text_predicates_escape_single_quotes_in_the_value(): void
    {
        $params = $this->newBuilder()
            ->whereStartsWith(RegisteredVehicleField::CommercialName, "L'EX")
            ->toSoqlParams();

        self::assertSame("starts_with(handelsbenaming, 'L''EX')", $params['$where']);
    }

    public function test_text_predicates_reject_a_field_from_a_different_dataset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not belong to dataset "m9d7-ebf2"');

        $this->newBuilder()->whereContains(RegisteredVehicleFuelField::FuelDescription, 'BENZINE');
    }

    public function test_where_not_in_emits_a_not_in_clause(): void
    {
        $params = $this->newBuilder()
            ->whereNotIn(RegisteredVehicleField::Brand, ['VOLKSWAGEN', 'AUDI'])
            ->toSoqlParams();

        self::assertSame(
            "merk NOT IN ('VOLKSWAGEN', 'AUDI')",
            $params['$where'],
        );
    }

    public function test_where_not_in_rejects_an_empty_list(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty value list');

        $this->newBuilder()->whereNotIn(RegisteredVehicleField::Brand, []);
    }

    public function test_where_null_and_where_not_null_emit_is_null_clauses(): void
    {
        $params = $this->newBuilder()
            ->whereNull(RegisteredVehicleField::ApkExpiryDate)
            ->whereNotNull(RegisteredVehicleField::FirstAdmissionDate)
            ->toSoqlParams();

        self::assertSame(
            '(vervaldatum_apk_dt IS NULL) AND (datum_eerste_toelating_dt IS NOT NULL)',
            $params['$where'],
        );
    }

    public function test_where_between_emits_a_between_clause_with_encoded_values(): void
    {
        $params = $this->newBuilder()
            ->whereBetween(RegisteredVehicleField::SeatCount, 2, 5)
            ->toSoqlParams();

        self::assertSame('aantal_zitplaatsen BETWEEN 2 AND 5', $params['$where']);
    }

    public function test_where_not_between_emits_a_not_between_clause(): void
    {
        $params = $this->newBuilder()
            ->whereNotBetween(RegisteredVehicleField::SeatCount, 2, 5)
            ->toSoqlParams();

        self::assertSame('aantal_zitplaatsen NOT BETWEEN 2 AND 5', $params['$where']);
    }

    public function test_where_any_joins_inner_clauses_with_or(): void
    {
        $params = $this->newBuilder()
            ->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN')
            ->whereAny(static fn (QueryBuilder $q): QueryBuilder => $q
                ->whereStartsWith(RegisteredVehicleField::CommercialName, 'GTI')
                ->whereContains(RegisteredVehicleField::CommercialName, 'R32'))
            ->toSoqlParams();

        self::assertSame(
            "(merk = 'VOLKSWAGEN') AND ((starts_with(handelsbenaming, 'GTI')) OR (contains(handelsbenaming, 'R32')))",
            $params['$where'],
        );
    }

    public function test_where_any_with_a_single_inner_clause_does_not_wrap_in_or_parens(): void
    {
        $params = $this->newBuilder()
            ->whereAny(static fn (QueryBuilder $q): QueryBuilder => $q
                ->where(RegisteredVehicleField::Brand, 'AUDI'))
            ->toSoqlParams();

        self::assertSame("merk = 'AUDI'", $params['$where']);
    }

    public function test_where_any_rejects_a_callback_that_returns_nothing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must return the chained QueryBuilder');

        $this->newBuilder()->whereAny(static function (QueryBuilder $q): void {
            $q->where(RegisteredVehicleField::Brand, 'X');
        });
    }

    public function test_where_any_rejects_an_empty_callback(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least one where clause');

        $this->newBuilder()->whereAny(static fn (QueryBuilder $q): QueryBuilder => $q);
    }

    public function test_aggregate_helpers_add_alias_select_expressions(): void
    {
        $params = $this->newBuilder()
            ->sum(RegisteredVehicleField::SeatCount, 'total_seats')
            ->avg(RegisteredVehicleField::SeatCount, 'avg_seats')
            ->min(RegisteredVehicleField::SeatCount, 'min_seats')
            ->max(RegisteredVehicleField::SeatCount, 'max_seats')
            ->countDistinct(RegisteredVehicleField::Brand, 'brand_variants')
            ->toSoqlParams();

        self::assertSame(
            'sum(aantal_zitplaatsen) AS total_seats, '
                . 'avg(aantal_zitplaatsen) AS avg_seats, '
                . 'min(aantal_zitplaatsen) AS min_seats, '
                . 'max(aantal_zitplaatsen) AS max_seats, '
                . 'count(distinct merk) AS brand_variants',
            $params['$select'],
        );
    }

    public function test_having_raw_emits_a_having_clause(): void
    {
        $params = $this->newBuilder()
            ->select(RegisteredVehicleField::Brand)
            ->count(null, 'n')
            ->groupBy(RegisteredVehicleField::Brand)
            ->havingRaw('count(*) > 100')
            ->toSoqlParams();

        self::assertSame('count(*) > 100', $params['$having']);
    }

    public function test_distinct_prepends_distinct_to_the_select_clause(): void
    {
        $params = $this->newBuilder()
            ->select(RegisteredVehicleField::Brand)
            ->distinct()
            ->toSoqlParams();

        self::assertSame('distinct merk', $params['$select']);
    }

    public function test_distinct_without_a_select_throws(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('distinct() requires');

        $this->newBuilder()->distinct()->toSoqlParams();
    }

    public function test_where_not_wraps_a_sub_group_in_not(): void
    {
        $params = $this->newBuilder()
            ->whereNot(static fn (QueryBuilder $q): QueryBuilder => $q
                ->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN')
                ->whereStartsWith(RegisteredVehicleField::CommercialName, 'UP'))
            ->toSoqlParams();

        self::assertSame(
            "NOT ((merk = 'VOLKSWAGEN') AND (starts_with(handelsbenaming, 'UP')))",
            $params['$where'],
        );
    }

    public function test_search_sets_the_full_text_q_parameter(): void
    {
        $params = $this->newBuilder()
            ->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN')
            ->search('polo bluemotion')
            ->toSoqlParams();

        self::assertSame('polo bluemotion', $params['$q']);
        self::assertSame("merk = 'VOLKSWAGEN'", $params['$where']);
    }

    public function test_search_rejects_a_whitespace_only_query(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        $this->newBuilder()->search('   ');
    }

    public function test_exists_returns_true_when_a_row_is_returned(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([['kenteken' => 'AB-123-C']], JSON_THROW_ON_ERROR)),
        ]);

        self::assertTrue($builder->exists());

        parse_str($this->spy->last()->getUri()->getQuery(), $params);
        self::assertSame('1', $params['$limit']);
    }

    public function test_exists_returns_false_on_an_empty_response(): void
    {
        $builder = $this->newBuilder([new Response(200, [], '[]')]);

        self::assertFalse($builder->exists());
    }

    public function test_pluck_returns_a_list_of_cast_values_for_one_field(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([
                ['aantal_zitplaatsen' => '5'],
                ['aantal_zitplaatsen' => '7'],
                ['aantal_zitplaatsen' => null],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $seats = $builder->pluck(RegisteredVehicleField::SeatCount);

        self::assertSame([5, 7, null], $seats);

        parse_str($this->spy->last()->getUri()->getQuery(), $params);
        self::assertSame('aantal_zitplaatsen', $params['$select']);
    }

    public function test_raw_escape_hatches_are_passed_through_unchanged(): void
    {
        $params = $this->newBuilder()
            ->whereRaw("datum_eerste_toelating_dt < '1992-01-01T00:00:00.000'")
            ->selectRaw('count(distinct handelsbenaming)', 'variants')
            ->toSoqlParams();

        self::assertSame(
            "datum_eerste_toelating_dt < '1992-01-01T00:00:00.000'",
            $params['$where'],
        );
        self::assertSame(
            'count(distinct handelsbenaming) AS variants',
            $params['$select'],
        );
    }

    public function test_builder_is_immutable_each_call_returns_a_new_instance(): void
    {
        $first = $this->newBuilder();
        $second = $first->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN');

        self::assertNotSame($first, $second);
        self::assertSame([], $first->toSoqlParams());
        self::assertArrayHasKey('$where', $second->toSoqlParams());
    }

    public function test_get_hydrates_response_rows_into_record_instances(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([
                [
                    'kenteken' => 'AB-123-C',
                    'merk' => 'VOLKSWAGEN',
                    'handelsbenaming' => 'POLO',
                    'aantal_zitplaatsen' => '5',
                    'tenaamstellen_mogelijk' => 'Ja',
                    'datum_tenaamstelling_dt' => '2022-09-30T00:00:00.000',
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $vehicles = $builder
            ->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN')
            ->get();

        self::assertCount(1, $vehicles);
        self::assertInstanceOf(RegisteredVehicle::class, $vehicles[0]);
        self::assertSame('AB-123-C', $vehicles[0]->licensePlate);
        self::assertSame(5, $vehicles[0]->seatCount);
        self::assertTrue($vehicles[0]->canBeTransferred);
        self::assertSame('2022-09-30T00:00:00+00:00', $vehicles[0]->registrationDate?->toIso8601String());
    }

    public function test_first_appends_a_limit_of_one(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([['kenteken' => 'AB-123-C']], JSON_THROW_ON_ERROR)),
        ]);

        $builder->first();

        parse_str($this->spy->last()->getUri()->getQuery(), $params);
        self::assertSame('1', $params['$limit']);
    }

    public function test_first_returns_null_on_empty_response(): void
    {
        $builder = $this->newBuilder([new Response(200, [], '[]')]);

        self::assertNull($builder->first());
    }

    public function test_iterate_pages_through_results_lazily(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([
                ['kenteken' => 'AA-1'],
                ['kenteken' => 'AA-2'],
            ], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode([
                ['kenteken' => 'AA-3'],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $collected = [];
        foreach ($builder->iterate(pageSize: 2) as $vehicle) {
            $collected[] = $vehicle->licensePlate;
        }

        self::assertSame(['AA-1', 'AA-2', 'AA-3'], $collected);
        self::assertSame(2, $this->spy->count());

        parse_str($this->spy->at(0)->getUri()->getQuery(), $first);
        self::assertSame('2', $first['$limit']);
        self::assertSame('0', $first['$offset']);

        parse_str($this->spy->at(1)->getUri()->getQuery(), $second);
        self::assertSame('2', $second['$limit']);
        self::assertSame('2', $second['$offset']);
    }

    public function test_iterate_respects_a_pre_set_outer_offset(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([['kenteken' => 'AA-1']], JSON_THROW_ON_ERROR)),
        ]);

        foreach ($builder->offset(50)->iterate(pageSize: 10) as $_) {
            // exhaust the generator
        }

        parse_str($this->spy->last()->getUri()->getQuery(), $params);
        self::assertSame('50', $params['$offset']);
        self::assertSame('10', $params['$limit']);
    }

    public function test_iterate_stops_when_hard_limit_is_reached(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([
                ['kenteken' => 'AA-1'],
                ['kenteken' => 'AA-2'],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $collected = [];
        foreach ($builder->limit(2)->iterate(pageSize: 10) as $vehicle) {
            $collected[] = $vehicle->licensePlate;
        }

        self::assertCount(2, $collected);
        self::assertSame(1, $this->spy->count(), 'A second page should not be requested once limit is reached.');
    }

    public function test_where_rejects_an_unknown_operator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Operator "); DROP" is not allowed');

        $this->newBuilder()->where(RegisteredVehicleField::Brand, 'X', '); DROP');
    }

    public function test_where_rejects_null_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('whereRaw');

        $this->newBuilder()->where(RegisteredVehicleField::Brand, null);
    }

    public function test_where_rejects_non_finite_floats(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('NAN or INF');

        $this->newBuilder()->where(RegisteredVehicleField::Length, INF);
    }

    public function test_select_raw_alias_must_be_a_simple_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Alias "weird alias-with-stuff" must match');

        $this->newBuilder()->selectRaw('count(*)', 'weird alias-with-stuff');
    }

    public function test_select_raw_accepts_a_well_formed_alias(): void
    {
        $params = $this->newBuilder()
            ->selectRaw('count(*)', 'total_count')
            ->toSoqlParams();

        self::assertSame('count(*) AS total_count', $params['$select']);
    }

    public function test_where_rejects_a_field_enum_from_a_different_dataset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not belong to dataset "m9d7-ebf2"');

        $this->newBuilder()->where(RegisteredVehicleFuelField::FuelDescription, 'Benzine');
    }

    public function test_where_in_rejects_null_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('whereIn does not accept null values');

        $this->newBuilder()->whereIn(RegisteredVehicleField::LicensePlate, ['AB-12-CD', null]);
    }

    public function test_where_in_rejects_an_empty_list(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty value list');

        $this->newBuilder()->whereIn(RegisteredVehicleField::LicensePlate, []);
    }

    public function test_limit_must_be_at_least_one(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->newBuilder()->limit(0);
    }

    public function test_offset_must_not_be_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->newBuilder()->offset(-1);
    }

    public function test_get_projection_returns_raw_rows(): void
    {
        $builder = $this->newBuilder([
            new Response(200, [], json_encode([
                ['merk' => 'VOLKSWAGEN', 'count' => '3'],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $rows = $builder
            ->select(RegisteredVehicleField::Brand)
            ->count(null, 'count')
            ->groupBy(RegisteredVehicleField::Brand)
            ->getProjection();

        self::assertSame([['merk' => 'VOLKSWAGEN', 'count' => '3']], $rows);
    }

    /**
     * @param list<Response> $responses
     * @return QueryBuilder<RegisteredVehicle>
     */
    private function newBuilder(array $responses = []): QueryBuilder
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        $this->spy->attach($handler);

        $guzzle = new Client([
            'handler' => $handler,
            'base_uri' => 'https://opendata.rdw.nl/',
        ]);

        $schema = (new SchemaRegistry())->get(DatasetId::RegisteredVehicles);

        return new QueryBuilder(
            $schema,
            new SocrataClient(new Configuration(), $guzzle),
            RegisteredVehicle::class,
        );
    }
}
