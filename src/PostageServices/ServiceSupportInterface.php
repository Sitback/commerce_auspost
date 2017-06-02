<?php

namespace Drupal\commerce_auspost\PostageServices;

use Drupal\physical\Length;
use Drupal\physical\Volume;
use Drupal\physical\Weight;

/**
 * Defines AusPost service support helpers.
 *
 * @package Drupal\commerce_auspost\PostageServices
 */
interface ServiceSupportInterface {

  /**
   * Maximum package dimensions supported by AusPost.
   *
   * @param string $destination
   *   Package destination.
   *
   * @return \Drupal\physical\Measurement[]
   *   An array with one or more of the following keys: length, weight, volume,
   *   girth.
   *
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   *   If package destination is not valid.
   */
  public function getMaxParcelDimensions($destination);

  /**
   * Calculates a parcel's cubic weight, as per AusPost's guidelines.
   *
   * @param \Drupal\physical\Volume $volume
   *   Current package volume.
   *
   * @return \Drupal\physical\Weight
   *   Cubic weight.
   */
  public function calculateParcelCubicWeight(Volume $volume);

  /**
   * Get a parcel's shipping weight - the actual weight or the cubic weight.
   *
   * @param \Drupal\physical\Volume $volume
   *   Current package volume.
   * @param \Drupal\physical\Weight $weight
   *   The actual weight of the parcel.
   *
   * @return \Drupal\physical\Weight
   *   Shipping weight.
   */
  public function calculateParcelWeight(Volume $volume, Weight $weight);

  /**
   * Confirm that a package meets AusPost's size guidelines.
   *
   * @param \Drupal\physical\Length $length
   *   Package length.
   * @param \Drupal\physical\Length $width
   *   Package width.
   * @param \Drupal\physical\Length $height
   *   Package height.
   * @param string $destination
   *   Package destination (one of ServiceDefinitions::SERVICE_DEST_DOMESTIC or
   *   ServiceDefinitions::SERVICE_DEST_INTERNATIONAL).
   *
   * @see https://auspost.com.au/parcels-mail/postage-tips-guides/size-weight-guidelines
   *
   * @return bool
   *   TRUE if package passes validation.
   *
   * @throws \Drupal\commerce_auspost\PostageServices\PackageSizeException
   *   If package size does not meet the guidelines.
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   *   If destination is not valid.
   */
  public function validatePackageSize(
    Length $length,
    Length $width,
    Length $height,
    $destination
  );

}
