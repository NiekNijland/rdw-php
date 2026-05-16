<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "jhie-znh9".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class RegisteredVehicleBodyworkSpecification
{
    public function __construct(
        public ?string $licensePlate = null,
        public ?string $sequenceNumber = null,
        public ?string $bodyworkCodeSequenceNumber = null,
        public ?string $bodyworkCode = null,
        public ?string $europeanBodyworkCodeDescription = null,
    ) {
    }
}
