<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Generator;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Exceptions\RdwException;
use NiekNijland\RDW\Generator\SchemaSnapshot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaSnapshot::class)]
final class SchemaSnapshotTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/rdw-snapshot-' . uniqid('', true);
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        $files = glob($this->directory . '/*');

        foreach (is_array($files) ? $files : [] as $file) {
            unlink($file);
        }

        rmdir($this->directory);
    }

    public function test_returns_non_internal_field_keys(): void
    {
        $this->write(DatasetId::OdometerJudgementExplanations, [
            'columns' => [
                ['fieldName' => ':id', 'name' => 'internal', 'dataTypeName' => 'meta_data'],
                ['fieldName' => 'code_toelichting_tellerstandoordeel', 'name' => 'Code', 'dataTypeName' => 'text'],
                ['fieldName' => 'toelichting_tellerstandoordeel', 'name' => 'Desc', 'dataTypeName' => 'text'],
            ],
        ]);

        $snapshot = new SchemaSnapshot($this->directory);

        self::assertSame(
            ['code_toelichting_tellerstandoordeel', 'toelichting_tellerstandoordeel'],
            $snapshot->fieldKeysFor(DatasetId::OdometerJudgementExplanations),
        );
    }

    public function test_throws_when_snapshot_file_is_missing(): void
    {
        $snapshot = new SchemaSnapshot($this->directory);

        $this->expectException(RdwException::class);
        $this->expectExceptionMessage('not found');

        $snapshot->fieldKeysFor(DatasetId::OdometerJudgementExplanations);
    }

    public function test_throws_on_invalid_json(): void
    {
        file_put_contents(
            $this->directory . '/' . DatasetId::OdometerJudgementExplanations->value . '.json',
            '{not-json',
        );

        $snapshot = new SchemaSnapshot($this->directory);

        $this->expectException(RdwException::class);
        $this->expectExceptionMessage('Invalid metadata snapshot');

        $snapshot->fieldKeysFor(DatasetId::OdometerJudgementExplanations);
    }

    public function test_throws_when_columns_key_is_missing(): void
    {
        $this->write(DatasetId::OdometerJudgementExplanations, ['name' => 'nope']);

        $snapshot = new SchemaSnapshot($this->directory);

        $this->expectException(RdwException::class);
        $this->expectExceptionMessage('Invalid metadata snapshot');

        $snapshot->fieldKeysFor(DatasetId::OdometerJudgementExplanations);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function write(DatasetId $dataset, array $payload): void
    {
        file_put_contents(
            $this->directory . '/' . $dataset->value . '.json',
            json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }
}
