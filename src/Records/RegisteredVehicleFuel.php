<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "8ys7-d773".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleFuel
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?string $sequenceNumber = null,
        public ?string $fuelDescription = null,
        public ?string $fuelConsumptionOuter = null,
        public ?string $fuelConsumptionCombined = null,
        public ?string $fuelConsumptionCity = null,
        public ?string $co2EmissionsCombined = null,
        public ?string $co2EmissionsWeighted = null,
        public ?string $noiseLevelDriving = null,
        public ?string $noiseLevelStationary = null,
        public ?string $emissionClass = null,
        public ?string $environmentalClassLight = null,
        public ?string $environmentalClassHeavy = null,
        public ?string $particulateEmissionsLight = null,
        public ?string $particulateEmissionsHeavy = null,
        public ?string $netMaximumPower = null,
        public ?string $nominalContinuousMaximumPower = null,
        public ?string $sootEmissions = null,
        public ?string $noiseLevelRpm = null,
        public ?string $wltpParticulateEmissionsType1 = null,
        public ?string $wltpCo2EmissionsCombined = null,
        public ?string $wltpCo2EmissionsWeightedCombined = null,
        public ?string $wltpFuelConsumptionCombined = null,
        public ?string $wltpFuelConsumptionWeightedCombined = null,
        public ?string $wltpElectricConsumptionElectricOnly = null,
        public ?string $wltpRangeElectricOnly = null,
        public ?string $wltpRangeElectricOnlyCity = null,
        public ?string $wltpElectricConsumptionExternalCharging = null,
        public ?string $wltpRangeExternalCharging = null,
        public ?string $wltpRangeExternalChargingCity = null,
        public ?string $maximumPower15Minutes = null,
        public ?string $maximumPower60Minutes = null,
        public ?string $netMaximumPowerElectric = null,
        public ?string $hybridElectricVehicleClass = null,
        public ?int $declaredMaximumSpeed = null,
        public ?string $exhaustEmissionLevel = null,
        public ?string $co2EmissionClass = null,
        public ?string $fuelConsumptionWeightedCombined = null,
        public ?string $electricityConsumptionWeightedCombined = null,
        public ?string $rangeExternalChargeable = null,
        public ?string $range = null,
        public ?string $electricityConsumptionFullyElectric = null,
    ) {
    }
}
