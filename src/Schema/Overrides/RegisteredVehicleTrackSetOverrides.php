<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleTrackSetField;
use NiekNijland\RDW\Records\RegisteredVehicleTrackSet;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleTrackSetOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleTrackSets,
            recordClass: RegisteredVehicleTrackSet::class,
            fieldEnumClass: RegisteredVehicleTrackSetField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('rupsband_set_volgnr', 'SequenceNumber', 'sequenceNumber', CastType::Integer),
                new FieldDescriptor('geremde_rupsband_indicator', 'IsBraked', 'isBraked', CastType::Boolean),
                new FieldDescriptor('aangedreven_rupsband_indicator', 'IsDriven', 'isDriven', CastType::Boolean),
                new FieldDescriptor('technisch_toelaatbaar_maximum', 'TechnicalMaximumMass', 'technicalMaximumMass', CastType::Integer),
                new FieldDescriptor('technisch_toelaatbaar_maximum_1', 'TechnicalMaximumMassMinimum', 'technicalMaximumMassMinimum', CastType::Integer),
                new FieldDescriptor('technisch_toelaatbaar_maximum_2', 'TechnicalMaximumMassMaximum', 'technicalMaximumMassMaximum', CastType::Integer),
            ],
        );
    }
}
