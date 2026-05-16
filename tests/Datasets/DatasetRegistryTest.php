<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Datasets;

use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Datasets\DatasetRegistry;
use NiekNijland\RDW\Exceptions\DatasetNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DatasetRegistry::class)]
final class DatasetRegistryTest extends TestCase
{
    public function test_registers_all_in_scope_datasets(): void
    {
        $registry = new DatasetRegistry();

        self::assertCount(10, $registry->all());
    }

    public function test_can_look_up_a_dataset_by_id(): void
    {
        $registry = new DatasetRegistry();

        $definition = $registry->get(DatasetId::RegisteredVehicles);

        self::assertSame(DatasetId::RegisteredVehicles, $definition->id);
        self::assertSame('m9d7-ebf2', $definition->id->value);
        self::assertSame('registeredVehicles', $definition->publicName);
    }

    public function test_can_look_up_a_dataset_by_public_name(): void
    {
        $registry = new DatasetRegistry();

        $definition = $registry->getByName('odometerJudgementExplanations');

        self::assertSame(DatasetId::OdometerJudgementExplanations, $definition->id);
    }

    public function test_throws_when_public_name_is_unknown(): void
    {
        $registry = new DatasetRegistry();

        $this->expectException(DatasetNotFoundException::class);

        $registry->getByName('nonExistent');
    }
}
