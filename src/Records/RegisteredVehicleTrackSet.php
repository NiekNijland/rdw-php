<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "3xwf-ince".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleTrackSet
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?int $sequenceNumber = null,
        public ?bool $isBraked = null,
        public ?bool $isDriven = null,
        public ?int $technicalMaximumMass = null,
        public ?int $technicalMaximumMassMinimum = null,
        public ?int $technicalMaximumMassMaximum = null,
    ) {
    }
}
