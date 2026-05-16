<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleBodyworkSpecificationField;
use NiekNijland\RDW\Records\RegisteredVehicleBodyworkSpecification;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleBodyworkSpecificationOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleBodyworkSpecifications,
            recordClass: RegisteredVehicleBodyworkSpecification::class,
            fieldEnumClass: RegisteredVehicleBodyworkSpecificationField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('carrosserie_volgnummer', 'SequenceNumber', 'sequenceNumber', CastType::String),
                new FieldDescriptor('carrosserie_voertuig_nummer_code_volgnummer', 'BodyworkCodeSequenceNumber', 'bodyworkCodeSequenceNumber', CastType::String),
                new FieldDescriptor('carrosseriecode', 'BodyworkCode', 'bodyworkCode', CastType::String),
                new FieldDescriptor('carrosserie_voertuig_nummer_europese_omschrijving', 'EuropeanBodyworkCodeDescription', 'europeanBodyworkCodeDescription', CastType::String),
            ],
        );
    }
}
