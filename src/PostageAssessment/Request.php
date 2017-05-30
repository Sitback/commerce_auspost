<?php

namespace Drupal\commerce_auspost\PostageAssessment;

use Drupal\physical\LengthUnit;
use Drupal\physical\WeightUnit;

/**
 * Defines a new PAC request.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class Request {

  /**
   * Package type, one of 'parcel' or 'letter'.
   *
   * @var string
   */
  private $packageType;

  /**
   * Order address.
   *
   * @var \Drupal\commerce_auspost\Address
   */
  private $address;

  /**
   * Order shipment.
   *
   * @var \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  private $shipment;

  /**
   * The shipping service definition.
   *
   * @TODO: turn this into an object.
   *
   * @var array
   */
  private $serviceDefinition;

  /**
   * Set package type.
   *
   * @param string $packageType
   *   Package type, one of 'parcel' or 'letter'.
   *
   * @return $this
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function setPackageType($packageType) {
    $allowedTypes = [
      SupportedServices::SERVICE_TYPE_PARCEL,
      SupportedServices::SERVICE_TYPE_LETTER,
    ];
    if (!in_array($packageType, $allowedTypes, true)) {
      throw new RequestException("Unknown package type '{$packageType}'.");
    }

    $this->packageType = $packageType;
    return $this;
  }

  /**
   * Get package type.
   *
   * @return string
   *   Package type, one of 'parcel' or 'letter'.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getPackageType() {
    if ($this->packageType === NULL) {
      throw new RequestException('Package type is not set.');
    }
    return $this->packageType;
  }

  /**
   * Set the order address.
   *
   * @param \Drupal\commerce_auspost\Address $address
   *   Order address.
   *
   * @return $this
   */
  public function setAddress($address) {
    $this->address = $address;
    return $this;
  }

  /**
   * Get order address.
   *
   * @return \Drupal\commerce_auspost\Address
   *   Order address.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getAddress() {
    if ($this->address === NULL) {
      throw new RequestException('Address is not set.');
    }
    return $this->address;
  }

  /**
   * Set the order shipment.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   Order shipment.
   *
   * @return $this
   */
  public function setShipment($shipment) {
    $this->shipment = $shipment;
    return $this;
  }

  /**
   * Get order shipment.
   *
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface Order shipment.
   *   Order shipment.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getShipment() {
    if ($this->shipment === NULL) {
      throw new RequestException('Shipment is not set.');
    }
    return $this->shipment;
  }

  /**
   * Set service definition.
   *
   * @param array $serviceDefinition
   *   Service definition.
   *
   * @return $this
   */
  public function setServiceDefinition(array $serviceDefinition) {
    $this->serviceDefinition = $serviceDefinition;
    return $this;
  }

  /**
   * Get service definition.
   *
   * @return array
   *   Service definition.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getServiceDefinition() {
    if ($this->serviceDefinition === NULL) {
      throw new RequestException('Service definition is not set.');
    }
    return $this->serviceDefinition;
  }

  /**
   * Checks if this request is a domestic PAC request.
   *
   * @return bool
   *   TRUE if domestic, FALSE otherwise.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function isDomestic() {
    $isDomestic = $this->address->isDomestic();

    if ($isDomestic === NULL) {
      throw new RequestException('Package destination could not be determined.');
    }

    return $isDomestic;
  }

  /**
   * Check if the request is for a parcel or a letter.
   *
   * @return bool
   *   TRUE if it is a parcel, FALSE if it is a letter.
   */
  public function isParcel() {
    return $this->packageType === SupportedServices::SERVICE_TYPE_PARCEL;
  }

  /**
   * Get order shipment dimensions.
   *
   * @return array
   *   An array with keys for 'length', 'width', 'height' and 'weight'.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getDimensions() {
    $package = $this->getShipment()->getPackageType();

    // Round length, width & height up as AusPost expects whole numbers.
    $length = (int) ceil(
      (float) $package->getLength()
      ->convert(LengthUnit::CENTIMETER)
      ->getNumber()
    );
    $width = (int) ceil(
      (float) $package->getWidth()
      ->convert(LengthUnit::CENTIMETER)
      ->getNumber()
    );
    $height = (int) ceil(
      (float) $package->getHeight()
      ->convert(LengthUnit::CENTIMETER)
      ->getNumber()
    );
    $weight = (float) $package->getWeight()
      ->convert(WeightUnit::KILOGRAM)
      ->getNumber();

    $dimensions = [
      'length' => $length,
      'width' => $width,
      'height' => $height,
      'weight' => $weight,
    ];

    return $dimensions;
  }

  /**
   * Get the AusPost service code.
   *
   * @return string
   *   Service code.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getServiceCode() {
    return $this->getServiceDefinition()['service_code'];
  }

  /**
   * Any further service options that are sent in the API call.
   *
   * @return array
   *   An array of extra service options.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getExtraServiceOptions() {
    $extraOpts = [];

    // Optional properties that may be sent with the request, depending on the
    // service in use.
    $optKeys = [
      'option_code',
      'sub_opt_code',
      'extra_cover',
    ];

    foreach ($optKeys as $opt) {
      if (!empty($this->getServiceDefinition()[$opt])) {
        $extraOpts[$opt] = $this->getServiceDefinition()[$opt];
      }
    }

    return $extraOpts;
  }

}
