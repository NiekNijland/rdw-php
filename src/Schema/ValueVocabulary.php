<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema;

use InvalidArgumentException;

/**
 * Optional vocabulary attached to a {@see FieldDescriptor}.
 *
 * Lets consumers (in particular, AI-driven query builders) discover the set of
 * values a field accepts without round-tripping to the live API. The library
 * never enforces the vocabulary during hydration: RDW occasionally introduces
 * new values, and a strict cast would turn that into a crash. The flag
 * `$exhaustive` is a hint, not a contract.
 *
 * - exhaustive=true:  the full set of accepted values is known and small. The
 *   matching Dutch code list is intentionally short (e.g. vehicle types,
 *   colors). Treat as a closed enum for prompt-shaping, but be ready for RDW
 *   to add a value.
 * - exhaustive=false: the field has many possible values (e.g. brand, model).
 *   The list is a curated set of common examples meant to anchor LLM output;
 *   the LLM should still feel free to emit other values.
 */
final readonly class ValueVocabulary
{
    /**
     * @param list<string> $values Must be non-empty; the constructor enforces it at runtime.
     */
    public function __construct(
        public array $values,
        public bool $exhaustive,
    ) {
        if ($values === []) {
            throw new InvalidArgumentException('ValueVocabulary requires at least one value.');
        }
    }

    /**
     * Mark the value set as the complete, known list for this field.
     */
    public static function closed(string $first, string ...$rest): self
    {
        return new self([$first, ...array_values($rest)], true);
    }

    /**
     * Mark the value list as a representative sample of an open value space.
     */
    public static function examples(string $first, string ...$rest): self
    {
        return new self([$first, ...array_values($rest)], false);
    }
}
