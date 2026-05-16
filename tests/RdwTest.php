<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Http\Configuration;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Query\QueryBuilder;
use NiekNijland\RDW\Rdw;
use NiekNijland\RDW\Relations\Relations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Rdw::class)]
final class RdwTest extends TestCase
{
    public function test_exposes_dataset_registry_and_configuration(): void
    {
        $rdw = new Rdw();

        self::assertCount(10, $rdw->datasets()->all());
        self::assertSame(Configuration::DEFAULT_BASE_URL, $rdw->configuration()->baseUrl);
    }

    public function test_configuration_reads_through_the_injected_http_client(): void
    {
        $config = new Configuration(appToken: 'tok-from-http');

        $rdw = new Rdw(http: new SocrataClient($config));

        self::assertSame('tok-from-http', $rdw->configuration()->appToken);
    }

    public function test_constructor_rejects_both_configuration_and_socrata_client(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not both');

        new Rdw(
            configuration: new Configuration(appToken: 'X'),
            http: new SocrataClient(new Configuration()),
        );
    }

    /**
     * @return iterable<string, array{0: callable(Rdw): QueryBuilder<object>, 1: DatasetId, 2: string}>
     */
    public static function builderMethods(): iterable
    {
        yield 'registeredVehicles' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicles(),
            DatasetId::RegisteredVehicles,
            \NiekNijland\RDW\Records\RegisteredVehicle::class,
        ];
        yield 'registeredVehicleFuels' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleFuels(),
            DatasetId::RegisteredVehicleFuels,
            \NiekNijland\RDW\Records\RegisteredVehicleFuel::class,
        ];
        yield 'registeredVehicleAxles' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleAxles(),
            DatasetId::RegisteredVehicleAxles,
            \NiekNijland\RDW\Records\RegisteredVehicleAxle::class,
        ];
        yield 'registeredVehicleBodyworks' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleBodyworks(),
            DatasetId::RegisteredVehicleBodyworks,
            \NiekNijland\RDW\Records\RegisteredVehicleBodywork::class,
        ];
        yield 'registeredVehicleBodyworkSpecifications' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleBodyworkSpecifications(),
            DatasetId::RegisteredVehicleBodyworkSpecifications,
            \NiekNijland\RDW\Records\RegisteredVehicleBodyworkSpecification::class,
        ];
        yield 'registeredVehicleClasses' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleClasses(),
            DatasetId::RegisteredVehicleClasses,
            \NiekNijland\RDW\Records\RegisteredVehicleClass::class,
        ];
        yield 'registeredVehicleSubcategories' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleSubcategories(),
            DatasetId::RegisteredVehicleSubcategories,
            \NiekNijland\RDW\Records\RegisteredVehicleSubcategory::class,
        ];
        yield 'registeredVehicleSpecialFeatures' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleSpecialFeatures(),
            DatasetId::RegisteredVehicleSpecialFeatures,
            \NiekNijland\RDW\Records\RegisteredVehicleSpecialFeature::class,
        ];
        yield 'registeredVehicleTrackSets' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->registeredVehicleTrackSets(),
            DatasetId::RegisteredVehicleTrackSets,
            \NiekNijland\RDW\Records\RegisteredVehicleTrackSet::class,
        ];
        yield 'odometerJudgementExplanations' => [
            static fn (Rdw $rdw): QueryBuilder => $rdw->odometerJudgementExplanations(),
            DatasetId::OdometerJudgementExplanations,
            \NiekNijland\RDW\Records\OdometerJudgementExplanation::class,
        ];
    }

    /**
     * @param callable(Rdw): QueryBuilder<object> $factory
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('builderMethods')]
    public function test_typed_builder_method_binds_to_matching_dataset_and_record(
        callable $factory,
        DatasetId $expectedDataset,
        string $expectedRecord,
    ): void {
        $builder = $factory(new Rdw());

        self::assertInstanceOf(QueryBuilder::class, $builder);

        $reflection = new ReflectionClass($builder);
        $schemaProp = $reflection->getProperty('schema');
        $schema = $schemaProp->getValue($builder);

        self::assertInstanceOf(\NiekNijland\RDW\Schema\DatasetSchema::class, $schema);
        self::assertSame($expectedDataset, $schema->datasetId);
        self::assertSame($expectedRecord, $schema->recordClass);
    }

    public function test_relations_returns_a_loader_bound_to_this_rdw_instance(): void
    {
        $rdw = new Rdw();

        self::assertInstanceOf(Relations::class, $rdw->relations());
    }

    public function test_raw_rows_delegates_to_the_socrata_client(): void
    {
        $guzzle = new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(200, [], json_encode([['kenteken' => 'AB-123-C']], JSON_THROW_ON_ERROR)),
            ])),
            'base_uri' => 'https://opendata.rdw.nl/',
        ]);

        $rdw = new Rdw(http: new SocrataClient(new Configuration(), $guzzle));

        $rows = $rdw->rawRows(DatasetId::RegisteredVehicles, ['kenteken' => 'AB-123-C']);

        self::assertSame([['kenteken' => 'AB-123-C']], $rows);
    }
}
