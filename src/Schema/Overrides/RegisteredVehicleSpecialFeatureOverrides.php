<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleSpecialFeatureField;
use NiekNijland\RDW\Records\RegisteredVehicleSpecialFeature;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleSpecialFeatureOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleSpecialFeatures,
            recordClass: RegisteredVehicleSpecialFeature::class,
            fieldEnumClass: RegisteredVehicleSpecialFeatureField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('bijzonderheid_volgnummer', 'SequenceNumber', 'sequenceNumber', CastType::Integer),
                new FieldDescriptor('bijzonderheid_code', 'SpecialFeatureCode', 'specialFeatureCode', CastType::Integer),
                new FieldDescriptor('bijzonderheid_code_1', 'SpecialFeatureDescription', 'specialFeatureDescription', CastType::String),
                new FieldDescriptor('bijzonderheid_variabele_tekst', 'VariableText', 'variableText', CastType::String),
                new FieldDescriptor('bijzonderheid_eenheid', 'Unit', 'unit', CastType::String),
            ],
        );
    }
}
