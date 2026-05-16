<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "vezc-m2t6".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleBodywork
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?string $sequenceNumber = null,
        public ?string $bodyworkType = null,
        public ?string $europeanBodyworkDescription = null,
    ) {
    }
}
