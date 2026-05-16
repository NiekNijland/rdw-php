<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\OdometerJudgementExplanationField;
use NiekNijland\RDW\Records\OdometerJudgementExplanation;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class OdometerJudgementExplanationOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::OdometerJudgementExplanations,
            recordClass: OdometerJudgementExplanation::class,
            fieldEnumClass: OdometerJudgementExplanationField::class,
            fields: [
                new FieldDescriptor('code_toelichting_tellerstandoordeel', 'OdometerJudgementCode', 'odometerJudgementCode', CastType::String),
                new FieldDescriptor('toelichting_tellerstandoordeel', 'OdometerJudgementDescription', 'odometerJudgementDescription', CastType::String),
            ],
        );
    }
}
