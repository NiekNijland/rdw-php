<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleBodyworkField;
use NiekNijland\RDW\Records\RegisteredVehicleBodywork;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleBodyworkOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleBodyworks,
            recordClass: RegisteredVehicleBodywork::class,
            fieldEnumClass: RegisteredVehicleBodyworkField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('carrosserie_volgnummer', 'SequenceNumber', 'sequenceNumber', CastType::String),
                new FieldDescriptor('carrosserietype', 'BodyworkType', 'bodyworkType', CastType::String),
                new FieldDescriptor('type_carrosserie_europese_omschrijving', 'EuropeanBodyworkDescription', 'europeanBodyworkDescription', CastType::String),
            ],
        );
    }
}
