<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Query;

use BackedEnum;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use InvalidArgumentException;
use LogicException;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Records\Hydrator;
use NiekNijland\RDW\Records\ValueCaster;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;

/**
 * Fluent SoQL builder bound to a single dataset.
 *
 * Typed methods accept the dataset's generated field enum cases and
 * translate them to RDW field keys at request time. Raw escape hatches
 * (whereRaw, selectRaw, groupByRaw, orderByRaw) take SoQL expressions verbatim and
 * must use RDW field keys, not English aliases.
 *
 * Methods are chainable and return clones so partially-built queries can
 * be reused safely.
 *
 * @template-covariant TRecord of object
 */
class QueryBuilder
{
    private const array ALLOWED_OPERATORS = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'];

    private const string ALIAS_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/';

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

    private ?string $having = null;

    private bool $distinct = false;

    private ?string $fullTextSearch = null;

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

        if ($value === null) {
            throw new InvalidArgumentException(
                'where() does not accept null; use whereRaw(\'field IS NULL\') for null comparisons.',
            );
        }

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

    /**
     * @param list<scalar|DateTimeInterface|null> $values null is rejected at runtime; use whereNotNull instead
     */
    public function whereNotIn(BackedEnum $field, array $values): static
    {
        $this->assertFieldBelongsToSchema($field);

        if ($values === []) {
            throw new InvalidArgumentException('whereNotIn requires a non-empty value list.');
        }

        $clone = clone $this;
        $encoded = [];

        foreach ($values as $value) {
            if ($value === null) {
                throw new InvalidArgumentException(
                    'whereNotIn does not accept null values; use whereNotNull for IS NOT NULL.',
                );
            }

            $encoded[] = $clone->encodeValue($field, $value);
        }

        $clone->wheres[] = sprintf(
            '%s NOT IN (%s)',
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

    /**
     * SQL-style LIKE with explicit `%` wildcards. Case-sensitive.
     * Single quotes in the pattern are escaped; pass the wildcards as-is.
     */
    public function whereLike(BackedEnum $field, string $pattern): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->wheres[] = sprintf(
            '%s LIKE %s',
            self::encodeField($field),
            self::quoteString($pattern),
        );

        return $clone;
    }

    /**
     * SoQL starts_with() prefix predicate. Case-sensitive.
     */
    public function whereStartsWith(BackedEnum $field, string $prefix): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->wheres[] = sprintf(
            'starts_with(%s, %s)',
            self::encodeField($field),
            self::quoteString($prefix),
        );

        return $clone;
    }

    /**
     * SoQL contains() substring predicate. Case-insensitive per Socrata semantics.
     */
    public function whereContains(BackedEnum $field, string $substring): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->wheres[] = sprintf(
            'contains(%s, %s)',
            self::encodeField($field),
            self::quoteString($substring),
        );

