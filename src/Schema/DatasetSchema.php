<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Schema;

use LogicException;
use NiekNijland\RDW\Datasets\DatasetId;

/**
 * The full curated field map for a dataset.
 *
 * A schema lists every metadata field once. Fields with cast === Excluded
 * are intentionally hidden from the public API but still tracked here so
 * generation can fail loudly when RDW introduces an unrecognized field.
 */
final readonly class DatasetSchema
{
    /** @var array<string, FieldDescriptor> indexed by rdwKey */
    public array $byRdwKey;

    /** @var array<string, FieldDescriptor> indexed by enumCase */
    public array $byEnumCase;

    /**
     * @param list<FieldDescriptor> $fields
     */
    public function __construct(
        public DatasetId $datasetId,
        public string $recordClass,
        public string $fieldEnumClass,
        public array $fields,
    ) {
        $byRdwKey = [];
        $byEnumCase = [];

        foreach ($fields as $field) {
            if (isset($byRdwKey[$field->rdwKey])) {
                throw new LogicException(sprintf(
                    'Duplicate rdwKey "%s" in dataset "%s".',
                    $field->rdwKey,
                    $datasetId->value,
                ));
            }

            if (isset($byEnumCase[$field->enumCase])) {
                throw new LogicException(sprintf(
                    'Duplicate enumCase "%s" in dataset "%s".',
                    $field->enumCase,
                    $datasetId->value,
                ));
            }

            $byRdwKey[$field->rdwKey] = $field;
            $byEnumCase[$field->enumCase] = $field;
        }

        $this->byRdwKey = $byRdwKey;
        $this->byEnumCase = $byEnumCase;
    }

    /**
     * @return list<FieldDescriptor>
     */
    public function exposedFields(): array
    {
        return array_values(array_filter($this->fields, static fn (FieldDescriptor $f) => $f->isExposed()));
    }

    /**
     * Exposed fields that carry an explicit {@see ValueVocabulary}.
     *
     * @return list<FieldDescriptor>
     */
    public function fieldsWithVocabulary(): array
    {
        return array_values(array_filter(
            $this->exposedFields(),
            static fn (FieldDescriptor $f) => $f->vocabulary !== null,
        ));
    }

    public function findByRdwKey(string $rdwKey): ?FieldDescriptor
    {
        return $this->byRdwKey[$rdwKey] ?? null;
    }
}
