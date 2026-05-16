<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Records;

use NiekNijland\RDW\Schema\DatasetSchema;

/**
 * Turns raw Socrata payload arrays into typed record instances using the
 * dataset's curated schema.
 *
 * Each schema lists every exposed field with its English property name
 * and cast type. ValueCaster handles the per-field transformation;
 * Hydrator orchestrates the lookup and instantiates the record via named
 * arguments so the resulting object is plain-old constructed PHP.
 */
final class Hydrator
{
    /**
     * @param array<string, mixed> $row
     */
    public static function hydrate(DatasetSchema $schema, array $row): object
    {
        $args = [];

        foreach ($schema->exposedFields() as $field) {
            $args[$field->propertyName] = ValueCaster::cast(
                $field->cast,
                $row[$field->rdwKey] ?? null,
            );
        }

        /** @var class-string $recordClass */
        $recordClass = $schema->recordClass;

        return new $recordClass(...$args);
    }
}
