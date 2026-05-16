<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Query;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
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
