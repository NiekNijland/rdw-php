<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleAxleField;
use NiekNijland\RDW\Records\RegisteredVehicleAxle;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleAxleOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleAxles,
            recordClass: RegisteredVehicleAxle::class,
            fieldEnumClass: RegisteredVehicleAxleField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('as_nummer', 'AxleNumber', 'axleNumber', CastType::Integer),
                new FieldDescriptor('aantal_assen', 'AxleCount', 'axleCount', CastType::Integer),
                new FieldDescriptor('aangedreven_as', 'IsDriven', 'isDriven', CastType::Boolean),
                new FieldDescriptor('hefas', 'IsLiftAxle', 'isLiftAxle', CastType::Boolean),
                new FieldDescriptor('plaatscode_as', 'AxlePositionCode', 'axlePositionCode', CastType::String),
                new FieldDescriptor('spoorbreedte', 'TrackWidth', 'trackWidth', CastType::Integer),
                new FieldDescriptor('weggedrag_code', 'RoadHandlingCode', 'roadHandlingCode', CastType::String),
                new FieldDescriptor('wettelijk_toegestane_maximum_aslast', 'LegalMaximumAxleLoad', 'legalMaximumAxleLoad', CastType::Integer),
                new FieldDescriptor('technisch_toegestane_maximum_aslast', 'TechnicalMaximumAxleLoad', 'technicalMaximumAxleLoad', CastType::Integer),
                new FieldDescriptor('geremde_as_indicator', 'IsBraked', 'isBraked', CastType::Boolean),
                new FieldDescriptor('afstand_tot_volgende_as_voertuig', 'DistanceToNextAxle', 'distanceToNextAxle', CastType::Integer),
                new FieldDescriptor('afstand_tot_volgende_as_voertuig_minimum', 'DistanceToNextAxleMinimum', 'distanceToNextAxleMinimum', CastType::Integer),
                new FieldDescriptor('afstand_tot_volgende_as_voertuig_maximum', 'DistanceToNextAxleMaximum', 'distanceToNextAxleMaximum', CastType::Integer),
                new FieldDescriptor('maximum_last_as_technisch_maximum', 'TechnicalMaximumAxleLoadUpper', 'technicalMaximumAxleLoadUpper', CastType::Integer),
                new FieldDescriptor('maximum_last_as_technisch_minimum', 'TechnicalMaximumAxleLoadLower', 'technicalMaximumAxleLoadLower', CastType::Integer),
            ],
        );
    }
}
