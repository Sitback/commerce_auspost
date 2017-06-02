<?php

namespace Drupal\commerce_auspost\PostageServices\ServiceDefinitions;

use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\Volume;
use Drupal\physical\VolumeUnit;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;

/**
 * All supported AusPost service definitions.
 *
 * These can be altered via Drupal's Plugin API.
 *
 * @see plugin_api
 *
 * @TODO add per-service max dimensions support.
 *
 * @package Drupal\commerce_auspost\PostageServices
 */
final class ServiceDefinitionDefaults {

  // The density used by AusPost to calculate cubic weight.
  const CUBIC_WEIGHT_DENSITY = 250;

  /**
   * Maximum package dimensions supported by AusPost.
   *
   * @see https://auspost.com.au/parcels-mail/postage-tips-guides/size-weight-guidelines
   *
   * @TODO Make this configurable with defaults.
   *
   * @return array
   *   A list of max package dimensions for each destination
   *   (domestic & international). Each dimenion array will have one or more of
   *   the following keys: length, weight, volume, girth.
   */
  public static function maxParcelDimensions() {
    return [
      ServiceDestinations::DOMESTIC => [
        'length' => new Length('105', LengthUnit::CENTIMETER),
        'weight' => new Weight('22', WeightUnit::KILOGRAM),
        'volume' => new Volume('0.25', VolumeUnit::CUBIC_METER),
      ],
      ServiceDestinations::INTERNATIONAL => [
        'length' => new Length('105', LengthUnit::CENTIMETER),
        'weight' => new Weight('20', WeightUnit::KILOGRAM),
        'girth' => new Length('140', LengthUnit::CENTIMETER),
      ],
    ];
  }

