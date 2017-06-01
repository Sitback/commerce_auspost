<?php

namespace Drupal\commerce_auspost\PostageAssessment;

use Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions;
use Drupal\commerce_auspost\PostageServices\ServiceSupport;
use Drupal\physical\LengthUnit;
use Drupal\physical\WeightUnit;

/**
 * Defines a new PAC request.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class Request implements RequestInterface {

  /**
   * Packed box.
   *
   * @var \Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox
   */
  private $packedBox;

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
   * Service support helpers.
   *
   * @var \Drupal\commerce_auspost\PostageServices\ServiceSupport
   */
  private $serviceSupport;

  /**
   * Request constructor.
   *
   * @param \Drupal\commerce_auspost\PostageServices\ServiceSupport $serviceSupport
   *   Supported services.
   */
  public function __construct(ServiceSupport $serviceSupport) {
    $this->serviceSupport = $serviceSupport;
  }

  /**
   * {@inheritdoc}
   */
  public function setPackageType($packageType) {
    if (!$this->serviceSupport->validatePackageType($packageType)) {
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
  public function setPackedBox(PackedBox $box) {
    $this->packedBox = $box;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackedBox() {
    if ($this->packedBox === NULL) {
      throw new RequestException('Packed box is not set.');
    }
    return $this->packedBox;
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
    $isDomestic = $this->getAddress()->isDomestic();

    if ($isDomestic === NULL) {
      throw new RequestException('Package destination could not be determined.');
    }

    return $isDomestic;
  }

  /**
   * {@inheritdoc}
   */
  public function isParcel() {
    return $this->packageType === ServiceDefinitions::SERVICE_TYPE_PARCEL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDimensions() {
    $weight = $this->getPackedBox()
      ->getWeight()
      ->convert(WeightUnit::KILOGRAM)
      ->getNumber();

    $dimensions = [
      'length' => $this->getPackedDimension('length'),
      'width' => $this->getPackedDimension('width'),
      'height' => $this->getPackedDimension('height'),
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

  /**
   * Get a dimension value of the packed box in centimetres.
   *
   * @param string $name
   *   Dimension name.
   *
   * @return int
   *   Dimension value, rounded up where required.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  private function getPackedDimension($name) {
    $name = ucfirst($name);
    $method = "get{$name}";
    $number = $this->getPackedBox()
      ->{$method}()
      ->convert(LengthUnit::CENTIMETER)
      ->getNumber();

    return (int) ceil((float) $number);
  }

}
