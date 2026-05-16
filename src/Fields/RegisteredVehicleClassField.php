<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "kmfi-hrps".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleClassField: string
{
    case LicensePlate = 'kenteken';
    case BodyworkSequenceNumber = 'carrosserie_volgnummer';
    case ClassSequenceNumber = 'carrosserie_klasse_volgnummer';
    case VehicleClass = 'voertuigklasse';
    case VehicleClassDescription = 'voertuigklasse_omschrijving';
}
