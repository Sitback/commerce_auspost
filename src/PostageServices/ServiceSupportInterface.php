<?php

namespace Drupal\commerce_auspost\PostageServices;

use Drupal\physical\Length;

/**
 * Defines AusPost service support helpers.
 *
 * @package Drupal\commerce_auspost\PostageServices
 */
interface ServiceSupportInterface {

  /**
   * Check if a service exists.
   *
   * @param string $key
   *   Service key.
   *
   * @return bool
   *   TRUE if the service exists, FALSE otherwise.
   */
  public function hasService($key);

  /**
   * Get service definition.
   *
   * @param string $key
   *   Service key.
   *
   * @return array
   *   Service definition.
   *
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceNotFoundException
   *   If requested service doesn't exist.
   */
  public function getService($key);

  /**
   * Get all defined services, optionally filtered by type and destination.
   *
   * @param string|null $type
   *   Service type, one of 'parcel' or 'letter'.
   * @param string|null $dest
   *   Service destination, one of 'domestic' or 'international'.
   *
   * @return array
   *   All requested service definitions.
   *
   * @throws ServiceNotFoundException
   *   If an invalid service type or destination was provided.
   */
  public function getServices($type = NULL, $dest = NULL);

  /**
   * Retrieves service definitions for a set of service keys.
   *
   * @param array $keys
   *   A set of service keys to return definitions for.
   * @param bool $ignoreNonExisting
   *   If TRUE, any 'not found' errors will be ignored.
   *
   * @return array
   *   Service definitions keyed by service key.
   *
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceNotFoundException
   */
  public function getServicesByKeys(array $keys, $ignoreNonExisting = FALSE);

  /**
   * Get all supported package types.
   *
   * @return array
   *   A list of all supported package types.
   */
  public function supportedPackageTypes();

  /**
   * Get all supported destinations.
   *
   * @return array
   *   A list of all supported destinations.
   */
  public function supportedDestinations();

  /**
   * Checks that a package destination is valid.
   *
   * @param string $destination
   *   Package destination (one of ServiceDefinitions::SERVICE_DEST_DOMESTIC or
   *   ServiceDefinitions::SERVICE_DEST_INTERNATIONAL).
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  public function validateDestination($destination);

  /**
   * Checks that a package type is valid.
   *
   * @param string $type
   *   Package type (one of ServiceDefinitions::SERVICE_TYPE_PARCEL or
   *   ServiceDefinitions::SERVICE_TYPE_LETTER).
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  public function validatePackageType($type);

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
