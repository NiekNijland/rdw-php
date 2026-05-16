<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema;

/**
 * Maps a single RDW field to its public-facing representation.
 *
 * - rdwKey:      the Dutch snake_case field name (e.g. handelsbenaming)
 * - enumCase:    the PascalCase public field enum case (e.g. CommercialName)
 * - propertyName: the camelCase record property name (e.g. commercialName)
 * - cast:        how the raw payload value is transformed during hydration
 */
final readonly class FieldDescriptor
{
    public function __construct(
        public string $rdwKey,
        public string $enumCase,
        public string $propertyName,
        public CastType $cast,
    ) {
    }

    public function isExposed(): bool
    {
        return $this->cast !== CastType::Excluded;
    }
}
