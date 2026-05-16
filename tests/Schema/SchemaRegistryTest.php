<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Schema;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\SchemaRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaRegistry::class)]
final class SchemaRegistryTest extends TestCase
{
    public function test_registers_all_ten_in_scope_datasets(): void
    {
        $registry = new SchemaRegistry();

        foreach (DatasetId::cases() as $id) {
            $schema = $registry->get($id);
            self::assertSame($id, $schema->datasetId);
            self::assertNotSame([], $schema->fields);
        }
    }

    public function test_exposed_fields_drop_excluded_descriptors(): void
    {
        $registry = new SchemaRegistry();
        $schema = $registry->get(DatasetId::RegisteredVehicles);

        $excluded = array_filter($schema->fields, fn ($f) => $f->cast === CastType::Excluded);
        $exposed = $schema->exposedFields();

        self::assertGreaterThan(0, count($excluded), 'RegisteredVehicle has known legacy/url fields to exclude.');
        self::assertCount(count($schema->fields) - count($excluded), $exposed);

        foreach ($exposed as $field) {
            self::assertNotSame(CastType::Excluded, $field->cast);
        }
    }

    public function test_canonical_english_names_match_the_plan(): void
    {
        $schema = (new SchemaRegistry())->get(DatasetId::RegisteredVehicles);

        $byKey = fn (string $key) => $schema->findByRdwKey($key);

        self::assertSame('CommercialName', $byKey('handelsbenaming')?->enumCase);
        self::assertSame('RegistrationDate', $byKey('datum_tenaamstelling_dt')?->enumCase);
        self::assertSame('FirstAdmissionDate', $byKey('datum_eerste_toelating_dt')?->enumCase);
        self::assertSame('CanBeTransferred', $byKey('tenaamstellen_mogelijk')?->enumCase);
        self::assertSame('ApkExpiryDate', $byKey('vervaldatum_apk_dt')?->enumCase);
        self::assertSame('HasOpenRecall', $byKey('openstaande_terugroepactie_indicator')?->enumCase);
        self::assertSame('OdometerJudgementCode', $byKey('code_toelichting_tellerstandoordeel')?->enumCase);
    }
}
