<?php

namespace Drupal\commerce_auspost\PostageServices;

use Auspost\Postage\Enum\ServiceCode;
use Auspost\Postage\Enum\ServiceOption;
use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\Volume;
use Drupal\physical\VolumeUnit;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;

/**
 * All supported AusPost service definitions.
 *
 * @TODO Use D8's Plugin API instead of hardcoding like this.
 *
 * @package Drupal\commerce_auspost\PostageServices
 */
final class ServiceDefinitions {

  // Parcel services.
  const SERVICE_TYPE_PARCEL = 'parcel';

  // Letter services.
  const SERVICE_TYPE_LETTER = 'letter';

  // Domestic services.
  const SERVICE_DEST_DOMESTIC = 'domestic';

  // International services.
  const SERVICE_DEST_INTERNATIONAL = 'international';

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
      static::SERVICE_DEST_DOMESTIC => [
        'length' => new Length('105', LengthUnit::CENTIMETER),
        'weight' => new Weight('22', WeightUnit::KILOGRAM),
        'volume' => new Volume('0.25', VolumeUnit::CUBIC_METER),
      ],
      static::SERVICE_DEST_INTERNATIONAL => [
        'length' => new Length('105', LengthUnit::CENTIMETER),
        'weight' => new Weight('20', WeightUnit::KILOGRAM),
        'girth' => new Length('140', LengthUnit::CENTIMETER),
      ],
    ];
  }

  /**
   * All supported AusPost services.
   *
   * @TODO: Convert these into plugins and load via plugin manager.
   *
   * @return array
   *   List of services, keyed by an interval service code.
   */
  public static function services() {
    return [
      // Domestic services.
      'AUS_SERVICE_OPTION_STANDARD' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Regular, Standard'),
        'display_title' => t('Standard Post'),
        'description' => t('Australia Post - 2-6 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_SERVICE_OPTION_SIGNATURE' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Regular, Signature required'),
        'display_title' => t('Standard Post, Signature required'),
        'description' => t('Australia Post - 2-6 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_SERVICE_OPTION_INS' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Regular, Insured'),
        'display_title' => t('Standard Post (Insured)'),
        'description' => t('Australia Post - 2-6 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => ServiceOption::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 300,
      ],
      'AUS_SERVICE_OPTION_SIG_INS' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Regular, Signature required, Insured'),
        'display_title' => t('Standard Post (Insured), Signature required'),
        'description' => t('Australia Post - 2-6 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_REGULAR,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => ServiceOption::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 5000,
      ],
      'AUS_PARCEL_EXPRESS' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Express Post'),
        'display_title' => t('Express Post'),
        'description' => t('Australia Post - 1-3 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_PARCEL_EXPRESS_SIGNATURE' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Express Post, Signature required'),
        'display_title' => t('Express Post, Signature required'),
        'description' => t('Australia Post - 1-3 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_PARCEL_EXPRESS_INS' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Express Post, Insured'),
        'display_title' => t('Express Post (Insured)'),
        'description' => t('Australia Post - 1-3 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => ServiceOption::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 300,
      ],
      'AUS_PARCEL_EXPRESS_SIG_INS' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Express Post, Signature reqd, Insured'),
        'display_title' => t('Express Post (Insured), Signature required'),
        'description' => t('Australia Post - 1-3 Days'),
        'service_code' => ServiceCode::AUS_PARCEL_EXPRESS,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
        'sub_opt_code' => ServiceOption::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 5000,
      ],
      'AUS_PARCEL_COURIER' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Courier Post'),
        'display_title' => t('Courier Post'),
        'description' => t('Australia Post - Same Day Delivery'),
        'service_code' => ServiceCode::AUS_PARCEL_COURIER,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'AUS_PARCEL_COUR_INS' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_DOMESTIC,
        'title' => t('Courier Post, Insured'),
        'display_title' => t('Courier Post (Insured)'),
        'description' => t('Australia Post - Same Day Delivery'),
        'service_code' => ServiceCode::AUS_PARCEL_COURIER,
        'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
        'sub_opt_code' => ServiceOption::AUS_SERVICE_OPTION_EXTRA_COVER,
        'extra_cover' => 5000,
      ],
      // International services.
      // @TODO: Figure out service codes for INT_PARCEL_SEA options.
      // 'INT_PARCEL_SEA_OWN_PACKAGING' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Economy Sea'),
      //   'display_title' => t('International Economy Sea'),
      //   'description' => t('Australia Post - 30+ Days'),
      //   'service_code' => 'INT_PARCEL_SEA_OWN_PACKAGING',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      // ],
      // 'INT_PARCEL_SEA_OWN_PACK_SIG' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Economy Sea, Signature required'),
      //   'display_title' => t('International Economy Sea, Signature required'),
      //   'description' => t('Australia Post - 30+ Days'),
      //   'service_code' => 'INT_PARCEL_SEA_OWN_PACKAGING',
      //   'option_code' => 'INT_SIGNATURE_ON_DELIVERY',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      // ],
      // 'INT_PARCEL_SEA_OWN_PACK_INS' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Economy Sea, Insured'),
      //   'display_title' => t('International Economy Sea (Insured)'),
      //   'description' => t('Australia Post - 30+ Days'),
      //   'service_code' => 'INT_PARCEL_SEA_OWN_PACKAGING',
      //   'option_code' => 'INT_EXTRA_COVER',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // Not working due to issues with AusPost.
      // 'INT_PAR_SEA_OWN_PACK_SIG_INS' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Economy Sea, Signature Reqd, Insured'),
      //   'display_title' => t('International Economy Sea (Insured), Signature required'),
      //   'description' => t('Australia Post - 30+ Days'),
      //   'service_code' => 'INT_PARCEL_SEA_OWN_PACKAGING',
      //   'option_code' => [
      //     '0' => 'INT_SIGNATURE_ON_DELIVERY',
      //     '1' => 'INT_EXTRA_COVER',
      //   ],
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // @TODO
      'INT_PARCEL_AIR_OWN_PACKAGING' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_INTERNATIONAL,
        'title' => t('Int Economy Air'),
        'display_title' => t('International Economy Air'),
        'description' => t('Australia Post - 10+ Days'),
        'service_code' => 'INT_PARCEL_AIR_OWN_PACKAGING',
        'option_code' => '',
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_AIR_OWN_PACK_SIG' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_INTERNATIONAL,
        'title' => t('Int Economy Air, Signature required'),
        'display_title' => t('International Economy Air, Signature required'),
        'description' => t('Australia Post - 10+ Days'),
        'service_code' => 'INT_PARCEL_AIR_OWN_PACKAGING',
        'option_code' => 'INT_SIGNATURE_ON_DELIVERY',
        'sub_opt_code' => '',
        'extra_cover' => 0,
      ],
      'INT_PARCEL_AIR_OWN_PACK_INS' => [
        'type' => static::SERVICE_TYPE_PARCEL,
        'destination' => static::SERVICE_DEST_INTERNATIONAL,
        'title' => t('Int Economy Air, Insured'),
        'display_title' => t('International Economy Air (Insured)'),
        'description' => t('Australia Post - 10+ Days'),
        'service_code' => 'INT_PARCEL_AIR_OWN_PACKAGING',
        'option_code' => 'INT_EXTRA_COVER',
        'sub_opt_code' => '',
        'extra_cover' => 5000,
      ],
      // Not working due to issues with AusPost.
      // 'INT_PAR_AIR_OWN_PACK_SIG_INS' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Economy Air, Signature Reqd, Insured'),
      //   'display_title' => t('International Economy Air (Insured), Signature required'),
      //   'description' => t('Australia Post - 10+ Days'),
      //   'service_code' => 'INT_PARCEL_AIR_OWN_PACKAGING',
      //   'option_code' => [
      //     '0' => 'INT_SIGNATURE_ON_DELIVERY',
      //     '1' => 'INT_EXTRA_COVER',
      //   ],
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // @TODO
      // 'INT_PARCEL_STD_OWN_PACKAGING' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Standard'),
      //   'display_title' => t('International Standard'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_PARCEL_STD_OWN_PACKAGING',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      // ],
      // 'INT_PARCEL_STD_OWN_PACK_SIG' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Standard, Signature required'),
      //   'display_title' => t('International Standard, Signature required'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_PARCEL_STD_OWN_PACKAGING',
      //   'option_code' => 'INT_SIGNATURE_ON_DELIVERY',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      // ],
      // 'INT_PARCEL_STD_OWN_PACK_INS' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Standard, Insured'),
      //   'display_title' => t('International Standard (Insured)'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_PARCEL_STD_OWN_PACKAGING',
      //   'option_code' => 'INT_EXTRA_COVER',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // Not working due to issues with AusPost.
      // 'INT_PAR_STD_OWN_PACK_SIG_INS' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Standard, Signature Reqd, Insured'),
      //   'display_title' => t('International Standard (Insured), Signature Required'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_PARCEL_STD_OWN_PACKAGING',
      //   'option_code' => [
      //     '0' => 'INT_SIGNATURE_ON_DELIVERY',
      //     '1' => 'INT_EXTRA_COVER',
      //   ],
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // @TODO
      // 'INT_PARCEL_EXP_OWN_PACKAGING' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Express'),
      //   'display_title' => t('International Express'),
      //   'description' => t('Australia Post - 2-4 Days'),
      //   'service_code' => 'INT_PARCEL_EXP_OWN_PACKAGING',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      // ],
      // 'INT_PARCEL_EXP_OWN_PACK_INS' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Express, Insured'),
      //   'display_title' => t('International Express (Insured)'),
      //   'description' => t('Australia Post - 2-4 Days'),
      //   'service_code' => 'INT_PARCEL_EXP_OWN_PACKAGING',
      //   'option_code' => 'INT_EXTRA_COVER',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // 'INT_PARCEL_COR_OWN_PACKAGING' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Courier'),
      //   'display_title' => t('International Courier'),
      //   'description' => t('Australia Post - 1-2 Days'),
      //   'service_code' => 'INT_PARCEL_COR_OWN_PACKAGING',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      // ],
      // 'INT_PARCEL_COR_OWN_PACK_INS' => [
      //   'type' => static::SERVICE_TYPE_PARCEL,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Courier, Insured'),
      //   'display_title' => t('International Courier (Insured)'),
      //   'description' => t('Australia Post - 1-2 Days'),
      //   'service_code' => 'INT_PARCEL_COR_OWN_PACKAGING',
      //   'option_code' => 'INT_EXTRA_COVER',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      // ],
      // // Domestic Letter services.
      // 'L_AUS_LETTER_SM' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Standard Letter Small'),
      //   'display_title' => t('Standard Letter'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_SMALL',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 5,
      //     'weight' => 250,
      //   ],
      // ],
      // 'L_AUS_LETTER_SM_PRIORITY' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Standard Letter Small Priority'),
      //   'display_title' => t('Standard Letter Priority'),
      //   'description' => t('Australia Post - 1-4 Days'),
      //   'service_code' => 'AUS_LETTER_PRIORITY_SMALL',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 5,
      //     'weight' => 250,
      //   ],
      // ],
      // 'L_AUS_LETTER_LG' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Standard Letter Large'),
      //   'display_title' => t('Standard Letter'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_LARGE',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_LG_PRIORITY' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Standard Letter Large Priority'),
      //   'display_title' => t('Standard Letter Priority'),
      //   'description' => t('Australia Post - 1-4 Days'),
      //   'service_code' => 'AUS_LETTER_PRIORITY_LARGE_500',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_SM_REG_POST' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post Small'),
      //   'display_title' => t('Registered Post Letter'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_SMALL',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_SM_REG_CONF' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post Small - Conf'),
      //   'display_title' => t('Registered Post Letter - Confirmation'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_SMALL',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => 'AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_SM_REG_P2P' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post, Small, Pers2Pers'),
      //   'display_title' => t('Registered Post Letter - Person to Person'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_SMALL',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => 'AUS_SERVICE_OPTION_PERSON_TO_PERSON',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // Not working due to issues with AusPost.
      // 'L_AUS_LET_SM_REG_CONF_P2P' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::self::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post, Small, Conf - P2P'),
      //   'display_title' => t('Registered Post Letter - Person to Person - Confirmation'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_SMALL',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => [
      //     '0' => 'AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION',
      //     '1' => 'AUS_SERVICE_OPTION_PERSON_TO_PERSON',
      //   ],
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // @TODO
      // 'L_AUS_LETTER_LG_REG_POST' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post Large'),
      //   'display_title' => t('Registered Post Letter Large'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_LARGE',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_LG_REG_POST_CONF' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post Large - Conf'),
      //   'display_title' => t('Registered Post Letter Large - Confirmation'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_LARGE',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => 'AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_LG_REG_P2P' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post, Large, Pers2Pers'),
      //   'display_title' => t('Registered Post Letter - Person to Person'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_LARGE',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => 'AUS_SERVICE_OPTION_PERSON_TO_PERSON',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // Not working due to issues with AusPost.
      // 'L_AUS_LET_LG_REG_CONF_P2P' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::self::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Registered Post, Large, Conf - P2P'),
      //   'display_title' => t('Registered Post Letter - Person to Person - Confirmation'),
      //   'description' => t('Australia Post - 2-6 Days'),
      //   'service_code' => 'AUS_LETTER_REGULAR_LARGE',
      //   'option_code' => 'AUS_SERVICE_OPTION_REGISTERED_POST',
      //   'sub_opt_code' => [
      //     '0' => 'AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION',
      //     '1' => 'AUS_SERVICE_OPTION_PERSON_TO_PERSON',
      //   ],
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // @TODO
      // 'L_AUS_LETTER_SM_EXP_POST' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Express Post Envelope Small'),
      //   'display_title' => t('Express Post Envelope Small'),
      //   'description' => t('Australia Post - 1-3 Days'),
      //   'service_code' => 'AUS_LETTER_EXPRESS_SMALL',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 220,
      //     'width' => 110,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_SM_EXP_SIG' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Express Post Envelope Small - Signature'),
      //   'display_title' => t('Express Post Envelope Small - Signature'),
      //   'description' => t('Australia Post - 1-3 Days'),
      //   'service_code' => 'AUS_LETTER_EXPRESS_SMALL',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 220,
      //     'width' => 110,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_MD_EXP' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Express Post Envelope Med'),
      //   'display_title' => t('Express Post Envelope Medium'),
      //   'description' => t('Australia Post - 1-3 Days'),
      //   'service_code' => 'AUS_LETTER_EXPRESS_MEDIUM',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 229,
      //     'width' => 162,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_MD_EXP_SIG' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Express Post Envelope Med - Signature'),
      //   'display_title' => t('Express Post Envelope Medium - Signature'),
      //   'description' => t('Australia Post - 1-3 Days'),
      //   'service_code' => 'AUS_LETTER_EXPRESS_MEDIUM',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 229,
      //     'width' => 162,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_LG_EXPRESS_POST' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Express Post Envelope Large'),
      //   'display_title' => t('Express Post Envelope Large'),
      //   'description' => t('Australia Post - 1-3 Days'),
      //   'service_code' => 'AUS_LETTER_EXPRESS_LARGE',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_STANDARD,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 353,
      //     'width' => 250,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_AUS_LETTER_LG_EXP_POST_SIG' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_DOMESTIC,
      //   'title' => t('Express Post Envelope Large - Signature'),
      //   'display_title' => t('Express Post Envelope Large - Signature'),
      //   'description' => t('Australia Post - 1-3 Days'),
      //   'service_code' => 'AUS_LETTER_EXPRESS_LARGE',
      //   'option_code' => ServiceOption::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY,
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 353,
      //     'width' => 250,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // // International Letter services.
      // 'L_INTL_SERVICE_AIR_MAIL_LGT' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Air Mail Light'),
      //   'display_title' => t('Air Mail Light'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_LETTER_AIR_OWN_PACKAGING_LIGHT',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 50,
      //   ],
      // ],
      // 'L_INTL_SERVICE_AIR_MAIL_MED' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Air Mail Medium'),
      //   'display_title' => t('Air Mail Medium'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_LETTER_AIR_OWN_PACKAGING_MEDIUM',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 250,
      //   ],
      // ],
      // 'L_INTL_SERVICE_AIR_MAIL_HVY' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Air Mail Heavy'),
      //   'display_title' => t('Air Mail Heavy'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_LETTER_AIR_OWN_PACKAGING_HEAVY',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_INT_LETTER_REG_SMALL' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Registered Prepaid DL'),
      //   'display_title' => t('International Registered Prepaid DL Envelope'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_LETTER_REG_SMALL_ENVELOPE',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 240,
      //     'width' => 130,
      //     'thickness' => 5,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_INT_LETTER_REG_LARGE' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Registered Prepaid B4'),
      //   'display_title' => t('International Registered Prepaid B4 Envelope'),
      //   'description' => t('Australia Post - 6+ Days'),
      //   'service_code' => 'INT_LETTER_REG_LARGE_ENVELOPE',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 265,
      //     'width' => 250,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_INT_LET_EXP_OWN_PKG' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Express Letter'),
      //   'display_title' => t('International Express Letter'),
      //   'description' => t('Australia Post - 2+ Days'),
      //   'service_code' => 'INT_LETTER_EXP_OWN_PACKAGING',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_INT_LET_EXP_OWN_PKG_INS' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Express Letter, Insured'),
      //   'display_title' => t('International Express Letter (Insured)'),
      //   'description' => t('Australia Post - 2+ Days'),
      //   'service_code' => 'INT_LETTER_EXP_OWN_PACKAGING',
      //   'option_code' => 'INT_EXTRA_COVER',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_INT_LET_COR_OWN_PKG' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Courier Letter'),
      //   'display_title' => t('International Courier Letter'),
      //   'description' => t('Australia Post - 2+ Days'),
      //   'service_code' => 'INT_LETTER_COR_OWN_PACKAGING',
      //   'option_code' => '',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 0,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
      // 'L_INT_LET_COR_OWN_PKG_INS' => [
      //   'type' => static::SERVICE_TYPE_LETTER,
      //   'destination' => static::SERVICE_DEST_INTERNATIONAL,
      //   'title' => t('Int Courier Letter, Insured'),
      //   'display_title' => t('International Courier Letter (Insured)'),
      //   'description' => t('Australia Post - 2+ Days'),
      //   'service_code' => 'INT_LETTER_COR_OWN_PACKAGING',
      //   'option_code' => 'INT_EXTRA_COVER',
      //   'sub_opt_code' => '',
      //   'extra_cover' => 5000,
      //   'max_dimensions' => [
      //     'length' => 360,
      //     'width' => 260,
      //     'thickness' => 20,
      //     'weight' => 500,
      //   ],
      // ],
    ];
  }

}
