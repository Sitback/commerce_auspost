<?php

namespace Drupal\commerce_auspost\PostageAssessment;

use Drupal\commerce_auspost\Address;
use Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox;
use Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod\AusPost;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceTypes;
use Drupal\commerce_auspost\PostageServices\ServiceSupport;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\physical\LengthUnit;
use Drupal\physical\WeightUnit;
use InvalidArgumentException;

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
   * @var \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface
   */
  private $serviceDefinition;

  /**
   * Service support helpers.
   *
   * @var \Drupal\commerce_auspost\PostageServices\ServiceSupport
   */
  private $serviceSupport;

  /**
   * Insurance status.
   *
   * @var bool
   */
  private $insuranceEnabled;

  /**
   * Insurance as a percentage of order total, represented as a decimal.
   *
   * @var float
   */
  private $insurancePercentage;

  /**
   * Whether AusPost max extra cover limits should be checked.
   *
   * @var bool
   */
  private $insuranceLimit;

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
    try {
      ServiceTypes::assertExists($packageType);
    }
    catch (InvalidArgumentException $e) {
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
  public function setAddress(Address $address) {
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
  public function setShipment(ShipmentInterface $shipment) {
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
  public function setServiceDefinition(ServiceDefinitionInterface $definition) {
    $this->serviceDefinition = $definition;
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
    return $this->packageType === ServiceTypes::PARCEL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDimensions() {
    $weight = $this->getPackedBox()
      ->getWeight()
      ->convert(WeightUnit::KILOGRAM);

    // Get shipping weight, which is either the actual weight or the cubic
    // weight of the parcel, whichever is greater.
    /** @var \Drupal\physical\Weight $shippingWeight */
    $shippingWeight = $this->serviceSupport->calculateParcelWeight(
      $this->getPackedBox()->getVolume(),
      $weight
    );

    $dimensions = [
      'length' => $this->getPackedDimension('length'),
      'width' => $this->getPackedDimension('width'),
      'height' => $this->getPackedDimension('height'),
      'weight' => $shippingWeight->getNumber(),
    ];

    return $dimensions;
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceCode() {
    return $this->getServiceDefinition()->getServiceCode();
  }

  /**
   * {@inheritdoc}
   */
  public function getExtraServiceOptions() {
    return $this->getServiceDefinition()->getAllOptions();
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

  /**
   * {@inheritdoc}
   */
  public function setInsuranceOptions($enabled, $percentage, $limit = TRUE) {
    $this->insuranceEnabled = (bool) $enabled;
    $this->insurancePercentage = (float) $percentage;
    $this->insuranceLimit = $limit;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getInsuranceOptions() {
    $requiredProps = [
      'insuranceEnabled',
      'insurancePercentage',
      'insuranceLimit',
    ];

    foreach ($requiredProps as $prop) {
      if ($this->{$prop} === NULL) {
        throw new RequestException("Required property '{$prop}' is not set.");
      }
    }

    return [
      'enabled' => $this->insuranceEnabled,
      'percentage' => $this->insurancePercentage,
      'limit' => $this->insuranceLimit,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getInsuranceAmount() {
    $maxExtraCover = $this->getServiceDefinition()->getExtraCover();
    // Disable insurance if service doesn't support extra cover.
    if ($maxExtraCover === NULL || $maxExtraCover === 0) {
      return 0;
    }

    $insuranceOpts = $this->getInsuranceOptions();
    if (!$insuranceOpts['enabled']) {
      return 0;
    }

    // We only deal in AUD.
    $orderAmount = $this->getShipment()
      ->getOrder()
      ->getTotalPrice()
      ->convert(AusPost::AUD_CURRENCY_CODE);

    $insuranceAmount = $orderAmount->multiply(
      (string) $insuranceOpts['percentage']
    );

    if ($insuranceOpts['limit']) {
      // Check if amount breaches limit and return limit if so.
      $max = new Price($maxExtraCover, AusPost::AUD_CURRENCY_CODE);
      if ($insuranceAmount->greaterThan($max)) {
        $insuranceAmount = $max;
      }
    }

    return (int) ceil((float) $insuranceAmount->getNumber());
  }

}
