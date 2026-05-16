<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "8ys7-d773".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleFuelField: string
{
    case LicensePlate = 'kenteken';
    case SequenceNumber = 'brandstof_volgnummer';
    case FuelDescription = 'brandstof_omschrijving';
    case FuelConsumptionOuter = 'brandstofverbruik_buiten';
    case FuelConsumptionCombined = 'brandstofverbruik_gecombineerd';
    case FuelConsumptionCity = 'brandstofverbruik_stad';
    case Co2EmissionsCombined = 'co2_uitstoot_gecombineerd';
    case Co2EmissionsWeighted = 'co2_uitstoot_gewogen';
    case NoiseLevelDriving = 'geluidsniveau_rijdend';
    case NoiseLevelStationary = 'geluidsniveau_stationair';
    case EmissionClass = 'emissiecode_omschrijving';
    case EnvironmentalClassLight = 'milieuklasse_eg_goedkeuring_licht';
    case EnvironmentalClassHeavy = 'milieuklasse_eg_goedkeuring_zwaar';
    case ParticulateEmissionsLight = 'uitstoot_deeltjes_licht';
    case ParticulateEmissionsHeavy = 'uitstoot_deeltjes_zwaar';
    case NetMaximumPower = 'nettomaximumvermogen';
    case NominalContinuousMaximumPower = 'nominaal_continu_maximumvermogen';
    case SootEmissions = 'roetuitstoot';
    case NoiseLevelRpm = 'toerental_geluidsniveau';
    case WltpParticulateEmissionsType1 = 'emis_deeltjes_type1_wltp';
    case WltpCo2EmissionsCombined = 'emissie_co2_gecombineerd_wltp';
    case WltpCo2EmissionsWeightedCombined = 'emis_co2_gewogen_gecombineerd_wltp';
    case WltpFuelConsumptionCombined = 'brandstof_verbruik_gecombineerd_wltp';
    case WltpFuelConsumptionWeightedCombined = 'brandstof_verbruik_gewogen_gecombineerd_wltp';
    case WltpElectricConsumptionElectricOnly = 'elektrisch_verbruik_enkel_elektrisch_wltp';
    case WltpRangeElectricOnly = 'actie_radius_enkel_elektrisch_wltp';
    case WltpRangeElectricOnlyCity = 'actie_radius_enkel_elektrisch_stad_wltp';
    case WltpElectricConsumptionExternalCharging = 'elektrisch_verbruik_extern_opladen_wltp';
    case WltpRangeExternalCharging = 'actie_radius_extern_opladen_wltp';
    case WltpRangeExternalChargingCity = 'actie_radius_extern_opladen_stad_wltp';
    case MaximumPower15Minutes = 'max_vermogen_15_minuten';
    case MaximumPower60Minutes = 'max_vermogen_60_minuten';
    case NetMaximumPowerElectric = 'netto_max_vermogen_elektrisch';
    case HybridElectricVehicleClass = 'klasse_hybride_elektrisch_voertuig';
    case DeclaredMaximumSpeed = 'opgegeven_maximum_snelheid';
    case ExhaustEmissionLevel = 'uitlaatemissieniveau';
    case Co2EmissionClass = 'co2_emissieklasse';
    case FuelConsumptionWeightedCombined = 'brandstofverbruik_gewogen_gecombineerd';
    case ElectricityConsumptionWeightedCombined = 'elektriciteitsverbruik_gewogen_gecombineerd';
    case RangeExternalChargeable = 'actieradius_extern_oplaadbaar';
    case Range = 'actieradius';
    case ElectricityConsumptionFullyElectric = 'elektriciteitsverbruik_volledig_elektrisch';
}
