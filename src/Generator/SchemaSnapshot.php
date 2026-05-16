<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Generator;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Exceptions\RdwException;

/**
 * Reads a checked-in RDW metadata snapshot from disk and exposes the
 * field list. Used by the generator to validate that overrides remain in
 * sync with upstream.
 */
final class SchemaSnapshot
{
    public function __construct(
        private readonly string $directory,
    ) {
    }

    /**
     * @return list<string> rdwKey for each non-internal column in the snapshot
     */
    public function fieldKeysFor(DatasetId $dataset): array
    {
        $path = $this->directory . DIRECTORY_SEPARATOR . $dataset->value . '.json';

        if (! is_file($path)) {
            throw new RdwException(sprintf('Metadata snapshot not found at "%s".', $path));
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RdwException(sprintf('Unable to read metadata snapshot at "%s".', $path));
        }

        $payload = json_decode($contents, associative: true);

        if (! is_array($payload) || ! isset($payload['columns']) || ! is_array($payload['columns'])) {
            throw new RdwException(sprintf('Invalid metadata snapshot at "%s".', $path));
        }

        $keys = [];

        foreach ($payload['columns'] as $column) {
            if (! is_array($column) || ! isset($column['fieldName']) || ! is_string($column['fieldName'])) {
                continue;
            }

            // Skip Socrata internal columns (start with ":").
            if (str_starts_with($column['fieldName'], ':')) {
                continue;
            }

            $keys[] = $column['fieldName'];
        }

        return $keys;
    }
}
