<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use NiekNijland\RDW\Datasets\DatasetId;
use NiekNijland\RDW\Http\Configuration;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Rdw;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Rdw::class)]
final class RdwTest extends TestCase
{
    public function test_exposes_dataset_registry_and_configuration(): void
    {
        $rdw = new Rdw();

        self::assertCount(10, $rdw->datasets()->all());
        self::assertSame(Configuration::DEFAULT_BASE_URL, $rdw->configuration()->baseUrl);
    }

    public function test_raw_rows_delegates_to_the_socrata_client(): void
    {
        $guzzle = new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(200, [], json_encode([['kenteken' => 'AB-123-C']], JSON_THROW_ON_ERROR)),
            ])),
            'base_uri' => 'https://opendata.rdw.nl/',
        ]);

        $rdw = new Rdw(http: new SocrataClient(new Configuration(), $guzzle));

        $rows = $rdw->rawRows(DatasetId::RegisteredVehicles, ['kenteken' => 'AB-123-C']);

        self::assertSame([['kenteken' => 'AB-123-C']], $rows);
    }
}
