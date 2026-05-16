<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "jhie-znh9".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleBodyworkSpecificationField: string
{
    case LicensePlate = 'kenteken';
    case SequenceNumber = 'carrosserie_volgnummer';
    case BodyworkCodeSequenceNumber = 'carrosserie_voertuig_nummer_code_volgnummer';
    case BodyworkCode = 'carrosseriecode';
    case EuropeanBodyworkCodeDescription = 'carrosserie_voertuig_nummer_europese_omschrijving';
}
