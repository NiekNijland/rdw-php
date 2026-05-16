<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Records;

use Carbon\CarbonImmutable;
use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Records\Hydrator;
use NiekNijland\RDW\Records\RegisteredVehicle;
use NiekNijland\RDW\Schema\SchemaRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Hydrator::class)]
final class HydratorTest extends TestCase
{
    public function test_hydrates_a_registered_vehicle_with_mixed_types(): void
    {
        $schema = (new SchemaRegistry())->get(DatasetId::RegisteredVehicles);

        /** @var RegisteredVehicle $vehicle */
        $vehicle = Hydrator::hydrate($schema, [
            'kenteken' => '6ZNS30',
            'merk' => 'VOLKSWAGEN',
            'handelsbenaming' => 'POLO',
            'aantal_zitplaatsen' => '5',
            'massa_ledig_voertuig' => '1050',
            'catalogusprijs' => '16890.50',
            'wam_verzekerd' => 'Ja',
            'tenaamstellen_mogelijk' => 'Ja',
            'openstaande_terugroepactie_indicator' => 'Nee',
            'datum_tenaamstelling_dt' => '2022-09-30T00:00:00.000',
            'vervaldatum_apk_dt' => '2026-11-03T00:00:00.000',
            // Excluded fields and missing fields stay null.
            'datum_tenaamstelling' => '20220930',
            'api_gekentekende_voertuigen_assen' => 'https://opendata.rdw.nl/.../assen',
        ]);

        self::assertInstanceOf(RegisteredVehicle::class, $vehicle);
        self::assertSame('6ZNS30', $vehicle->licensePlate);
        self::assertSame('VOLKSWAGEN', $vehicle->brand);
        self::assertSame('POLO', $vehicle->commercialName);
        self::assertSame(5, $vehicle->seatCount);
        self::assertSame(1050, $vehicle->emptyMass);
        self::assertSame('16890.50', $vehicle->catalogPrice);
        self::assertTrue($vehicle->isWamInsured);
        self::assertTrue($vehicle->canBeTransferred);
        self::assertFalse($vehicle->hasOpenRecall);

        self::assertInstanceOf(CarbonImmutable::class, $vehicle->registrationDate);
        self::assertSame('2022-09-30T00:00:00+00:00', $vehicle->registrationDate->toIso8601String());

        self::assertInstanceOf(CarbonImmutable::class, $vehicle->apkExpiryDate);
        self::assertSame('2026-11-03T00:00:00+00:00', $vehicle->apkExpiryDate->toIso8601String());

        // Missing fields default to null on the record.
        self::assertNull($vehicle->vehicleType);
        self::assertNull($vehicle->primaryColor);
    }
}
