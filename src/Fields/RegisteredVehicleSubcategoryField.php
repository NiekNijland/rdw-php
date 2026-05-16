<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "2ba7-embk".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleSubcategoryField: string
{
    case LicensePlate = 'kenteken';
    case SequenceNumber = 'subcategorie_voertuig_volgnummer';
    case EuropeanSubcategory = 'subcategorie_voertuig_europees';
    case EuropeanSubcategoryDescription = 'subcategorie_voertuig_europees_omschrijving';
}
