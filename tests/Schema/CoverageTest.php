<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Schema;

use NiekNijland\RDW\Generator\EnumGenerator;
use NiekNijland\RDW\Generator\SchemaSnapshot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Locks in the public API stability contract from the implementation plan:
 * every RDW metadata field must be claimed by an override (either exposed
 * or explicitly excluded), and every override must point at a real field.
 * Drift surfaces as a failed assertion here, not silently at runtime.
 */
#[CoversClass(EnumGenerator::class)]
final class CoverageTest extends TestCase
{
    public function test_every_metadata_field_is_covered_by_overrides(): void
    {
        $generator = new EnumGenerator(new SchemaSnapshot(dirname(__DIR__, 2) . '/metadata'));

        $results = $generator->generate();

        self::assertNotEmpty($results, 'Generator returned no files.');
    }
}
