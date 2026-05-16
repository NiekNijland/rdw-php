<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Relations;

use NiekNijland\RDW\Datasets\DatasetId;

/**
 * The canonical map of relations between in-scope RDW datasets.
 *
 * The map is checked into source control so that any future change to a
 * key shape (for example introducing a new sequence column) is a
 * deliberate edit rather than something silently inferred from data.
 */
final class RelationRegistry
{
    /**
     * @return list<RelationDefinition>
     */
    public static function all(): array
    {
        return [
            new RelationDefinition(
                name: 'fuels',
                from: DatasetId::RegisteredVehicles,
                to: DatasetId::RegisteredVehicleFuels,
                sourceProperties: ['licensePlate'],
                targetFields: ['kenteken'],
                cardinality: Cardinality::OneToMany,
            ),
            new RelationDefinition(
                name: 'axles',
                from: DatasetId::RegisteredVehicles,
                to: DatasetId::RegisteredVehicleAxles,
                sourceProperties: ['licensePlate'],
                targetFields: ['kenteken'],
                cardinality: Cardinality::OneToMany,
            ),
            new RelationDefinition(
                name: 'bodyworks',
                from: DatasetId::RegisteredVehicles,
                to: DatasetId::RegisteredVehicleBodyworks,
                sourceProperties: ['licensePlate'],
                targetFields: ['kenteken'],
                cardinality: Cardinality::OneToMany,
            ),
            new RelationDefinition(
                name: 'subcategories',
                from: DatasetId::RegisteredVehicles,
                to: DatasetId::RegisteredVehicleSubcategories,
                sourceProperties: ['licensePlate'],
                targetFields: ['kenteken'],
                cardinality: Cardinality::OneToMany,
            ),
            new RelationDefinition(
                name: 'specialFeatures',
                from: DatasetId::RegisteredVehicles,
                to: DatasetId::RegisteredVehicleSpecialFeatures,
                sourceProperties: ['licensePlate'],
                targetFields: ['kenteken'],
                cardinality: Cardinality::OneToMany,
            ),
            new RelationDefinition(
                name: 'trackSets',
                from: DatasetId::RegisteredVehicles,
                to: DatasetId::RegisteredVehicleTrackSets,
                sourceProperties: ['licensePlate'],
                targetFields: ['kenteken'],
                cardinality: Cardinality::OneToMany,
            ),
            new RelationDefinition(
                name: 'odometerJudgement',
                from: DatasetId::RegisteredVehicles,
                to: DatasetId::OdometerJudgementExplanations,
                sourceProperties: ['odometerJudgementCode'],
                targetFields: ['code_toelichting_tellerstandoordeel'],
                cardinality: Cardinality::ManyToOne,
            ),
            new RelationDefinition(
                name: 'specifications',
                from: DatasetId::RegisteredVehicleBodyworks,
                to: DatasetId::RegisteredVehicleBodyworkSpecifications,
                sourceProperties: ['licensePlate', 'sequenceNumber'],
                targetFields: ['kenteken', 'carrosserie_volgnummer'],
                cardinality: Cardinality::OneToMany,
            ),
            new RelationDefinition(
                name: 'vehicleClasses',
                from: DatasetId::RegisteredVehicleBodyworks,
                to: DatasetId::RegisteredVehicleClasses,
                sourceProperties: ['licensePlate', 'sequenceNumber'],
                targetFields: ['kenteken', 'carrosserie_volgnummer'],
                cardinality: Cardinality::OneToMany,
            ),
        ];
    }
}
