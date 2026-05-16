<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Schema;

use LogicException;
use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Fields\RegisteredVehicleField;
use NiekNijland\RDW\Records\RegisteredVehicle;
use NiekNijland\RDW\Schema\CastType;
use NiekNijland\RDW\Schema\DatasetSchema;
use NiekNijland\RDW\Schema\FieldDescriptor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DatasetSchema::class)]
final class DatasetSchemaTest extends TestCase
{
    public function test_throws_on_duplicate_rdw_key(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Duplicate rdwKey "kenteken"');

        new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicles,
            recordClass: RegisteredVehicle::class,
            fieldEnumClass: RegisteredVehicleField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('kenteken', 'AnotherCase', 'another', CastType::String),
            ],
        );
    }

    public function test_throws_on_duplicate_enum_case(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Duplicate enumCase "LicensePlate"');

        new DatasetSchema(
            datasetId: DatasetId::RegisteredVehicles,
            recordClass: RegisteredVehicle::class,
            fieldEnumClass: RegisteredVehicleField::class,
            fields: [
                new FieldDescriptor('kenteken', 'LicensePlate', 'licensePlate', CastType::String),
                new FieldDescriptor('merk', 'LicensePlate', 'brand', CastType::String),
            ],
        );
    }
}
