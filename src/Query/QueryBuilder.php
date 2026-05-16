<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Query;

use BackedEnum;
use DateTimeInterface;
use Generator;
use InvalidArgumentException;
use LogicException;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Records\Hydrator;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;

/**
 * Fluent SoQL builder bound to a single dataset.
 *
 * Typed methods accept the dataset's generated field enum cases and
 * translate them to RDW field keys at request time. Raw escape hatches
 * (whereRaw, selectRaw, orderByRaw) take SoQL expressions verbatim and
 * must use RDW field keys, not English aliases.
 *
 * Methods are chainable and return clones so partially-built queries can
 * be reused safely.
 *
 * @template TRecord of object
 */
class QueryBuilder
{
    private const array ALLOWED_OPERATORS = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'];

    /** @var list<string> */
    private array $wheres = [];

    /** @var list<string> */
    private array $selects = [];

    /** @var list<string> */
    private array $groups = [];

    /** @var list<string> */
    private array $orders = [];

    private ?int $limit = null;

    private ?int $offset = null;

    /**
     * @param class-string<TRecord> $recordClass carries the generic binding the schema cannot
     */
    public function __construct(
        private readonly DatasetSchema $schema,
        private readonly SocrataClient $http,
        string $recordClass,
    ) {
        if ($recordClass !== $schema->recordClass) {
            throw new LogicException(sprintf(
                'QueryBuilder record class "%s" does not match dataset schema record class "%s".',
                $recordClass,
                $schema->recordClass,
            ));
        }
    }

    public function where(BackedEnum $field, mixed $value, string $operator = '='): static
    {
        $this->assertFieldBelongsToSchema($field);
        self::assertOperator($operator);

        $clone = clone $this;
        $clone->wheres[] = sprintf(
            '%s %s %s',
            self::encodeField($field),
            $operator,
            $clone->encodeValue($field, $value),
        );

        return $clone;
    }

    /**
     * @param list<scalar|DateTimeInterface|null> $values null is rejected at runtime; use whereRaw for IS NULL
     */
    public function whereIn(BackedEnum $field, array $values): static
    {
        $this->assertFieldBelongsToSchema($field);

        if ($values === []) {
            throw new InvalidArgumentException('whereIn requires a non-empty value list.');
        }

        $clone = clone $this;
        $encoded = [];

        foreach ($values as $value) {
            if ($value === null) {
                throw new InvalidArgumentException(
                    'whereIn does not accept null values; use a separate "IS NULL" filter via whereRaw.',
                );
            }

            $encoded[] = $clone->encodeValue($field, $value);
        }

        $clone->wheres[] = sprintf(
            '%s IN (%s)',
            self::encodeField($field),
            implode(', ', $encoded),
        );

        return $clone;
    }

    public function whereRaw(string $expression): static
    {
        $clone = clone $this;
        $clone->wheres[] = $expression;

        return $clone;
    }

    public function select(BackedEnum ...$fields): static
    {
        foreach ($fields as $field) {
            $this->assertFieldBelongsToSchema($field);
        }

        $clone = clone $this;
        foreach ($fields as $field) {
            $clone->selects[] = self::encodeField($field);
        }

        return $clone;
    }

    public function selectRaw(string $expression, ?string $alias = null): static
    {
        $clone = clone $this;
        $clone->selects[] = $alias !== null ? "{$expression} AS {$alias}" : $expression;

        return $clone;
    }

    public function groupBy(BackedEnum ...$fields): static
    {
        foreach ($fields as $field) {
            $this->assertFieldBelongsToSchema($field);
        }

        $clone = clone $this;
        foreach ($fields as $field) {
            $clone->groups[] = self::encodeField($field);
        }

        return $clone;
    }

    public function orderBy(BackedEnum $field, SortDirection $direction = SortDirection::Asc): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->orders[] = self::encodeField($field) . ' ' . $direction->value;

