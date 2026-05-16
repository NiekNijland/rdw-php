<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleSubcategoryField;
use NiekNijland\RDW\Records\RegisteredVehicleSubcategory;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleSubcategoryOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleSubcategories,
            recordClass: RegisteredVehicleSubcategory::class,
            fieldEnumClass: RegisteredVehicleSubcategoryField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('subcategorie_voertuig_volgnummer', 'SequenceNumber', 'sequenceNumber', CastType::Integer),
                new FieldDescriptor('subcategorie_voertuig_europees', 'EuropeanSubcategory', 'europeanSubcategory', CastType::String),
                new FieldDescriptor('subcategorie_voertuig_europees_omschrijving', 'EuropeanSubcategoryDescription', 'europeanSubcategoryDescription', CastType::String),
            ],
        );
    }
}
