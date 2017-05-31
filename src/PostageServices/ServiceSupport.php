<?php

namespace Drupal\commerce_auspost\PostageServices;

use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\Volume;
use Drupal\physical\VolumeUnit;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;

/**
 * Defines some AusPost service support helpers.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class ServiceSupport {

  /**
   * Check if a service exists.
   *
   * @param string $key
   *   Service key.
   *
   * @return bool
   *   TRUE if the service exists, FALSE otherwise.
   */
  public function hasService($key) {
    return array_key_exists($key, $this->services());
  }

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
  public function getService($key) {
    if ($this->hasService($key)) {
      return ServiceDefinitions::services()[$key];
    }
    throw new ServiceNotFoundException("Requested service '{$key}' does not exist.");
  }

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
  public function getServices($type = NULL, $dest = NULL) {
    $services = ServiceDefinitions::services();

    if ($type === NULL && $dest === NULL) {
      return $services;
    }

    $filterByType = function ($type) {
      return function ($service) use ($type) {
        return $type === $service['type'];
      };
    };
    $filterByDest = function ($dest) {
      return function ($service) use ($dest) {
        return $dest === $service['destination'];
      };
    };

    // Filter by postage type if required.
    switch ($type) {
      case ServiceDefinitions::SERVICE_TYPE_PARCEL:
        $services = array_filter(
          $services,
          $filterByType(ServiceDefinitions::SERVICE_TYPE_PARCEL)
        );
        break;

      case ServiceDefinitions::SERVICE_TYPE_LETTER:
        $services = array_filter(
          $services,
          $filterByType(ServiceDefinitions::SERVICE_TYPE_LETTER)
        );
        break;

      case NULL:
        break;

      default:
        throw new ServiceNotFoundException("Unknown service type '{$type}'.");
    }

    // Filter by destination if required.
    switch ($dest) {
      case ServiceDefinitions::SERVICE_DEST_DOMESTIC:
        $services = array_filter(
          $services,
          $filterByDest(ServiceDefinitions::SERVICE_DEST_DOMESTIC)
        );
        break;

      case ServiceDefinitions::SERVICE_DEST_INTERNATIONAL:
        $services = array_filter(
          $services,
          $filterByDest(ServiceDefinitions::SERVICE_DEST_INTERNATIONAL)
        );
        break;

      case NULL:
        break;

      default:
        throw new ServiceNotFoundException(
          "Unknown service destination '{$dest}'."
        );
    }

    return $services;
  }

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
  public function getServicesByKeys(array $keys, $ignoreNonExisting = FALSE) {
    $services = [];

    foreach ($keys as $key) {
      try {
        $services[$key] = $this->getService($key);
      } catch (ServiceNotFoundException $e) {
        if ($ignoreNonExisting) {
          continue;
        }
        throw $e;
      }
    }

    return $services;
  }

  /**
   * Get all supported package types.
   *
   * @return array
   *   A list of all supported package types.
   */
  public function supportedPackageTypes() {
    return [
      ServiceDefinitions::SERVICE_TYPE_PARCEL,
      ServiceDefinitions::SERVICE_TYPE_LETTER,
    ];
  }

  /**
   * Get all supported destinations.
   *
   * @return array
   *   A list of all supported destinations.
   */
  public function supportedDestinations() {
    return [
      ServiceDefinitions::SERVICE_DEST_DOMESTIC,
      ServiceDefinitions::SERVICE_DEST_INTERNATIONAL,
    ];
  }

  /**
   * Maximum package dimensions supported by AusPost.
   *
   * @see https://auspost.com.au/parcels-mail/postage-tips-guides/size-weight-guidelines
   *
   * @param string $destination
   *   Package destination.
   *
   * @return array
   *   An array with one or more of the following keys: length, weight, volume,
   *   girth.
   *
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   *   If package destination is not valid.
   */
  public function getMaxParcelDimensions($destination) {
    switch ($destination) {
      case ServiceDefinitions::SERVICE_DEST_DOMESTIC:
        return [
          'length' => new Length('105', LengthUnit::CENTIMETER),
          'weight' => new Weight('22', WeightUnit::KILOGRAM),
          'volume' => new Volume('0.25', VolumeUnit::CUBIC_METER),
        ];

      case ServiceDefinitions::SERVICE_DEST_INTERNATIONAL:
        return [
          'length' => new Length('105', LengthUnit::CENTIMETER),
          'weight' => new Weight('20', WeightUnit::KILOGRAM),
          'girth' => new Length('140', LengthUnit::CENTIMETER),
        ];

      default:
        throw new ServiceSupportException(
          "Unknown package destination '{$destination}'."
        );
    }
  }

}
