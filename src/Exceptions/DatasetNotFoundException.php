<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Exceptions;

class DatasetNotFoundException extends RdwException
{
    public static function forId(string $datasetId): self
    {
        return new self(sprintf('No dataset registered with id "%s".', $datasetId));
    }

    public static function forName(string $publicName): self
    {
        return new self(sprintf('No dataset registered with public name "%s".', $publicName));
    }
}
