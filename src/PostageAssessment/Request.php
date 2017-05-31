<?php

namespace Drupal\commerce_auspost\PostageAssessment;

use Drupal\physical\LengthUnit;
use Drupal\physical\WeightUnit;

/**
 * Defines a new PAC request.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class Request implements RequestInterface {

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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function getPackageType() {
    if ($this->packageType === NULL) {
      throw new RequestException('Package type is not set.');
    }
    return $this->packageType;
  }

  /**
   * {@inheritdoc}
   */
  public function setAddress($address) {
    $this->address = $address;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAddress() {
    if ($this->address === NULL) {
      throw new RequestException('Address is not set.');
    }
    return $this->address;
  }

  /**
   * {@inheritdoc}
   */
  public function setShipment($shipment) {
    $this->shipment = $shipment;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShipment() {
    if ($this->shipment === NULL) {
      throw new RequestException('Shipment is not set.');
    }
    return $this->shipment;
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceDefinition(array $serviceDefinition) {
    $this->serviceDefinition = $serviceDefinition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceDefinition() {
    if ($this->serviceDefinition === NULL) {
      throw new RequestException('Service definition is not set.');
    }
    return $this->serviceDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function isDomestic() {
    $isDomestic = $this->address->isDomestic();

    if ($isDomestic === NULL) {
      throw new RequestException('Package destination could not be determined.');
    }

    return $isDomestic;
  }

  /**
   * {@inheritdoc}
   */
  public function isParcel() {
    return $this->packageType === SupportedServices::SERVICE_TYPE_PARCEL;
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function getServiceCode() {
    return $this->getServiceDefinition()['service_code'];
  }

  /**
   * {@inheritdoc}
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
