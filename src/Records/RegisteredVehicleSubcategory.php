<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "2ba7-embk".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleSubcategory
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?int $sequenceNumber = null,
        public ?string $europeanSubcategory = null,
        public ?string $europeanSubcategoryDescription = null,
    ) {
    }
}
