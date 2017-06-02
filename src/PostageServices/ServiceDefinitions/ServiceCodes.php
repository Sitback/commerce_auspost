<?php

namespace Drupal\commerce_auspost\PostageServices\ServiceDefinitions;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Defines Australia Post service codes.
 *
 * @package Drupal\commerce_auspost\PostageServices\ServiceDefinitions
 */
final class ServiceCodes extends AbstractEnum {

  // Domestic letter regular post large envelope.
  const AUS_LETTER_REGULAR_SMALL = 'AUS_LETTER_REGULAR_SMALL';

  // Domestic letter regular post large envelope.
  const AUS_LETTER_REGULAR_LARGE = 'AUS_LETTER_REGULAR_LARGE';

  // Domestic letter priority post small envelope.
  const AUS_LETTER_PRIORITY_SMALL = 'AUS_LETTER_PRIORITY_SMALL';

  // Domestic letter priority post large envelope (500g).
  const AUS_LETTER_PRIORITY_LARGE_500 = 'AUS_LETTER_PRIORITY_LARGE_500';

  // Domestic letter express post small envelope.
  const AUS_LETTER_EXPRESS_SMALL = 'AUS_LETTER_EXPRESS_SMALL';

  // Domestic letter express post medium envelope.
  const AUS_LETTER_EXPRESS_MEDIUM = 'AUS_LETTER_EXPRESS_MEDIUM';

  // Domestic letter express post large envelope.
  const AUS_LETTER_EXPRESS_LARGE = 'AUS_LETTER_EXPRESS_LARGE';

  // Domestic parcel post.
  const AUS_PARCEL_REGULAR = 'AUS_PARCEL_REGULAR';

  // Domestic parcel post small (500g) satchel.
  const AUS_PARCEL_REGULAR_SATCHEL_500G = 'AUS_PARCEL_REGULAR_SATCHEL_500G';

  // Domestic parcel post medium (3kg) satchel.
  const AUS_PARCEL_REGULAR_SATCHEL_3KG = 'AUS_PARCEL_REGULAR_SATCHEL_3KG';

  // Domestic parcel post large (5kg) satchel.
  const AUS_PARCEL_REGULAR_SATCHEL_5KG = 'AUS_PARCEL_REGULAR_SATCHEL_5KG';

  // Domestic express post.
  const AUS_PARCEL_EXPRESS = 'AUS_PARCEL_EXPRESS';

  // Domestic express post small (500g) satchel.
  const AUS_PARCEL_EXPRESS_SATCHEL_500G = 'AUS_PARCEL_EXPRESS_SATCHEL_500G';

  // Domestic express post medium (3kg) satchel.
  const AUS_PARCEL_EXPRESS_SATCHEL_3KG = 'AUS_PARCEL_EXPRESS_SATCHEL_3KG';

  // Domestic express post large (5kg) satchel.
  const AUS_PARCEL_EXPRESS_SATCHEL_5KG = 'AUS_PARCEL_EXPRESS_SATCHEL_5KG';

  // Domestic courier post.
  const AUS_PARCEL_COURIER = 'AUS_PARCEL_COURIER';

  // Domestic courier post assessed medium satchel.
  const AUS_PARCEL_COURIER_SATCHEL_MEDIUM = 'AUS_PARCEL_COURIER_SATCHEL_MEDIUM';


  // International letter economy air, own packaging.
  const INT_LETTER_AIR_OWN_PACKAGING_LIGHT = 'INT_LETTER_AIR_OWN_PACKAGING_LIGHT';

  // International letter economy air, own packaging.
  const INT_LETTER_AIR_OWN_PACKAGING_MEDIUM = 'INT_LETTER_AIR_OWN_PACKAGING_MEDIUM';

  // International letter economy air, own packaging.
  const INT_LETTER_AIR_OWN_PACKAGING_HEAVY = 'INT_LETTER_AIR_OWN_PACKAGING_HEAVY';

  // International letter courier, own packaging.
  const INT_LETTER_COR_OWN_PACKAGING = 'INT_LETTER_COR_OWN_PACKAGING';

  // International letter express, own packaging.
  const INT_LETTER_EXP_OWN_PACKAGING = 'INT_LETTER_EXP_OWN_PACKAGING';

  // International letter registered post DL.
  const INT_LETTER_REG_SMALL_ENVELOPE = 'INT_LETTER_REG_SMALL_ENVELOPE';

  // International letter registered post B4.
  const INT_LETTER_REG_LARGE_ENVELOPE = 'INT_LETTER_REG_LARGE_ENVELOPE';

  // International parcel economy sea, own packaging.
  const INT_PARCEL_SEA_OWN_PACKAGING = 'INT_PARCEL_SEA_OWN_PACKAGING';

  // International parcel courier, own packaging.
  const INT_PARCEL_COR_OWN_PACKAGING = 'INT_PARCEL_COR_OWN_PACKAGING';

  // International parcel standard, own packaging.
  const INT_PARCEL_STD_OWN_PACKAGING = 'INT_PARCEL_STD_OWN_PACKAGING';

  // International parcel express, own packaging.
  const INT_PARCEL_EXP_OWN_PACKAGING = 'INT_PARCEL_EXP_OWN_PACKAGING';

  // International parcel air mail, own packaging.
  const INT_PARCEL_AIR_OWN_PACKAGING = 'INT_PARCEL_AIR_OWN_PACKAGING';

}
