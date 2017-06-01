<?php

namespace Drupal\commerce_auspost\PostageServices;

use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\Volume;
use Drupal\physical\VolumeUnit;

/**
 * Defines some AusPost service support helpers.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class ServiceSupport implements ServiceSupportInterface {

  /**
   * {@inheritdoc}
   */
  public function hasService($key) {
    return array_key_exists($key, ServiceDefinitions::services());
  }

  /**
   * {@inheritdoc}
   */
  public function getService($key) {
    if ($this->hasService($key)) {
      return ServiceDefinitions::services()[$key];
    }
    throw new ServiceNotFoundException("Requested service '{$key}' does not exist.");
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function supportedPackageTypes() {
    return [
      ServiceDefinitions::SERVICE_TYPE_PARCEL,
      ServiceDefinitions::SERVICE_TYPE_LETTER,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function supportedDestinations() {
    return [
      ServiceDefinitions::SERVICE_DEST_DOMESTIC,
      ServiceDefinitions::SERVICE_DEST_INTERNATIONAL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateDestination($destination) {
    return in_array($destination, $this->supportedDestinations(), true);
  }

  /**
   * {@inheritdoc}
   */
  public function validatePackageType($type) {
    return in_array($type, $this->supportedPackageTypes(), true);
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxParcelDimensions($destination) {
    $dimensions = ServiceDefinitions::maxParcelDimensions();

    if (array_key_exists($destination, $dimensions)) {
      return $dimensions[$destination];
    }

    throw new ServiceSupportException(
      "Unknown package destination '{$destination}'."
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validatePackageSize(
    Length $length,
    Length $width,
    Length $height,
    $destination
  ) {
    $isDomestic = $destination === ServiceDefinitions::SERVICE_DEST_DOMESTIC;
    /** @var \Drupal\physical\Measurement[] $max */
    $max = $this->getMaxParcelDimensions($destination);

    // Convert all units where required.
    $length = $length->convert(LengthUnit::CENTIMETER);
    $width = $width->convert(LengthUnit::CENTIMETER);
    $height = $height->convert(LengthUnit::CENTIMETER);

    // All destinations have length requirements, confirm that no edge of the
    // package exceeds this.
    if (
      $max['length']->lessThan($width) ||
      $max['length']->lessThan($length) ||
      $max['length']->lessThan($height)
    ) {
      throw new PackageSizeException(
        "Package width, length or height are greater than the AusPost maximum of '{$max['length']}'."
      );
    }

    // Domestic packages have volume requirements.
    if ($isDomestic) {
      // Volume is calculated in m^3.
      $widthNumber = $width->convert(LengthUnit::METER)
        ->getNumber();
      $heightNumber = $height->convert(LengthUnit::METER)
        ->getNumber();

      $volumeCalc = $length->convert(LengthUnit::METER)
        ->multiply($widthNumber)
        ->multiply($heightNumber);
      $volume = new Volume($volumeCalc->getNumber(), VolumeUnit::CUBIC_METER);

      if ($max['volume']->lessThan($volume)) {
        throw new PackageSizeException(
          "Package volume ({$volume}) is greater than the AusPost maximum of '{$max['volume']}'."
        );
      }
    } else {
      // International packages have girth requirements,
      // (where girth = ((width + height) * 2)).
      // Each package edge combination needs to be checked.
      $girthVariations = [
        ['width', 'height'],
        ['width', 'length'],
        ['length', 'height'],
      ];
      foreach ($girthVariations as list($fieldA, $fieldB)) {
        /** @var \Drupal\physical\Length $fieldAVal */
        $fieldAVal = ${$fieldA};
        /** @var \Drupal\physical\Length $fieldBVal */
        $fieldBVal = ${$fieldB};

        $girth = $fieldAVal->add($fieldBVal)
          ->multiply('2');

        if ($max['girth']->lessThan($girth)) {
          throw new PackageSizeException(
            "Package girth ({$girth}, calculated using '{$fieldA}' and '{$fieldB}') is greater than the AusPost maximum of '{$max['girth']}'."
          );
        }
      }
    }

    return TRUE;
  }

}