        return $clone;
    }

    public function whereNull(BackedEnum $field): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->wheres[] = self::encodeField($field) . ' IS NULL';

        return $clone;
    }

    public function whereNotNull(BackedEnum $field): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->wheres[] = self::encodeField($field) . ' IS NOT NULL';

        return $clone;
    }

    /**
     * @param scalar|DateTimeInterface $min
     * @param scalar|DateTimeInterface $max
     */
    public function whereBetween(BackedEnum $field, mixed $min, mixed $max): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->wheres[] = sprintf(
            '%s BETWEEN %s AND %s',
            self::encodeField($field),
            $clone->encodeValue($field, $min),
            $clone->encodeValue($field, $max),
        );

        return $clone;
    }

    /**
     * @param scalar|DateTimeInterface $min
     * @param scalar|DateTimeInterface $max
     */
    public function whereNotBetween(BackedEnum $field, mixed $min, mixed $max): static
    {
        $this->assertFieldBelongsToSchema($field);

        $clone = clone $this;
        $clone->wheres[] = sprintf(
            '%s NOT BETWEEN %s AND %s',
            self::encodeField($field),
            $clone->encodeValue($field, $min),
            $clone->encodeValue($field, $max),
        );

        return $clone;
    }

    /**
     * OR-group of where clauses. The callback receives a fresh builder bound
     * to the same dataset; chained where() calls inside the callback are
     * combined with OR instead of the default AND. Because the builder is
     * immutable, the callback MUST return the chained builder — a void-return
     * closure discards every where() call.
     *
     * @param callable(self<TRecord>): mixed $callback
     */
    public function whereAny(callable $callback): static
    {
        /** @var class-string<TRecord> $recordClass */
        $recordClass = $this->schema->recordClass;
        $sub = new self($this->schema, $this->http, $recordClass);

        $result = $callback($sub);

        if (! $result instanceof self) {
            throw new InvalidArgumentException(
                'whereAny callback must return the chained QueryBuilder — the builder is '
                . 'immutable, so a closure that does not return discards every where() call.',
            );
        }

        if ($result->wheres === []) {
            throw new InvalidArgumentException('whereAny callback must add at least one where clause.');
        }

        $clone = clone $this;
        $clone->wheres[] = count($result->wheres) === 1
            ? $result->wheres[0]
            : '(' . implode(') OR (', $result->wheres) . ')';

        return $clone;
    }

    /**
     * NOT-wraps a sub-group of where clauses. The callback's clauses are
     * AND-joined inside the NOT; combine with whereAny() inside the callback
     * for NOT (a OR b).
     *
     * @param callable(self<TRecord>): mixed $callback
     */
    public function whereNot(callable $callback): static
    {
        /** @var class-string<TRecord> $recordClass */
        $recordClass = $this->schema->recordClass;
        $sub = new self($this->schema, $this->http, $recordClass);

        $result = $callback($sub);

        if (! $result instanceof self) {
            throw new InvalidArgumentException(
                'whereNot callback must return the chained QueryBuilder — the builder is '
                . 'immutable, so a closure that does not return discards every where() call.',
            );
        }

        if ($result->wheres === []) {
            throw new InvalidArgumentException('whereNot callback must add at least one where clause.');
        }

        $inner = count($result->wheres) === 1
            ? $result->wheres[0]
            : '(' . implode(') AND (', $result->wheres) . ')';

        $clone = clone $this;
        $clone->wheres[] = 'NOT (' . $inner . ')';

        return $clone;
    }

    /**
     * Socrata full-text search across all string columns ($q parameter).
     * The query is tokenized by whitespace; rows must contain every token.
     */
    public function search(string $query): static
    {
        if (trim($query) === '') {
            throw new InvalidArgumentException('search() requires a non-empty query.');
        }

        $clone = clone $this;
        $clone->fullTextSearch = $query;

        return $clone;
    }

    /**
     * Repeated calls with the same field are idempotent — a duplicate
     * `$select` would produce SoQL that RDW rejects with HTTP 400, so we
     * dedupe at the builder level. Mixing with `selectRaw()` is unaffected;
     * raw expressions are never compared against typed selects.
     */
    public function select(BackedEnum ...$fields): static
    {
        foreach ($fields as $field) {
            $this->assertFieldBelongsToSchema($field);
        }

        $clone = clone $this;
        foreach ($fields as $field) {
            $encoded = self::encodeField($field);
            if (! in_array($encoded, $clone->selects, true)) {
                $clone->selects[] = $encoded;
            }
        }

        return $clone;
    }

    public function selectRaw(string $expression, ?string $alias = null): static
    {
        if ($alias !== null && preg_match(self::ALIAS_PATTERN, $alias) !== 1) {
            throw new InvalidArgumentException(sprintf(
                'Alias "%s" must match %s (letters, digits, underscore; not starting with a digit).',
                $alias,
                self::ALIAS_PATTERN,
            ));
        }

        $clone = clone $this;
        $clone->selects[] = $alias !== null ? "{$expression} AS {$alias}" : $expression;

        return $clone;
    }

    /**
     * Marks the projection as DISTINCT. Requires at least one select() or
     * selectRaw() call before the query is executed.
     */
    public function distinct(): static
    {
        $clone = clone $this;
        $clone->distinct = true;

        return $clone;
    }

    /**
     * Idempotent for the same field — RDW rejects a duplicate `$group`
     * column with HTTP 400, so we dedupe at the builder level.
     */
    public function groupBy(BackedEnum ...$fields): static
    {
        foreach ($fields as $field) {
            $this->assertFieldBelongsToSchema($field);
        }

        $clone = clone $this;
        foreach ($fields as $field) {
            $encoded = self::encodeField($field);
            if (! in_array($encoded, $clone->groups, true)) {
                $clone->groups[] = $encoded;
            }
        }

        return $clone;
    }

    /**
     * Escape hatch for grouping by an arbitrary SoQL expression — e.g.
     * `date_trunc_ym(datum_eerste_toelating_dt)`. The expression is appended
     * verbatim and must reference RDW field keys, not English aliases.
     */
    public function groupByRaw(string $expression): static
    {
        $clone = clone $this;
        $clone->groups[] = $expression;

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

    public function countDistinct(BackedEnum $field, string $alias = 'count'): static
    {
        $this->assertFieldBelongsToSchema($field);

        return $this->selectRaw(sprintf('count(distinct %s)', self::encodeField($field)), $alias);
    }

    public function sum(BackedEnum $field, string $alias = 'sum'): static
    {
        $this->assertFieldBelongsToSchema($field);

        return $this->selectRaw(sprintf('sum(%s)', self::encodeField($field)), $alias);
    }

    public function avg(BackedEnum $field, string $alias = 'avg'): static
    {
        $this->assertFieldBelongsToSchema($field);

        return $this->selectRaw(sprintf('avg(%s)', self::encodeField($field)), $alias);
    }

    public function min(BackedEnum $field, string $alias = 'min'): static
    {
        $this->assertFieldBelongsToSchema($field);

        return $this->selectRaw(sprintf('min(%s)', self::encodeField($field)), $alias);
    }

    public function max(BackedEnum $field, string $alias = 'max'): static
    {
        $this->assertFieldBelongsToSchema($field);

        return $this->selectRaw(sprintf('max(%s)', self::encodeField($field)), $alias);
    }

    /**
     * SoQL $having clause. The expression must reference aggregate columns
     * (e.g. "count(*) > 100"); use RDW field keys and the same aliases you
     * passed to sum/avg/etc.
     */
    public function havingRaw(string $expression): static
    {
        $clone = clone $this;
        $clone->having = $expression;

        return $clone;
    }

    /**
     * @return array<string, string>
     */
    public function toSoqlParams(): array
    {
        if ($this->distinct && $this->selects === []) {
            throw new LogicException(
                'distinct() requires at least one select() or selectRaw() call before the query is executed.',
            );
        }

        $params = [];

        if ($this->selects !== []) {
            $select = implode(', ', $this->selects);
            $params['$select'] = $this->distinct ? 'distinct ' . $select : $select;
        }

        if ($this->fullTextSearch !== null) {
            $params['$q'] = $this->fullTextSearch;
        }

        if ($this->wheres !== []) {
            $params['$where'] = count($this->wheres) === 1
                ? $this->wheres[0]
                : '(' . implode(') AND (', $this->wheres) . ')';
        }

        if ($this->groups !== []) {
            $params['$group'] = implode(', ', $this->groups);
        }

        if ($this->having !== null) {
            $params['$having'] = $this->having;
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
     * Returns true when at least one row matches the current query. Issues a
     * single-row request and skips hydration.
     */
    public function exists(): bool
    {
        return $this->limit(1)->getProjection() !== [];
    }

    /**
     * Returns a flat list of one column's values, cast through the same
     * ValueCaster the records use. Useful for "give me every license plate"
     * style queries without paying the full hydration cost.
     *
     * @return list<scalar|CarbonImmutable|null>
     */
    public function pluck(BackedEnum $field): array
    {
        $this->assertFieldBelongsToSchema($field);

        $descriptor = $this->schema->byEnumCase[$field->name] ?? null;
        if ($descriptor === null) {
            throw new LogicException(sprintf(
                'Field "%s" not found on schema "%s".',
                $field->name,
                $this->schema->datasetId->value,
            ));
        }

        $rdwKey = self::encodeField($field);
        $rows = $this->select($field)->getProjection();

        $values = [];
        foreach ($rows as $row) {
            $values[] = ValueCaster::cast($descriptor->cast, $row[$rdwKey] ?? null);
        }

        return $values;
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
            // RDW interprets datetime literals as UTC, so normalize before formatting
            // — otherwise CarbonImmutable::parse('...', 'Europe/Amsterdam') shifts an hour.
            $utc = (new DateTimeImmutable('@' . $value->getTimestamp()))
                ->setTimezone(new DateTimeZone('UTC'));

            return self::quoteString($utc->format('Y-m-d\TH:i:s.000'));
        }

        if (is_float($value) && ! is_finite($value)) {
            throw new InvalidArgumentException('where() does not accept NAN or INF.');
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return self::quoteString((string) $value);
    }

    private static function quoteString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }
}
