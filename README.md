# rdw-php

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nieknijland/rdw-php.svg?style=flat-square)](https://packagist.org/packages/nieknijland/rdw-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/nieknijland/rdw-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nieknijland/rdw-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/nieknijland/rdw-php.svg?style=flat-square)](https://packagist.org/packages/nieknijland/rdw-php)

A typed PHP client for the [RDW Open Data](https://opendata.rdw.nl) `Voertuigen`
datasets. Avoids magic Dutch field strings: every dataset has a generated
field enum with English case names, every record is a typed value object,
and dates are `CarbonImmutable` in UTC.

## Installation

```bash
composer require nieknijland/rdw-php
```

Requires PHP 8.4+.

## Quick start

```php
use NiekNijland\RDW\Rdw;
use NiekNijland\RDW\Fields\RegisteredVehicleField;
use NiekNijland\RDW\Query\SortDirection;

$rdw = new Rdw();

$vehicles = $rdw->registeredVehicles()
    ->where(RegisteredVehicleField::CommercialName, 'POLO')
    ->where(RegisteredVehicleField::CanBeTransferred, true)
    ->orderBy(RegisteredVehicleField::RegistrationDate, SortDirection::Desc)
    ->limit(10)
    ->get();

foreach ($vehicles as $vehicle) {
    echo $vehicle->licensePlate.' '.$vehicle->brand.' '.$vehicle->commercialName.PHP_EOL;
    echo '  registered: '.$vehicle->registrationDate?->toDateString().PHP_EOL;
    echo '  apk: '.$vehicle->apkExpiryDate?->toDateString().PHP_EOL;
}
```

## Configuration

```php
use NiekNijland\RDW\Rdw;
use NiekNijland\RDW\Http\Configuration;

$rdw = new Rdw(new Configuration(
    appToken: 'YOUR_SOCRATA_APP_TOKEN', // optional, raises your rate limit
    userAgent: 'your-app/1.0',
    timeoutSeconds: 10.0,
));
```

The HTTP layer raises `NiekNijland\RDW\Exceptions\RateLimitException` on HTTP
`429` (with `retryAfterSeconds` extracted from the `Retry-After` header) and
`HttpException` on any other non-2xx response.

## Supported datasets

| Method on `Rdw`                                | RDW dataset id   | Record class                                                       |
|------------------------------------------------|------------------|--------------------------------------------------------------------|
| `registeredVehicles()`                         | `m9d7-ebf2`      | `Records\RegisteredVehicle`                                        |
| `registeredVehicleFuels()`                     | `8ys7-d773`      | `Records\RegisteredVehicleFuel`                                    |
| `registeredVehicleAxles()`                     | `3huj-srit`      | `Records\RegisteredVehicleAxle`                                    |
| `registeredVehicleBodyworks()`                 | `vezc-m2t6`      | `Records\RegisteredVehicleBodywork`                                |
| `registeredVehicleBodyworkSpecifications()`    | `jhie-znh9`      | `Records\RegisteredVehicleBodyworkSpecification`                   |
| `registeredVehicleClasses()`                   | `kmfi-hrps`      | `Records\RegisteredVehicleClass`                                   |
| `registeredVehicleSubcategories()`             | `2ba7-embk`      | `Records\RegisteredVehicleSubcategory`                             |
| `registeredVehicleSpecialFeatures()`           | `7ug8-2dtt`      | `Records\RegisteredVehicleSpecialFeature`                          |
| `registeredVehicleTrackSets()`                 | `3xwf-ince`      | `Records\RegisteredVehicleTrackSet`                                |
| `odometerJudgementExplanations()`              | `jqs4-4kvw`      | `Records\OdometerJudgementExplanation`                             |

## Query builder

```php
$builder = $rdw->registeredVehicles()
    ->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN')
    ->whereIn(RegisteredVehicleField::VehicleType, ['Personenauto', 'Bedrijfsauto'])
    ->where(RegisteredVehicleField::FirstAdmissionDate, $threshold, '>=')
    ->select(
        RegisteredVehicleField::LicensePlate,
        RegisteredVehicleField::Brand,
        RegisteredVehicleField::CommercialName,
    )
    ->orderBy(RegisteredVehicleField::RegistrationDate, SortDirection::Desc)
    ->limit(25);

$vehicles = $builder->get();        // list<RegisteredVehicle>
$first    = $builder->first();      // ?RegisteredVehicle
```

The builder is immutable: every chained method returns a clone. You can
share a partially built query across functions safely.

### Boolean fields

Fields backed by RDW's `Ja`/`Nee` text values are typed as `bool` in the
records and in the where-clauses:

```php
$rdw->registeredVehicles()
    ->where(RegisteredVehicleField::CanBeTransferred, true)  // → tenaamstellen_mogelijk='Ja'
    ->where(RegisteredVehicleField::HasOpenRecall, false)    // → openstaande_terugroepactie_indicator='Nee'
    ->get();
```

### Dates

Calendar-date fields (`*_dt`) hydrate to `CarbonImmutable` at midnight UTC.
When you pass any `DateTimeInterface` to `where()` for a date field, it is
serialized as a Socrata datetime literal:

```php
use Carbon\CarbonImmutable;

$rdw->registeredVehicles()
    ->where(RegisteredVehicleField::FirstAdmissionDate, CarbonImmutable::parse('1991-01-01'), '>=')
    ->where(RegisteredVehicleField::FirstAdmissionDate, CarbonImmutable::parse('1992-01-01'), '<')
    ->get();
```

### Aggregates and grouped projections

For aggregate or grouped queries, use `getProjection()` to receive raw
associative arrays — the record schema does not fit aggregate rows:

```php
$variants = $rdw->registeredVehicles()
    ->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN')
    ->select(RegisteredVehicleField::CommercialName)
    ->count(null, 'count')
    ->groupBy(RegisteredVehicleField::CommercialName)
    ->getProjection();

// → [['handelsbenaming' => 'POLO', 'count' => '12345'], ...]
```

### Raw SoQL escape hatch

For Socrata expressions the typed API does not model, fall back to the raw
methods. Raw fragments take RDW field keys, not English aliases:

```php
$rdw->registeredVehicles()
    ->where(RegisteredVehicleField::CommercialName, 'GSX-R 1100')
    ->whereRaw("datum_eerste_toelating_dt >= '1991-01-01T00:00:00.000'")
    ->whereRaw("datum_eerste_toelating_dt <  '1992-01-01T00:00:00.000'")
    ->selectRaw('count(distinct kenteken)', 'unique_kentekens')
    ->getProjection();
```

### Pagination

`get()` issues a single request and returns one page. For large result sets
use `iterate()`, which yields hydrated records lazily and pages internally:

```php
foreach ($rdw->registeredVehicles()
    ->where(RegisteredVehicleField::Brand, 'VOLKSWAGEN')
    ->iterate(pageSize: 1000) as $vehicle) {
    // process one vehicle at a time without buffering the whole set
}
```

An outer `->limit(N)` is respected as a hard ceiling.

## Relations

A registered vehicle is the entry point for every other dataset. Relations
return pre-filtered query builders, so you can chain more filters before
hitting the API:

```php
$vehicle = $rdw->registeredVehicles()
    ->where(RegisteredVehicleField::LicensePlate, '6ZNS30')
    ->first();

$fuels         = $rdw->relations()->fuelsFor($vehicle)->get();
$axles         = $rdw->relations()->axlesFor($vehicle)->get();
$bodyworks     = $rdw->relations()->bodyworksFor($vehicle)->get();
$subcategories = $rdw->relations()->subcategoriesFor($vehicle)->get();
$specials      = $rdw->relations()->specialFeaturesFor($vehicle)->get();
$trackSets     = $rdw->relations()->trackSetsFor($vehicle)->get();
$judgement     = $rdw->relations()->odometerJudgementFor($vehicle)->first();

// Composite key: bodywork → bodywork specifications / vehicle classes
$bodywork        = $bodyworks[0];
$specifications  = $rdw->relations()->specificationsFor($bodywork)->get();
$vehicleClasses  = $rdw->relations()->vehicleClassesFor($bodywork)->get();
```

Relations throw `RdwException` when a required join key is `null` on the
source record — that prevents accidentally querying "everything where
kenteken IS NULL".

## Schema regeneration

Field enums and record classes live under `src/Fields/` and `src/Records/`.
They are generated from the curated override classes under
`src/Schema/Overrides/` and validated against checked-in metadata
snapshots under `metadata/`.

To refresh after a change to overrides (or after pulling a new RDW
schema snapshot):

```bash
composer rdw:generate
```

Generation fails loudly when an RDW metadata field has no override or
when an override points at a field RDW no longer exposes — that is the
public API stability contract from the implementation plan.

## Testing

```bash
composer test
```

The default suite is fully offline and uses mocked HTTP. To run live tests
against `opendata.rdw.nl`, add your own tests behind an environment guard;
the package does not ship live tests in the default suite.

## Architecture

```
src/
  Rdw.php                      # entry point
  Http/
    Configuration.php          # app token, user agent, timeout
    SocrataClient.php          # thin Guzzle wrapper
  Datasets/
    DatasetId.php              # backed enum of in-scope RDW dataset ids
    DatasetRegistry.php
  Schema/
    CastType.php               # how a single raw value is transformed
    FieldDescriptor.php
    DatasetSchema.php
    SchemaRegistry.php
    Overrides/                 # the canonical English name + cast map (one per dataset)
  Fields/                      # GENERATED — enum cases for typed queries
  Records/                     # GENERATED — typed value objects per dataset
    Hydrator.php
    ValueCaster.php
  Query/
    QueryBuilder.php
    SortDirection.php
  Relations/
    Relations.php              # typed loaders (fuelsFor, axlesFor, …)
    RelationDefinition.php
    RelationRegistry.php
    Cardinality.php
  Generator/
    EnumGenerator.php          # produces src/Fields and src/Records from overrides
    SchemaSnapshot.php         # reads metadata/{id}.json
  Exceptions/
    RdwException.php           # base
    HttpException.php
    RateLimitException.php
    DatasetNotFoundException.php
    MissingFieldOverrideException.php
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
