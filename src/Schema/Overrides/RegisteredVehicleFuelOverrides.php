<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema\Overrides;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleFuelField;
use NiekNijland\RDW\Records\RegisteredVehicleFuel;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;

final class RegisteredVehicleFuelOverrides
{
    public static function schema(): DatasetSchema
    {
        return new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicleFuels,
            recordClass: RegisteredVehicleFuel::class,
            fieldEnumClass: RegisteredVehicleFuelField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('brandstof_volgnummer', 'SequenceNumber', 'sequenceNumber', CastType::String),
                new FieldDescriptor('brandstof_omschrijving', 'FuelDescription', 'fuelDescription', CastType::String),
                new FieldDescriptor('brandstofverbruik_gecombineerd', 'FuelConsumptionCombined', 'fuelConsumptionCombined', CastType::Decimal),
                new FieldDescriptor('co2_uitstoot_gecombineerd', 'Co2EmissionsCombined', 'co2EmissionsCombined', CastType::Decimal),
                new FieldDescriptor('co2_uitstoot_gewogen', 'Co2EmissionsWeighted', 'co2EmissionsWeighted', CastType::Decimal),
                new FieldDescriptor('geluidsniveau_rijdend', 'NoiseLevelDriving', 'noiseLevelDriving', CastType::Decimal),
                new FieldDescriptor('geluidsniveau_stationair', 'NoiseLevelStationary', 'noiseLevelStationary', CastType::Decimal),
                new FieldDescriptor('emissiecode_omschrijving', 'EmissionClass', 'emissionClass', CastType::String),
                new FieldDescriptor('milieuklasse_eg_goedkeuring_licht', 'EnvironmentalClassLight', 'environmentalClassLight', CastType::String),
                new FieldDescriptor('milieuklasse_eg_goedkeuring_zwaar', 'EnvironmentalClassHeavy', 'environmentalClassHeavy', CastType::String),
                new FieldDescriptor('uitstoot_deeltjes_licht', 'ParticulateEmissionsLight', 'particulateEmissionsLight', CastType::Decimal),
                new FieldDescriptor('uitstoot_deeltjes_zwaar', 'ParticulateEmissionsHeavy', 'particulateEmissionsHeavy', CastType::Decimal),
                new FieldDescriptor('nettomaximumvermogen', 'NetMaximumPower', 'netMaximumPower', CastType::Decimal),
                new FieldDescriptor('nominaal_continu_maximumvermogen', 'NominalContinuousMaximumPower', 'nominalContinuousMaximumPower', CastType::Decimal),
                new FieldDescriptor('toerental_geluidsniveau', 'NoiseLevelRpm', 'noiseLevelRpm', CastType::Decimal),
                new FieldDescriptor('emis_deeltjes_type1_wltp', 'WltpParticulateEmissionsType1', 'wltpParticulateEmissionsType1', CastType::Decimal),
                new FieldDescriptor('emissie_co2_gecombineerd_wltp', 'WltpCo2EmissionsCombined', 'wltpCo2EmissionsCombined', CastType::Decimal),
                new FieldDescriptor('emis_co2_gewogen_gecombineerd_wltp', 'WltpCo2EmissionsWeightedCombined', 'wltpCo2EmissionsWeightedCombined', CastType::Decimal),
                new FieldDescriptor('brandstof_verbruik_gecombineerd_wltp', 'WltpFuelConsumptionCombined', 'wltpFuelConsumptionCombined', CastType::Decimal),
                new FieldDescriptor('brandstof_verbruik_gewogen_gecombineerd_wltp', 'WltpFuelConsumptionWeightedCombined', 'wltpFuelConsumptionWeightedCombined', CastType::Decimal),
                new FieldDescriptor('elektrisch_verbruik_enkel_elektrisch_wltp', 'WltpElectricConsumptionElectricOnly', 'wltpElectricConsumptionElectricOnly', CastType::Decimal),
                new FieldDescriptor('actie_radius_enkel_elektrisch_wltp', 'WltpRangeElectricOnly', 'wltpRangeElectricOnly', CastType::Decimal),
                new FieldDescriptor('elektrisch_verbruik_extern_opladen_wltp', 'WltpElectricConsumptionExternalCharging', 'wltpElectricConsumptionExternalCharging', CastType::Decimal),
                new FieldDescriptor('actie_radius_extern_opladen_wltp', 'WltpRangeExternalCharging', 'wltpRangeExternalCharging', CastType::Decimal),
                new FieldDescriptor('max_vermogen_15_minuten', 'MaximumPower15Minutes', 'maximumPower15Minutes', CastType::Decimal),
                new FieldDescriptor('netto_max_vermogen_elektrisch', 'NetMaximumPowerElectric', 'netMaximumPowerElectric', CastType::Decimal),
                new FieldDescriptor('klasse_hybride_elektrisch_voertuig', 'HybridElectricVehicleClass', 'hybridElectricVehicleClass', CastType::String),
                new FieldDescriptor('opgegeven_maximum_snelheid', 'DeclaredMaximumSpeed', 'declaredMaximumSpeed', CastType::Integer),
                new FieldDescriptor('uitlaatemissieniveau', 'ExhaustEmissionLevel', 'exhaustEmissionLevel', CastType::String),
                new FieldDescriptor('co2_emissieklasse', 'Co2EmissionClass', 'co2EmissionClass', CastType::String),
                new FieldDescriptor('brandstofverbruik_gewogen_gecombineerd', 'FuelConsumptionWeightedCombined', 'fuelConsumptionWeightedCombined', CastType::Decimal),
                new FieldDescriptor('elektriciteitsverbruik_gewogen_gecombineerd', 'ElectricityConsumptionWeightedCombined', 'electricityConsumptionWeightedCombined', CastType::Decimal),
                new FieldDescriptor('actieradius_extern_oplaadbaar', 'RangeExternalChargeable', 'rangeExternalChargeable', CastType::Decimal),
                new FieldDescriptor('actieradius', 'Range', 'range', CastType::Decimal),
                new FieldDescriptor('elektriciteitsverbruik_volledig_elektrisch', 'ElectricityConsumptionFullyElectric', 'electricityConsumptionFullyElectric', CastType::Decimal),
            ],
        );
    }
}
