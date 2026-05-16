<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "3huj-srit".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleAxle
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?int $axleNumber = null,
        public ?int $axleCount = null,
        public ?bool $isDriven = null,
        public ?bool $isLiftAxle = null,
        public ?string $axlePositionCode = null,
        public ?int $trackWidth = null,
        public ?string $roadHandlingCode = null,
        public ?int $legalMaximumAxleLoad = null,
        public ?int $technicalMaximumAxleLoad = null,
        public ?bool $isBraked = null,
        public ?int $distanceToNextAxle = null,
        public ?int $distanceToNextAxleMinimum = null,
        public ?int $distanceToNextAxleMaximum = null,
        public ?int $technicalMaximumAxleLoadUpper = null,
        public ?int $technicalMaximumAxleLoadLower = null,
    ) {
    }
}
