<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Generator;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Exceptions\MissingFieldOverrideException;
use NiekNijland\RDW\Generator\EnumGenerator;
use NiekNijland\RDW\Generator\SchemaSnapshot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumGenerator::class)]
final class EnumGeneratorTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/rdw-generator-' . uniqid('', true);
        mkdir($this->directory);

        // Copy real metadata snapshots so every dataset's override can match.
        $real = dirname(__DIR__, 2) . '/metadata';
        $files = glob($real . '/*.json');

        foreach (is_array($files) ? $files : [] as $file) {
            copy($file, $this->directory . '/' . basename($file));
        }
    }

    protected function tearDown(): void
    {
        $files = glob($this->directory . '/*');

        foreach (is_array($files) ? $files : [] as $file) {
            unlink($file);
        }

        rmdir($this->directory);
    }

    public function test_renders_an_enum_file_for_each_dataset(): void
    {
        $results = (new EnumGenerator(new SchemaSnapshot($this->directory)))->generate();

        $enumFile = $this->find($results, 'src/Fields/OdometerJudgementExplanationField.php');

        self::assertStringContainsString('namespace NiekNijland\\RDW\\Fields;', $enumFile);
        self::assertStringContainsString(
            "enum OdometerJudgementExplanationField: string\n{\n    case OdometerJudgementCode = 'code_toelichting_tellerstandoordeel';",
            $enumFile,
        );
        self::assertStringContainsString('GENERATED FILE', $enumFile);
    }

    public function test_renders_a_record_file_with_carbon_import_only_when_needed(): void
    {
        $results = (new EnumGenerator(new SchemaSnapshot($this->directory)))->generate();

        $odometerRecord = $this->find($results, 'src/Records/OdometerJudgementExplanation.php');
        // OdometerJudgementExplanation has no date fields -> no Carbon import.
        self::assertStringNotContainsString('use Carbon\\CarbonImmutable;', $odometerRecord);
        self::assertStringContainsString('public ?string $odometerJudgementCode = null,', $odometerRecord);

        $vehicleRecord = $this->find($results, 'src/Records/RegisteredVehicle.php');
        self::assertStringContainsString('use Carbon\\CarbonImmutable;', $vehicleRecord);
        self::assertStringContainsString('public ?CarbonImmutable $apkExpiryDate = null,', $vehicleRecord);
    }

    public function test_excluded_fields_do_not_appear_in_the_generated_enum_or_record(): void
    {
        $results = (new EnumGenerator(new SchemaSnapshot($this->directory)))->generate();

        $enum = $this->find($results, 'src/Fields/RegisteredVehicleField.php');
        $record = $this->find($results, 'src/Records/RegisteredVehicle.php');

        // datum_tenaamstelling is an excluded legacy column; api_*_url are excluded.
        self::assertStringNotContainsString("'datum_tenaamstelling'", $enum);
        self::assertStringNotContainsString('apkExpiryDateLegacy', $record);
        self::assertStringNotContainsString('axlesApiUrl', $record);
    }

    public function test_throws_when_metadata_has_a_field_not_present_in_overrides(): void
    {
        $this->tamper(DatasetId::OdometerJudgementExplanations, [
            ['fieldName' => 'code_toelichting_tellerstandoordeel'],
            ['fieldName' => 'toelichting_tellerstandoordeel'],
            ['fieldName' => 'brand_new_rdw_field'],
        ]);

        $generator = new EnumGenerator(new SchemaSnapshot($this->directory));

        try {
            $generator->generate();
            self::fail('Expected MissingFieldOverrideException.');
        } catch (MissingFieldOverrideException $exception) {
            self::assertStringContainsString('jqs4-4kvw', $exception->getMessage());
            self::assertStringContainsString('brand_new_rdw_field', $exception->getMessage());
            self::assertStringContainsString('not in overrides', $exception->getMessage());
        }
    }

    public function test_throws_when_overrides_reference_a_field_no_longer_in_metadata(): void
    {
        $this->tamper(DatasetId::OdometerJudgementExplanations, [
            ['fieldName' => 'code_toelichting_tellerstandoordeel'],
            // toelichting_tellerstandoordeel removed upstream
        ]);

        $generator = new EnumGenerator(new SchemaSnapshot($this->directory));

        try {
            $generator->generate();
            self::fail('Expected MissingFieldOverrideException.');
        } catch (MissingFieldOverrideException $exception) {
            self::assertStringContainsString('toelichting_tellerstandoordeel', $exception->getMessage());
            self::assertStringContainsString('not in RDW metadata', $exception->getMessage());
        }
    }

    /**
     * @param list<array{file: string, code: string}> $results
     */
    private function find(array $results, string $file): string
    {
        foreach ($results as $result) {
            if ($result['file'] === $file) {
                return $result['code'];
            }
        }

        self::fail("Generator did not emit {$file}");
    }

    /**
     * @param list<array{fieldName: string}> $columns
     */
    private function tamper(DatasetId $dataset, array $columns): void
    {
        file_put_contents(
            $this->directory . '/' . $dataset->value . '.json',
            json_encode(['columns' => $columns], JSON_THROW_ON_ERROR),
        );
    }
}