        return $clone;
    }

    public function orderByRaw(string $expression): static
    {
        $clone = clone $this;
        $clone->orders[] = $expression;

        return $clone;
    }

    public function limit(int $limit): static
    {
        if ($limit < 1) {
            throw new InvalidArgumentException('limit must be >= 1.');
        }

        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }

    public function offset(int $offset): static
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('offset must be >= 0.');
        }

        $clone = clone $this;
        $clone->offset = $offset;

        return $clone;
    }

    /**
     * Adds a count(*) or count(<field>) aggregate expression to the projection
     * with the given alias. Useful in combination with groupBy() + getProjection().
     */
    public function count(?BackedEnum $field = null, string $alias = 'count'): static
    {
        if ($field !== null) {
            $this->assertFieldBelongsToSchema($field);
        }

        $expression = $field !== null
            ? sprintf('count(%s)', self::encodeField($field))
            : 'count(*)';

        return $this->selectRaw($expression, $alias);
    }

    /**
     * @return array<string, string>
     */
    public function toSoqlParams(): array
    {
        $params = [];

        if ($this->selects !== []) {
            $params['$select'] = implode(', ', $this->selects);
        }

        if ($this->wheres !== []) {
            $params['$where'] = count($this->wheres) === 1
                ? $this->wheres[0]
                : '(' . implode(') AND (', $this->wheres) . ')';
        }

        if ($this->groups !== []) {
            $params['$group'] = implode(', ', $this->groups);
        }

        if ($this->orders !== []) {
            $params['$order'] = implode(', ', $this->orders);
        }

        if ($this->limit !== null) {
            $params['$limit'] = (string) $this->limit;
        }

        if ($this->offset !== null) {
            $params['$offset'] = (string) $this->offset;
        }

        return $params;
    }

    /**
     * @return list<TRecord>
     */
    public function get(): array
    {
        $rows = $this->fetchRows($this->toSoqlParams());

        /** @var list<TRecord> */
        return array_map(
            fn (array $row): object => Hydrator::hydrate($this->schema, $row),
            $rows,
        );
    }

    /**
     * @return TRecord|null
     */
    public function first(): ?object
    {
        $rows = $this->limit(1)->get();

        return $rows[0] ?? null;
    }

    /**
     * Returns raw associative rows without hydration. Use for aggregates,
     * grouped queries, or arbitrary selectRaw projections where the
     * record schema does not apply.
     *
     * @return list<array<string, mixed>>
     */
    public function getProjection(): array
    {
        return $this->fetchRows($this->toSoqlParams());
    }

    /**
     * Pages through the full result set lazily, yielding hydrated records.
     *
     * When the builder has its own ->limit() or ->offset() set, iteration
     * respects them as outer bounds while still pulling pageSize rows
     * per request from RDW.
     *
     * @return Generator<int, TRecord>
     */
    public function iterate(int $pageSize = 1000): Generator
    {
        if ($pageSize <= 0) {
            throw new InvalidArgumentException('pageSize must be > 0.');
        }

        $startOffset = $this->offset ?? 0;
        $hardLimit = $this->limit;
        $emitted = 0;

        $baseParams = $this->toSoqlParams();
        // $limit/$offset on the builder describe the outer window; pagination
        // overrides both per request, so strip them from the base params.
        unset($baseParams['$limit'], $baseParams['$offset']);

        while (true) {
            $remaining = $hardLimit !== null ? $hardLimit - $emitted : null;
            if ($remaining !== null && $remaining <= 0) {
                return;
            }

            $requestSize = $remaining !== null ? min($pageSize, $remaining) : $pageSize;

            $params = $baseParams;
            $params['$limit'] = (string) $requestSize;
            $params['$offset'] = (string) ($startOffset + $emitted);

            $rows = $this->fetchRows($params);

            if ($rows === []) {
                return;
            }

            foreach ($rows as $row) {
                /** @var TRecord $record */
                $record = Hydrator::hydrate($this->schema, $row);
                yield $record;
                $emitted++;
            }

            if (count($rows) < $requestSize) {
                return;
            }
        }
    }

    /**
     * @param array<string, string> $params
     * @return list<array<string, mixed>>
     */
    private function fetchRows(array $params): array
    {
        return $this->http->getRows($this->schema->datasetId->value, $params);
    }

    private function assertFieldBelongsToSchema(BackedEnum $field): void
    {
        if (! is_a($field, $this->schema->fieldEnumClass)) {
            throw new InvalidArgumentException(sprintf(
                'Field enum "%s" does not belong to dataset "%s" (expected "%s").',
                $field::class,
                $this->schema->datasetId->value,
                $this->schema->fieldEnumClass,
            ));
        }
    }

    private static function assertOperator(string $operator): void
    {
        if (! in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Operator "%s" is not allowed; use one of: %s.',
                $operator,
                implode(', ', self::ALLOWED_OPERATORS),
            ));
        }
    }

    private static function encodeField(BackedEnum $field): string
    {
        return (string) $field->value;
    }

    private function encodeValue(BackedEnum $field, mixed $value): string
    {
        $descriptor = $this->schema->byEnumCase[$field->name] ?? null;

        if (is_bool($value)) {
            if ($descriptor !== null && $descriptor->cast === CastType::Boolean) {
                return self::quoteString($value ? 'Ja' : 'Nee');
            }

            return $value ? 'true' : 'false';
        }

        if ($value instanceof DateTimeInterface) {
            return self::quoteString($value->format('Y-m-d\TH:i:s.000'));
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value === null) {
            return 'null';
        }

        return self::quoteString((string) $value);
    }

    private static function quoteString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }
}
