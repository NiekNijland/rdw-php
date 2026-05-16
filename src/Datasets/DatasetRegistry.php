<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Datasets;

use NiekNijland\RDW\Exceptions\DatasetNotFoundException;

final class DatasetRegistry
{
    /** @var array<string, DatasetDefinition> indexed by dataset id (e.g. m9d7-ebf2) */
    private array $byId;

    /** @var array<string, DatasetDefinition> indexed by public name */
    private array $byName;

    public function __construct()
    {
        $this->byId = [];
        $this->byName = [];

        foreach (self::defaultDefinitions() as $definition) {
            $this->byId[$definition->id->value] = $definition;
            $this->byName[$definition->publicName] = $definition;
        }
    }

    /**
     * @return list<DatasetDefinition>
     */
    public function all(): array
    {
        return array_values($this->byId);
    }

    public function get(DatasetId $id): DatasetDefinition
    {
        return $this->byId[$id->value]
            ?? throw DatasetNotFoundException::forId($id->value);
    }

    public function getByName(string $publicName): DatasetDefinition
    {
        return $this->byName[$publicName]
            ?? throw DatasetNotFoundException::forName($publicName);
    }

    /**
     * @return list<DatasetDefinition>
     */
    private static function defaultDefinitions(): array
    {
        return [
            new DatasetDefinition(DatasetId::RegisteredVehicles, 'registeredVehicles'),
            new DatasetDefinition(DatasetId::RegisteredVehicleFuels, 'registeredVehicleFuels'),
            new DatasetDefinition(DatasetId::RegisteredVehicleAxles, 'registeredVehicleAxles'),
            new DatasetDefinition(DatasetId::RegisteredVehicleBodyworks, 'registeredVehicleBodyworks'),
            new DatasetDefinition(DatasetId::RegisteredVehicleBodyworkSpecifications, 'registeredVehicleBodyworkSpecifications'),
            new DatasetDefinition(DatasetId::RegisteredVehicleClasses, 'registeredVehicleClasses'),
            new DatasetDefinition(DatasetId::RegisteredVehicleSubcategories, 'registeredVehicleSubcategories'),
            new DatasetDefinition(DatasetId::RegisteredVehicleSpecialFeatures, 'registeredVehicleSpecialFeatures'),
            new DatasetDefinition(DatasetId::RegisteredVehicleTrackSets, 'registeredVehicleTrackSets'),
            new DatasetDefinition(DatasetId::OdometerJudgementExplanations, 'odometerJudgementExplanations'),
        ];
    }
}
