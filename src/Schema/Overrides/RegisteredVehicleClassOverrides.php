<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleClassField;
use NiekNijland\RDW\Records\RegisteredVehicleClass;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleClassOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleClasses,
            recordClass: RegisteredVehicleClass::class,
            fieldEnumClass: RegisteredVehicleClassField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('carrosserie_volgnummer', 'BodyworkSequenceNumber', 'bodyworkSequenceNumber', CastType::String),
                new FieldDescriptor('carrosserie_klasse_volgnummer', 'ClassSequenceNumber', 'classSequenceNumber', CastType::String),
                new FieldDescriptor('voertuigklasse', 'VehicleClass', 'vehicleClass', CastType::String),
                new FieldDescriptor('voertuigklasse_omschrijving', 'VehicleClassDescription', 'vehicleClassDescription', CastType::String),
            ],
        );
    }
}
