<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Generator;

use NiekNijland\RDW\Exceptions\MissingFieldOverrideException;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;
use NiekNijland\RDW\Schema\SchemaRegistry;

/**
 * Produces PHP source for the field enums and record classes based on the
 * checked-in override classes, after validating that every snapshot field
 * has been claimed by an override.
 *
 * Generation fails loudly when RDW adds, removes or renames a field that
 * the overrides have not yet accounted for. Enum case names and record
 * property names are treated as part of the package API: once an override
 * is committed, regeneration produces the same symbols.
 */
final class EnumGenerator
{
    public function __construct(
        private readonly SchemaSnapshot $snapshot,
    ) {
    }

    /**
     * @return list<array{file: string, code: string}>
     */
    public function generate(): array
    {
        $results = [];

        foreach (SchemaRegistry::all() as $schema) {
            $this->assertSchemaMatchesSnapshot($schema);

            $results[] = [
                'file' => 'src/Fields/' . self::shortName($schema->fieldEnumClass) . '.php',
                'code' => $this->renderEnum($schema),
            ];

            $results[] = [
                'file' => 'src/Records/' . self::shortName($schema->recordClass) . '.php',
                'code' => $this->renderRecord($schema),
            ];
        }

        return $results;
    }

    private function assertSchemaMatchesSnapshot(DatasetSchema $schema): void
    {
        $metadataKeys = $this->snapshot->fieldKeysFor($schema->datasetId);

        $overrideKeys = array_map(
            static fn (FieldDescriptor $field): string => $field->rdwKey,
            $schema->fields,
        );

        $missingFromOverrides = array_values(array_diff($metadataKeys, $overrideKeys));
        $missingFromMetadata = array_values(array_diff($overrideKeys, $metadataKeys));

        if ($missingFromOverrides === [] && $missingFromMetadata === []) {
            return;
        }

        throw MissingFieldOverrideException::forDataset(
            $schema->datasetId->value,
            $missingFromOverrides,
            $missingFromMetadata,
        );
    }

    private function renderEnum(DatasetSchema $schema): string
    {
        $shortName = self::shortName($schema->fieldEnumClass);
        $namespace = self::namespaceOf($schema->fieldEnumClass);

        $cases = '';

        foreach ($schema->fields as $field) {
            if ($field->cast === CastType::Excluded) {
                continue;
            }

            $cases .= sprintf("    case %s = '%s';\n", $field->enumCase, $field->rdwKey);
        }

        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

/**
 * Public-facing field names for RDW dataset "{$schema->datasetId->value}".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum {$shortName}: string
{
{$cases}}

PHP;
    }

    private function renderRecord(DatasetSchema $schema): string
    {
        $shortName = self::shortName($schema->recordClass);
        $namespace = self::namespaceOf($schema->recordClass);

        $usesCarbon = false;
        $properties = [];

        foreach ($schema->exposedFields() as $field) {
            $type = self::phpType($field->cast);

            if (str_contains($type, 'CarbonImmutable')) {
                $usesCarbon = true;
            }

            $properties[] = sprintf('        public %s $%s = null,', $type, $field->propertyName);
        }

        $propertyList = implode("\n", $properties);
        $carbonImport = $usesCarbon ? "\nuse Carbon\\CarbonImmutable;\n" : '';

        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};
{$carbonImport}
/**
 * Typed record for RDW dataset "{$schema->datasetId->value}".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
final readonly class {$shortName}
{
    public function __construct(
{$propertyList}
    ) {
    }
}

PHP;
    }

    private static function phpType(CastType $cast): string
    {
        return match ($cast) {
            CastType::String, CastType::Decimal => '?string',
            CastType::Integer => '?int',
            CastType::Boolean => '?bool',
            CastType::CalendarDate, CastType::NumericDate => '?CarbonImmutable',
            CastType::Excluded => '?string',
        };
    }

    private static function shortName(string $fqn): string
    {
        $pos = strrpos($fqn, '\\');

        return $pos === false ? $fqn : substr($fqn, $pos + 1);
    }

    private static function namespaceOf(string $fqn): string
    {
        $pos = strrpos($fqn, '\\');

        return $pos === false ? '' : substr($fqn, 0, $pos);
    }
}
