<?php

declare(strict_types=1);

namespace NiekNijland\RDW;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Datasets\DatasetRegistry;
use NiekNijland\RDW\Http\Configuration;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Query\QueryBuilder;
use NiekNijland\RDW\Relations\Relations;
use NiekNijland\RDW\Schema\SchemaRegistry;

/**
 * Public entry point for the RDW PHP package.
 *
 * Construct it with an optional {@see Configuration} (app token, timeout,
 * user agent) and call one of the dataset methods to obtain a typed
 * {@see QueryBuilder}. Raw row and metadata fetches are also exposed for
 * advanced use cases that the typed API does not cover.
 */
class Rdw
{
    private readonly SocrataClient $http;

    private readonly DatasetRegistry $datasets;

    private readonly SchemaRegistry $schemas;

    public function __construct(
        private readonly Configuration $configuration = new Configuration(),
        ?SocrataClient $http = null,
        ?DatasetRegistry $datasets = null,
        ?SchemaRegistry $schemas = null,
    ) {
        $this->http = $http ?? new SocrataClient($this->configuration);
        $this->datasets = $datasets ?? new DatasetRegistry();
        $this->schemas = $schemas ?? new SchemaRegistry();
    }

    public function configuration(): Configuration
    {
        return $this->configuration;
    }

    public function datasets(): DatasetRegistry
    {
        return $this->datasets;
    }

    public function schemas(): SchemaRegistry
    {
        return $this->schemas;
    }

    public function http(): SocrataClient
    {
        return $this->http;
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicle>
     */
    public function registeredVehicles(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicles, Records\RegisteredVehicle::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleFuel>
     */
    public function registeredVehicleFuels(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleFuels, Records\RegisteredVehicleFuel::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleAxle>
     */
    public function registeredVehicleAxles(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleAxles, Records\RegisteredVehicleAxle::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleBodywork>
     */
    public function registeredVehicleBodyworks(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleBodyworks, Records\RegisteredVehicleBodywork::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleBodyworkSpecification>
     */
    public function registeredVehicleBodyworkSpecifications(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleBodyworkSpecifications, Records\RegisteredVehicleBodyworkSpecification::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleClass>
     */
    public function registeredVehicleClasses(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleClasses, Records\RegisteredVehicleClass::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleSubcategory>
     */
    public function registeredVehicleSubcategories(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleSubcategories, Records\RegisteredVehicleSubcategory::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleSpecialFeature>
     */
    public function registeredVehicleSpecialFeatures(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleSpecialFeatures, Records\RegisteredVehicleSpecialFeature::class);
    }

    /**
     * @return QueryBuilder<Records\RegisteredVehicleTrackSet>
     */
    public function registeredVehicleTrackSets(): QueryBuilder
    {
        return $this->builderFor(DatasetId::RegisteredVehicleTrackSets, Records\RegisteredVehicleTrackSet::class);
    }

    /**
     * @return QueryBuilder<Records\OdometerJudgementExplanation>
     */
    public function odometerJudgementExplanations(): QueryBuilder
    {
        return $this->builderFor(DatasetId::OdometerJudgementExplanations, Records\OdometerJudgementExplanation::class);
    }

    /**
     * @template TRecord of object
     *
     * @param class-string<TRecord> $recordClass
     * @return QueryBuilder<TRecord>
     */
    private function builderFor(DatasetId $dataset, string $recordClass): QueryBuilder
    {
        return new QueryBuilder($this->schemas->get($dataset), $this->http, $recordClass);
    }

    public function relations(): Relations
    {
        return new Relations($this);
    }

    /**
     * Raw row fetch for a known dataset.
     * Returns associative rows as the Socrata API delivers them.
     *
     * @param array<string, scalar|null> $query
     * @return list<array<string, mixed>>
     */
    public function rawRows(DatasetId $dataset, array $query = []): array
    {
        return $this->http->getRows($dataset->value, $query);
    }

    /**
     * Raw metadata fetch for a known dataset.
     *
     * @return array<string, mixed>
     */
    public function rawMetadata(DatasetId $dataset): array
    {
        return $this->http->getMetadata($dataset->value);
    }
}
