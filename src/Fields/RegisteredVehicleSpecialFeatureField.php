<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "7ug8-2dtt".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleSpecialFeatureField: string
{
    case LicensePlate = 'kenteken';
    case SequenceNumber = 'bijzonderheid_volgnummer';
    case SpecialFeatureCode = 'bijzonderheid_code';
    case SpecialFeatureDescription = 'bijzonderheid_code_1';
    case VariableText = 'bijzonderheid_variabele_tekst';
    case Unit = 'bijzonderheid_eenheid';
}
