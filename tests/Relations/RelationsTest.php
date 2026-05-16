<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Relations;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use NiekNijland\RDW\Exceptions\RdwException;
use NiekNijland\RDW\Http\Configuration;
use NiekNijland\RDW\Http\SocrataClient;
use NiekNijland\RDW\Rdw;
use NiekNijland\RDW\Records\RegisteredVehicle;
use NiekNijland\RDW\Records\RegisteredVehicleBodywork;
use NiekNijland\RDW\Relations\Relations;
use NiekNijland\RDW\Tests\Support\RequestSpy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Relations::class)]
final class RelationsTest extends TestCase
{
    private RequestSpy $spy;

    protected function setUp(): void
    {
        $this->spy = new RequestSpy();
    }

    public function test_fuels_relation_filters_by_license_plate(): void
    {
        $rdw = $this->newRdw([new Response(200, [], '[]')]);

        $rdw->relations()
            ->fuelsFor(new RegisteredVehicle(licensePlate: '6ZNS30'))
            ->get();

        $uri = $this->spy->last()->getUri();
        parse_str($uri->getQuery(), $params);
        self::assertSame("kenteken = '6ZNS30'", $params['$where']);
        self::assertStringContainsString('/resource/8ys7-d773.json', (string) $uri);
    }

    public function test_subcategories_relation_filters_by_license_plate(): void
    {
        $rdw = $this->newRdw([new Response(200, [], '[]')]);

        $rdw->relations()
            ->subcategoriesFor(new RegisteredVehicle(licensePlate: '6ZNS30'))
            ->get();

        $uri = $this->spy->last()->getUri();
        parse_str($uri->getQuery(), $params);
        self::assertSame("kenteken = '6ZNS30'", $params['$where']);
        self::assertStringContainsString('/resource/2ba7-embk.json', (string) $uri);
    }

    public function test_track_sets_relation_filters_by_license_plate(): void
    {
        $rdw = $this->newRdw([new Response(200, [], '[]')]);

        $rdw->relations()
            ->trackSetsFor(new RegisteredVehicle(licensePlate: '6ZNS30'))
            ->get();

        $uri = $this->spy->last()->getUri();
        parse_str($uri->getQuery(), $params);
        self::assertSame("kenteken = '6ZNS30'", $params['$where']);
        self::assertStringContainsString('/resource/3xwf-ince.json', (string) $uri);
    }

    public function test_bodywork_specifications_relation_uses_composite_key(): void
    {
        $rdw = $this->newRdw([new Response(200, [], '[]')]);

        $rdw->relations()
            ->specificationsFor(new RegisteredVehicleBodywork(
                licensePlate: '6ZNS30',
                sequenceNumber: '1',
            ))
            ->get();

        parse_str($this->spy->last()->getUri()->getQuery(), $params);
        self::assertSame(
            "(kenteken = '6ZNS30') AND (carrosserie_volgnummer = '1')",
            $params['$where'],
        );
    }

    public function test_relation_throws_when_join_key_is_missing(): void
    {
        $rdw = new Rdw();

        $this->expectException(RdwException::class);

        $rdw->relations()->fuelsFor(new RegisteredVehicle());
    }

    public function test_relation_throws_when_composite_join_partially_missing(): void
    {
        $rdw = new Rdw();

        $this->expectException(RdwException::class);

        $rdw->relations()->specificationsFor(new RegisteredVehicleBodywork(licensePlate: '6ZNS30'));
    }

    public function test_relation_throws_when_join_key_is_whitespace_only(): void
    {
        $rdw = new Rdw();

        $this->expectException(RdwException::class);

        $rdw->relations()->fuelsFor(new RegisteredVehicle(licensePlate: '   '));
    }

    /**
     * @param list<Response> $responses
     */
    private function newRdw(array $responses): Rdw
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        $this->spy->attach($handler);

        $guzzle = new Client([
            'handler' => $handler,
            'base_uri' => 'https://opendata.rdw.nl/',
        ]);

        return new Rdw(http: new SocrataClient(new Configuration(), $guzzle));
    }
}
