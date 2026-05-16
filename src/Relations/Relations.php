<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Relations;

use NiekNijland\RDW\Exceptions\RdwException;
use NiekNijland\RDW\Fields\OdometerJudgementExplanationField;
use NiekNijland\RDW\Fields\RegisteredVehicleAxleField;
use NiekNijland\RDW\Fields\RegisteredVehicleBodyworkField;
use NiekNijland\RDW\Fields\RegisteredVehicleBodyworkSpecificationField;
use NiekNijland\RDW\Fields\RegisteredVehicleClassField;
use NiekNijland\RDW\Fields\RegisteredVehicleFuelField;
use NiekNijland\RDW\Fields\RegisteredVehicleSpecialFeatureField;
use NiekNijland\RDW\Fields\RegisteredVehicleSubcategoryField;
use NiekNijland\RDW\Fields\RegisteredVehicleTrackSetField;
use NiekNijland\RDW\Query\QueryBuilder;
use NiekNijland\RDW\Rdw;
use NiekNijland\RDW\Records\RegisteredVehicle;
use NiekNijland\RDW\Records\RegisteredVehicleBodywork;

/**
 * Typed relation loaders.
 *
 * Each method accepts the source record and returns a pre-filtered
 * {@see QueryBuilder} for the related dataset, so the caller can still
 * chain additional filters before fetching. Methods throw when a join
 * key is missing on the source — that prevents accidentally widening a
 * query to "everything where kenteken is null".
 */
final class Relations
{
    public function __construct(
        private readonly Rdw $rdw,
    ) {
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\RegisteredVehicleFuel>
     */
    public function fuelsFor(RegisteredVehicle $vehicle): QueryBuilder
    {
        return $this->rdw->registeredVehicleFuels()
            ->where(
                RegisteredVehicleFuelField::LicensePlate,
                self::requireString($vehicle->licensePlate, 'licensePlate'),
            );
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\RegisteredVehicleAxle>
     */
    public function axlesFor(RegisteredVehicle $vehicle): QueryBuilder
    {
        return $this->rdw->registeredVehicleAxles()
            ->where(
                RegisteredVehicleAxleField::LicensePlate,
                self::requireString($vehicle->licensePlate, 'licensePlate'),
            );
    }

    /**
     * @return QueryBuilder<RegisteredVehicleBodywork>
     */
    public function bodyworksFor(RegisteredVehicle $vehicle): QueryBuilder
    {
        return $this->rdw->registeredVehicleBodyworks()
            ->where(
                RegisteredVehicleBodyworkField::LicensePlate,
                self::requireString($vehicle->licensePlate, 'licensePlate'),
            );
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\RegisteredVehicleSubcategory>
     */
    public function subcategoriesFor(RegisteredVehicle $vehicle): QueryBuilder
    {
        return $this->rdw->registeredVehicleSubcategories()
            ->where(
                RegisteredVehicleSubcategoryField::LicensePlate,
                self::requireString($vehicle->licensePlate, 'licensePlate'),
            );
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\RegisteredVehicleSpecialFeature>
     */
    public function specialFeaturesFor(RegisteredVehicle $vehicle): QueryBuilder
    {
        return $this->rdw->registeredVehicleSpecialFeatures()
            ->where(
                RegisteredVehicleSpecialFeatureField::LicensePlate,
                self::requireString($vehicle->licensePlate, 'licensePlate'),
            );
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\RegisteredVehicleTrackSet>
     */
    public function trackSetsFor(RegisteredVehicle $vehicle): QueryBuilder
    {
        return $this->rdw->registeredVehicleTrackSets()
            ->where(
                RegisteredVehicleTrackSetField::LicensePlate,
                self::requireString($vehicle->licensePlate, 'licensePlate'),
            );
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\OdometerJudgementExplanation>
     */
    public function odometerJudgementFor(RegisteredVehicle $vehicle): QueryBuilder
    {
        return $this->rdw->odometerJudgementExplanations()
            ->where(
                OdometerJudgementExplanationField::OdometerJudgementCode,
                self::requireString($vehicle->odometerJudgementCode, 'odometerJudgementCode'),
            );
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\RegisteredVehicleBodyworkSpecification>
     */
    public function specificationsFor(RegisteredVehicleBodywork $bodywork): QueryBuilder
    {
        return $this->rdw->registeredVehicleBodyworkSpecifications()
            ->where(
                RegisteredVehicleBodyworkSpecificationField::LicensePlate,
                self::requireString($bodywork->licensePlate, 'licensePlate'),
            )
            ->where(
                RegisteredVehicleBodyworkSpecificationField::SequenceNumber,
                self::requireString($bodywork->sequenceNumber, 'sequenceNumber'),
            );
    }

    /**
     * @return QueryBuilder<\NiekNijland\RDW\Records\RegisteredVehicleClass>
     */
    public function vehicleClassesFor(RegisteredVehicleBodywork $bodywork): QueryBuilder
    {
        return $this->rdw->registeredVehicleClasses()
            ->where(
                RegisteredVehicleClassField::LicensePlate,
                self::requireString($bodywork->licensePlate, 'licensePlate'),
            )
            ->where(
                RegisteredVehicleClassField::BodyworkSequenceNumber,
                self::requireString($bodywork->sequenceNumber, 'sequenceNumber'),
            );
    }

    private static function requireString(?string $value, string $property): string
    {
        if ($value === null || $value === '') {
            throw new RdwException(sprintf(
                'Cannot resolve relation: source record property "%s" is missing.',
                $property,
            ));
        }

        return $value;
    }
}
