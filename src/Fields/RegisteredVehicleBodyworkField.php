<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "vezc-m2t6".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleBodyworkField: string
{
    case LicensePlate = 'kenteken';
    case SequenceNumber = 'carrosserie_volgnummer';
    case BodyworkType = 'carrosserietype';
    case EuropeanBodyworkDescription = 'type_carrosserie_europese_omschrijving';
}
