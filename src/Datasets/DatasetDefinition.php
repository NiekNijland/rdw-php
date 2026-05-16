<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Datasets;

final readonly class DatasetDefinition
{
    public function __construct(
        public DatasetId $id,
        public string $publicName,
    ) {
    }
}
