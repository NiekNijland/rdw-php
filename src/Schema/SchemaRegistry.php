<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Exceptions\DatasetNotFoundException;
use NiekNijland\RDW\Schema\Overrides\OdometerJudgementExplanationOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleAxleOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleBodyworkOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleBodyworkSpecificationOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleClassOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleFuelOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleSpecialFeatureOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleSubcategoryOverrides;
use NiekNijland\RDW\Schema\Overrides\RegisteredVehicleTrackSetOverrides;

final class SchemaRegistry
{
    /** @var array<string, DatasetSchema> indexed by DatasetId->value */
    private array $byId;

    public function __construct()
    {
        $this->byId = [];

        foreach (self::all() as $schema) {
            $this->byId[$schema->datasetId->value] = $schema;
        }
    }

    public function get(DatasetId $id): DatasetSchema
    {
        return $this->byId[$id->value]
            ?? throw DatasetNotFoundException::forId($id->value);
    }

    /**
     * @return list<DatasetSchema>
     */
    public static function all(): array
    {
        return [
            RegisteredVehicleOverrides::schema(),
            RegisteredVehicleFuelOverrides::schema(),
            RegisteredVehicleAxleOverrides::schema(),
            RegisteredVehicleBodyworkOverrides::schema(),
            RegisteredVehicleBodyworkSpecificationOverrides::schema(),
            RegisteredVehicleClassOverrides::schema(),
            RegisteredVehicleSubcategoryOverrides::schema(),
            RegisteredVehicleSpecialFeatureOverrides::schema(),
            RegisteredVehicleTrackSetOverrides::schema(),
            OdometerJudgementExplanationOverrides::schema(),
        ];
    }
}
