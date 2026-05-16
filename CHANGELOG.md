# Changelog

All notable changes to `rdw-opendata-php` will be documented in this file.

## v0.1.0 - 2026-05-16

Initial release.

### Added

- Typed entry point `Rdw` exposing 10 RDW `Voertuigen` datasets (registered vehicles, fuels, axles, bodyworks, bodywork specifications, classes, subcategories, special features, track sets, odometer judgement explanations).
- Generated field enums under `src/Fields/` and typed record value objects under `src/Records/`, with `CarbonImmutable` UTC dates and `Ja`/`Nee` → `bool` casts.
- Immutable fluent SoQL query builder covering every documented Socrata parameter (`$select`, `$where`, `$order`, `$group`, `$having`, `$limit`, `$offset`, `$q`) with ~30 chainable methods including `where`, `whereIn`, `whereNotIn`, `whereNull`, `whereNotNull`, `whereBetween`, `whereNotBetween`, `whereLike`, `whereStartsWith`, `whereContains`, `whereAny`, `whereNot`, `search`, `select`, `selectRaw`, `groupBy`, `orderBy`, `orderByRaw`, `limit`, `offset`, `distinct`, `count`, `countDistinct`, `sum`, `avg`, `min`, `max`, `havingRaw`, and the terminators `get`, `first`, `exists`, `pluck`, `iterate`, `getProjection`, `toSoqlParams`.
- Typed `Relations` loader for navigating between datasets (e.g. `fuelsFor`, `axlesFor`, `bodyworksFor`, `subcategoriesFor`, `specialFeaturesFor`, `trackSetsFor`, `odometerJudgementFor`, `specificationsFor`, `vehicleClassesFor`).
- Schema generator (`bin/rdw-generate`, `composer rdw:generate`) producing `src/Fields/*.php` and `src/Records/*.php` from override classes plus checked-in `metadata/*.json` snapshots, with CI drift detection via `composer rdw:generate:check`.
- Typed exception hierarchy rooted at `RdwException`, with dedicated `HttpException` (carries `statusCode`, `responseBody`), `RateLimitException` (carries `retryAfterSeconds` from the `Retry-After` header on HTTP 429), `DatasetNotFoundException`, and `MissingFieldOverrideException`.
- Raw escape hatches: `Rdw::rawRows()` and `Rdw::rawMetadata()` for ad-hoc queries, plus `whereRaw` / `selectRaw` / `orderByRaw` / `havingRaw` on the builder.
