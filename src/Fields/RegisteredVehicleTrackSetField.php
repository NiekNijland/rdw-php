<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "3xwf-ince".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleTrackSetField: string
{
    case LicensePlate = 'kenteken';
    case SequenceNumber = 'rupsband_set_volgnr';
    case IsBraked = 'geremde_rupsband_indicator';
    case IsDriven = 'aangedreven_rupsband_indicator';
    case TechnicalMaximumMass = 'technisch_toelaatbaar_maximum';
    case TechnicalMaximumMassMinimum = 'technisch_toelaatbaar_maximum_1';
    case TechnicalMaximumMassMaximum = 'technisch_toelaatbaar_maximum_2';
}