  /**
   * All supported AusPost services.
   *
   * @return array
   *   List of services, keyed by an internal service code.
   */
  public static function services() {
    return [
      // Domestic services.
      'AUS_SERVICE_OPTION_STANDARD' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Post - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_SERVICE_OPTION_SIGNATURE' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Post, Signature required - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_SERVICE_OPTION_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Post (Insured) - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 300,
      ],
      'AUS_SERVICE_OPTION_SIG_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Post (Insured), Signature required - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 5000,
      ],
      'AUS_PARCEL_EXPRESS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_PARCEL_EXPRESS_SIGNATURE' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post, Signature required - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_PARCEL_EXPRESS_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post (Insured) - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 300,
      ],
      'AUS_PARCEL_EXPRESS_SIG_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post (Insured), Signature required - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 5000,
      ],
      'AUS_PARCEL_COURIER' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Courier Post - Same Day Delivery'),
        'service_code' => ServiceCodes::AUS_PARCEL_COURIER,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_PARCEL_COUR_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Courier Post (Insured) - Same Day Delivery'),
        'service_code' => ServiceCodes::AUS_PARCEL_COURIER,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 5000,
      ],
      // International services.
      'INT_PARCEL_SEA_OWN_PACKAGING' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Economy Sea - 30+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_SEA_OWN_PACKAGING,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_SEA_OWN_PACK_SIG' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Economy Sea, Signature required - 30+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_SEA_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_SEA_OWN_PACK_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Economy Sea (Insured) - 30+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_SEA_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_EXTRA_COVER,
        'sub_opt_code' => '',
        'extra_cover' => 5000,
      ],
      // @codingStandardsIgnoreStart
      // Not working due to issues with AusPost.
      // 'INT_PAR_SEA_OWN_PACK_SIG_INS' => [
      //   'type' => ServiceTypes::self::PARCEL,
      //   'destination' => ServiceDestinations::INTERNATIONAL,
      //   'description' => t('Australia Post International Economy Sea (Insured), Signature required - 30+ Days'),
      //   'service_code' => ServiceCodes::INT_PARCEL_SEA_OWN_PACKAGING,
      //   'option_code' => [
      //     '0' => ServiceOptions::INT_SIGNATURE_ON_DELIVERY,
      //     '1' => ServiceOptions::INT_EXTRA_COVER,
      //   ],
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // @codingStandardsIgnoreEnd
      'INT_PARCEL_AIR_OWN_PACKAGING' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Economy Air - 10+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_AIR_OWN_PACKAGING,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_AIR_OWN_PACK_SIG' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Economy Air, Signature required - 10+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_AIR_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_AIR_OWN_PACK_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Economy Air (Insured) - 10+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_AIR_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_EXTRA_COVER,
        'sub_opt_code' => '',
        'extra_cover' => 5000,
      ],
      // @codingStandardsIgnoreStart
      // Not working due to issues with AusPost.
      // 'INT_PAR_AIR_OWN_PACK_SIG_INS' => [
      //   'type' => ServiceTypes::self::PARCEL,
      //   'destination' => ServiceDestinations::INTERNATIONAL,
      //   'description' => t('Australia Post International Economy Air (Insured), Signature required - 10+ Days'),
      //   'service_code' => ServiceCodes::INT_PARCEL_AIR_OWN_PACKAGING,
      //   'option_code' => [
      //     '0' => ServiceOptions::INT_SIGNATURE_ON_DELIVERY,
      //     '1' => ServiceOptions::INT_EXTRA_COVER,
      //   ],
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // @codingStandardsIgnoreEnd
      'INT_PARCEL_STD_OWN_PACKAGING' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Standard - 6+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_STD_OWN_PACKAGING,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_STD_OWN_PACK_SIG' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Standard, Signature required - 6+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_STD_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_STD_OWN_PACK_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Standard (Insured) - 6+ Days'),
        'service_code' => ServiceCodes::INT_PARCEL_STD_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_EXTRA_COVER,
        'sub_opt_code' => '',
        'extra_cover' => 5000,
      ],
      // @codingStandardsIgnoreStart
      // Not working due to issues with AusPost.
      // 'INT_PAR_STD_OWN_PACK_SIG_INS' => [
      //   'type' => ServiceTypes::self::PARCEL,
      //   'destination' => ServiceDestinations::INTERNATIONAL,
      //   'description' => t('Australia Post International Standard (Insured), Signature Required - 6+ Days'),
      //   'service_code' => ServiceCodes::INT_PARCEL_STD_OWN_PACKAGING,
      //   'option_code' => [
      //     '0' => ServiceOptions::INT_SIGNATURE_ON_DELIVERY,
      //     '1' => ServiceOptions::INT_EXTRA_COVER,
      //   ],
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // @codingStandardsIgnoreEnd
      'INT_PARCEL_EXP_OWN_PACKAGING' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Express - 2-4 Days'),
        'service_code' => ServiceCodes::INT_PARCEL_EXP_OWN_PACKAGING,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_EXP_OWN_PACK_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Express (Insured) - 2-4 Days'),
        'service_code' => ServiceCodes::INT_PARCEL_EXP_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_EXTRA_COVER,
        'sub_opt_code' => '',
        'extra_cover' => 5000,
      ],
      'INT_PARCEL_COR_OWN_PACKAGING' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Courier - 1-2 Days'),
        'service_code' => ServiceCodes::INT_PARCEL_COR_OWN_PACKAGING,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_COR_OWN_PACK_INS' => [
        'type' => ServiceTypes::PARCEL,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Courier (Insured) - 1-2 Days'),
        'service_code' => ServiceCodes::INT_PARCEL_COR_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_EXTRA_COVER,
        'sub_opt_code' => '',
        'extra_cover' => 5000,
      ],
      // Domestic Letter services.
      'L_AUS_LETTER_SM' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Letter - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_SMALL,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 240,
          'width' => 130,
          'thickness' => 5,
          'weight' => 250,
        ],
      ],
      'L_AUS_LETTER_SM_PRIORITY' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Letter Priority - 1-4 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_PRIORITY_SMALL,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 240,
          'width' => 130,
          'thickness' => 5,
          'weight' => 250,
        ],
      ],
      'L_AUS_LETTER_LG' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Letter - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_LARGE,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_LG_PRIORITY' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Standard Letter Priority - 1-4 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_PRIORITY_LARGE_500,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_SM_REG_POST' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Registered Post Letter - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_SMALL,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 240,
          'width' => 130,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_SM_REG_CONF' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Registered Post Letter - Confirmation - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_SMALL,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION,
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 240,
          'width' => 130,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_SM_REG_P2P' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Registered Post Letter - Person to Person - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_SMALL,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_PERSON_TO_PERSON,
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 240,
          'width' => 130,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      // @codingStandardsIgnoreStart
      // Not working due to issues with AusPost.
      // 'L_AUS_LET_SM_REG_CONF_P2P' => [
      //   'type' => ServiceTypes::LETTER,
      //   'destination' => static::self::SERVICE_DEST_DOMESTIC,
      //   'description' => t('Australia Post Registered Post Letter - Person to Person - Confirmation - 2-6 Days'),
      //   'service_code' => ServiceCodes::AUS_LETTER_REGULAR_SMALL,
      //   'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
      //   'sub_opt_code' => [
      //     '0' => ServiceOptions::AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION,
      //     '1' => ServiceOptions::AUS_SERVICE_OPTION_PERSON_TO_PERSON,
      //   ],
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // @codingStandardsIgnoreEnd
      'L_AUS_LETTER_LG_REG_POST' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Registered Post Letter Large - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_LARGE,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_LG_REG_POST_CONF' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Registered Post Letter Large - Confirmation - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_LARGE,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION,
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_LG_REG_P2P' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Registered Post Letter - Person to Person - 2-6 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_REGULAR_LARGE,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
        'sub_opt_code' => ServiceOptions::AUS_SERVICE_OPTION_PERSON_TO_PERSON,
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      // @codingStandardsIgnoreStart
      // Not working due to issues with AusPost.
      // 'L_AUS_LET_LG_REG_CONF_P2P' => [
      //   'type' => ServiceTypes::LETTER,
      //   'destination' => static::self::SERVICE_DEST_DOMESTIC,
      //   'description' => t('Australia Post Registered Post Letter - Person to Person - Confirmation - 2-6 Days'),
      //   'service_code' => ServiceCodes::AUS_LETTER_REGULAR_LARGE,
      //   'option_code' => ServiceOptions::AUS_SERVICE_OPTION_REGISTERED_POST,
      //   'sub_opt_code' => [
      //     '0' => ServiceOptions::AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION,
      //     '1' => ServiceOptions::AUS_SERVICE_OPTION_PERSON_TO_PERSON,
      //   ],
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // @codingStandardsIgnoreEnds
      'L_AUS_LETTER_SM_EXP_POST' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post Envelope Small - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_EXPRESS_SMALL,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 220,
          'width' => 110,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_SM_EXP_SIG' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post Envelope Small - Signature - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_EXPRESS_SMALL,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 220,
          'width' => 110,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_MD_EXP' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post Envelope Medium - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_EXPRESS_MEDIUM,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 229,
          'width' => 162,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_MD_EXP_SIG' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post Envelope Medium - Signature - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_EXPRESS_MEDIUM,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 229,
          'width' => 162,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_LG_EXPRESS_POST' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post Envelope Large - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_EXPRESS_LARGE,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 353,
          'width' => 250,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_AUS_LETTER_LG_EXP_POST_SIG' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::DOMESTIC,
        'description' => t('Australia Post Express Post Envelope Large - Signature - 1-3 Days'),
        'service_code' => ServiceCodes::AUS_LETTER_EXPRESS_LARGE,
        'option_code' => ServiceOptions::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 353,
          'width' => 250,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      // International Letter services.
      'L_INTL_SERVICE_AIR_MAIL_LGT' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post Air Mail Light - 6+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_AIR_OWN_PACKAGING_LIGHT,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 50,
        ],
      ],
      'L_INTL_SERVICE_AIR_MAIL_MED' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post Air Mail Medium - 6+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_AIR_OWN_PACKAGING_MEDIUM,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 250,
        ],
      ],
      'L_INTL_SERVICE_AIR_MAIL_HVY' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post Air Mail Heavy - 6+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_AIR_OWN_PACKAGING_HEAVY,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_INT_LETTER_REG_SMALL' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Registered Prepaid DL Envelope - 6+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_REG_SMALL_ENVELOPE,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 240,
          'width' => 130,
          'thickness' => 5,
          'weight' => 500,
        ],
      ],
      'L_INT_LETTER_REG_LARGE' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Registered Prepaid B4 Envelope - 6+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_REG_LARGE_ENVELOPE,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 265,
          'width' => 250,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_INT_LET_EXP_OWN_PKG' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Express Letter - 2+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_EXP_OWN_PACKAGING,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_INT_LET_EXP_OWN_PKG_INS' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Express Letter (Insured) - 2+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_EXP_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_EXTRA_COVER,
        'sub_opt_code' => '',
        'extra_cover' => 5000,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_INT_LET_COR_OWN_PKG' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Courier Letter - 2+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_COR_OWN_PACKAGING,
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
      'L_INT_LET_COR_OWN_PKG_INS' => [
        'type' => ServiceTypes::LETTER,
        'destination' => ServiceDestinations::INTERNATIONAL,
        'description' => t('Australia Post International Courier Letter (Insured) - 2+ Days'),
        'service_code' => ServiceCodes::INT_LETTER_COR_OWN_PACKAGING,
        'option_code' => ServiceOptions::INT_EXTRA_COVER,
        'sub_opt_code' => '',
        'extra_cover' => 5000,
        'max_dimensions' => [
          'length' => 360,
          'width' => 260,
          'thickness' => 20,
          'weight' => 500,
        ],
      ],
    ];
  }

}
