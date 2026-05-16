<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Datasets;

enum DatasetId: string
{
    case RegisteredVehicles = 'm9d7-ebf2';
    case RegisteredVehicleFuels = '8ys7-d773';
    case RegisteredVehicleAxles = '3huj-srit';
    case RegisteredVehicleBodyworks = 'vezc-m2t6';
    case RegisteredVehicleBodyworkSpecifications = 'jhie-znh9';
    case RegisteredVehicleClasses = 'kmfi-hrps';
    case RegisteredVehicleSubcategories = '2ba7-embk';
    case RegisteredVehicleSpecialFeatures = '7ug8-2dtt';
    case RegisteredVehicleTrackSets = '3xwf-ince';
    case OdometerJudgementExplanations = 'jqs4-4kvw';
}
