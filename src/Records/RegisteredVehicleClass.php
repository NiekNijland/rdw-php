<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "kmfi-hrps".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleClass
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?string $bodyworkSequenceNumber = null,
        public ?string $classSequenceNumber = null,
        public ?string $vehicleClass = null,
        public ?string $vehicleClassDescription = null,
    ) {
    }
}
