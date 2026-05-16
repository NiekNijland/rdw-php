<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Fields;

/**
 * Public-facing field names for RDW dataset "m9d7-ebf2".
 *
 * GENERATED FILE - do not edit by hand. Update the matching Overrides class
 * in src/Schema/Overrides and re-run `composer rdw:generate`.
 */
enum RegisteredVehicleField: string
{
    case LicensePlate = 'kenteken';
    case VehicleType = 'voertuigsoort';
    case Brand = 'merk';
    case CommercialName = 'handelsbenaming';
    case GrossBpm = 'bruto_bpm';
    case Configuration = 'inrichting';
    case SeatCount = 'aantal_zitplaatsen';
    case PrimaryColor = 'eerste_kleur';
    case SecondaryColor = 'tweede_kleur';
    case CylinderCount = 'aantal_cilinders';
    case EngineDisplacement = 'cilinderinhoud';
    case EmptyMass = 'massa_ledig_voertuig';
    case PermittedMaximumMass = 'toegestane_maximum_massa_voertuig';
    case ReadyToDriveMass = 'massa_rijklaar';
    case MaximumUnbrakedTowingMass = 'maximum_massa_trekken_ongeremd';
    case MaximumBrakedTowingMass = 'maximum_trekken_massa_geremd';
    case IsWaitingForInspection = 'wacht_op_keuren';
    case CatalogPrice = 'catalogusprijs';
    case IsWamInsured = 'wam_verzekerd';
    case MaximumDesignSpeed = 'maximale_constructiesnelheid';
    case LoadCapacity = 'laadvermogen';
    case SemiTrailerBrakedMass = 'oplegger_geremd';
    case TrailerAutonomousBrakedMass = 'aanhangwagen_autonoom_geremd';
    case TrailerCenterAxleBrakedMass = 'aanhangwagen_middenas_geremd';
    case StandingPlaceCount = 'aantal_staanplaatsen';
    case DoorCount = 'aantal_deuren';
    case WheelCount = 'aantal_wielen';
    case DistanceCouplingToRear = 'afstand_hart_koppeling_tot_achterzijde_voertuig';
    case DistanceFrontToCoupling = 'afstand_voorzijde_voertuig_tot_hart_koppeling';
    case AlternativeMaximumSpeed = 'afwijkende_maximum_snelheid';
    case Length = 'lengte';
    case Width = 'breedte';
    case EuropeanVehicleCategory = 'europese_voertuigcategorie';
    case EuropeanVehicleCategoryAddition = 'europese_voertuigcategorie_toevoeging';
    case EuropeanVariantCategoryAddition = 'europese_uitvoeringcategorie_toevoeging';
    case ChassisNumberLocation = 'plaats_chassisnummer';
    case TechnicalMaximumMass = 'technische_max_massa_voertuig';
    case Type = 'type';
    case GasInstallationType = 'type_gasinstallatie';
    case TypeApprovalNumber = 'typegoedkeuringsnummer';
    case Variant = 'variant';
    case Execution = 'uitvoering';
    case EuTypeApprovalChangeSequenceNumber = 'volgnummer_wijziging_eu_typegoedkeuring';
    case PowerToReadyMassRatio = 'vermogen_massarijklaar';
    case Wheelbase = 'wielbasis';
    case IsExportRegistration = 'export_indicator';
    case HasOpenRecall = 'openstaande_terugroepactie_indicator';
    case IsTaxi = 'taxi_indicator';
    case MaximumCombinationMass = 'maximum_massa_samenstelling';
    case WheelchairPlaceCount = 'aantal_rolstoelplaatsen';
    case MaximumAssistiveSpeed = 'maximum_ondersteunende_snelheid';
    case LastOdometerRegistrationYear = 'jaar_laatste_registratie_tellerstand';
    case OdometerJudgement = 'tellerstandoordeel';
    case OdometerJudgementCode = 'code_toelichting_tellerstandoordeel';
    case CanBeTransferred = 'tenaamstellen_mogelijk';
    case ApkExpiryDate = 'vervaldatum_apk_dt';
    case RegistrationDate = 'datum_tenaamstelling_dt';
    case FirstAdmissionDate = 'datum_eerste_toelating_dt';
    case FirstNetherlandsRegistrationDate = 'datum_eerste_tenaamstelling_in_nederland_dt';
    case TachographExpiryDate = 'vervaldatum_tachograaf_dt';
    case MaximumFrontAxleLoad = 'maximum_last_onder_de_vooras_sen_tezamen_koppeling';
    case BrakeSystemTypeCode = 'type_remsysteem_voertuig_code';
    case TrackChassisConfigurationCode = 'rupsonderstelconfiguratiecode';
    case WheelbaseMinimum = 'wielbasis_voertuig_minimum';
    case WheelbaseMaximum = 'wielbasis_voertuig_maximum';
    case LengthMinimum = 'lengte_voertuig_minimum';
    case LengthMaximum = 'lengte_voertuig_maximum';
    case WidthMinimum = 'breedte_voertuig_minimum';
    case WidthMaximum = 'breedte_voertuig_maximum';
    case Height = 'hoogte_voertuig';
    case HeightMinimum = 'hoogte_voertuig_minimum';
    case HeightMaximum = 'hoogte_voertuig_maximum';
    case OperationalMassMinimum = 'massa_bedrijfsklaar_minimaal';
    case OperationalMassMaximum = 'massa_bedrijfsklaar_maximaal';
    case TechnicalCouplingPointMass = 'technisch_toelaatbaar_massa_koppelpunt';
    case TechnicalMaximumMassUpper = 'maximum_massa_technisch_maximaal';
    case TechnicalMaximumMassLower = 'maximum_massa_technisch_minimaal';
    case NetherlandsSubcategory = 'subcategorie_nederland';
    case CouplingPointVerticalLoadTrailer = 'verticale_belasting_koppelpunt_getrokken_voertuig';
    case EfficiencyClassification = 'zuinigheidsclassificatie';
    case BpmDepreciationApprovalDate = 'registratie_datum_goedkeuring_afschrijvingsmoment_bpm_dt';
    case AverageLoadValue = 'gem_lading_wrde';
    case AerodynamicEquipment = 'aerodyn_voorz';
    case AlternativeDriveAdditionalMass = 'massa_alt_aandr';
    case HasExtendedCab = 'verl_cab_ind';
    case LegalPassengerSeatCount = 'aantal_passagiers_zitplaatsen_wettelijk';
    case DesignationNumber = 'aanwijzingsnummer';
}
