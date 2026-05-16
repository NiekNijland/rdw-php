<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Relations;

use NiekNijland\RDW\Datasets\DatasetId;

/**
 * Describes a single relation between two RDW datasets.
 *
 * sourceProperties / targetFields are parallel lists — index N on each
 * side describes one join column. Composite relations such as bodywork
 * → bodywork specifications carry multiple entries.
 */
final readonly class RelationDefinition
{
    /**
     * @param list<string> $sourceProperties english record property names on the source record
     * @param list<string> $targetFields RDW field keys on the target dataset
     */
    public function __construct(
        public string $name,
        public DatasetId $from,
        public DatasetId $to,
        public array $sourceProperties,
        public array $targetFields,
        public Cardinality $cardinality,
    ) {
    }
}
