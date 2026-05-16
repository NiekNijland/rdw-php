<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "3huj-srit".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleAxleField: string
{
    case LicensePlate = 'kenteken';
    case AxleNumber = 'as_nummer';
    case AxleCount = 'aantal_assen';
    case IsDriven = 'aangedreven_as';
    case IsLiftAxle = 'hefas';
    case AxlePositionCode = 'plaatscode_as';
    case TrackWidth = 'spoorbreedte';
    case RoadHandlingCode = 'weggedrag_code';
    case LegalMaximumAxleLoad = 'wettelijk_toegestane_maximum_aslast';
    case TechnicalMaximumAxleLoad = 'technisch_toegestane_maximum_aslast';
    case IsBraked = 'geremde_as_indicator';
    case DistanceToNextAxle = 'afstand_tot_volgende_as_voertuig';
    case DistanceToNextAxleMinimum = 'afstand_tot_volgende_as_voertuig_minimum';
    case DistanceToNextAxleMaximum = 'afstand_tot_volgende_as_voertuig_maximum';
    case TechnicalMaximumAxleLoadUpper = 'maximum_last_as_technisch_maximum';
    case TechnicalMaximumAxleLoadLower = 'maximum_last_as_technisch_minimum';
}
