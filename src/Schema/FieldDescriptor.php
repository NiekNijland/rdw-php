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
 * - vocabulary:  optional list of accepted/example values (closed enum vs.
 *                curated samples). Carries no runtime enforcement — meant
 *                primarily for schema-introspection use cases like LLM-driven
 *                query builders.
 */
final readonly class FieldDescriptor
{
    public function __construct(
        public string $rdwKey,
        public string $enumCase,
        public string $propertyName,
        public CastType $cast,
        public ?ValueVocabulary $vocabulary = null,
    ) {
    }

    public function isExposed(): bool
    {
        return $this->cast !== CastType::Excluded;
    }
}
