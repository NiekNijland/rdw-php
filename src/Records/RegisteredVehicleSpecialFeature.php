<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "7ug8-2dtt".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleSpecialFeature
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?int $sequenceNumber = null,
        public ?int $specialFeatureCode = null,
        public ?string $specialFeatureDescription = null,
        public ?string $variableText = null,
        public ?string $unit = null,
    ) {
    }
}
