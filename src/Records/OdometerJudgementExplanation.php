<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

/**
 * Typed record for RDW dataset "jqs4-4kvw".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class OdometerJudgementExplanation
{
    public function __construct(
        public ?string $odometerJudgementCode = null,
        public ?string $odometerJudgementDescription = null,
    ) {
    }
}
