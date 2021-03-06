<?php

namespace Drupal\commerce_auspost\PostageAssessment;

use Drupal\commerce_auspost\Address;
use Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface;
use Drupal\commerce_shipping\Entity\ShipmentInterface;

/**
 * Defines an interface to create a new PAC request.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
interface RequestInterface {

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
  public function setPackageType($packageType);

  /**
   * Get package type.
   *
   * @return string
   *   Package type, one of 'parcel' or 'letter'.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getPackageType();

  /**
   * Set packed box.
   *
   * @param \Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox $box
   *   Packed box.
   *
   * @return $this
   */
  public function setPackedBox(PackedBox $box);

  /**
   * Get packed box.
   *
   * @return \Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox
   *   Packed box.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getPackedBox();

  /**
   * Set the order address.
   *
   * @param \Drupal\commerce_auspost\Address $address
   *   Order address.
   *
   * @return $this
   */
  public function setAddress(Address $address);

  /**
   * Get order address.
   *
   * @return \Drupal\commerce_auspost\Address
   *   Order address.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getAddress();

  /**
   * Set the order shipment.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   Order shipment.
   *
   * @return $this
   */
  public function setShipment(ShipmentInterface $shipment);

  /**
   * Get order shipment.
   *
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface
   *   Order shipment.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getShipment();

  /**
   * Set service definition.
   *
   * @param \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface $definition
   *   Service definition.
   *
   * @return $this
   */
  public function setServiceDefinition(ServiceDefinitionInterface $definition);

  /**
   * Get service definition.
   *
   * @return \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface
   *   Service definition.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getServiceDefinition();

  /**
   * Set service definition.
   *
   * @param bool $enabled
   *   TRUE if insurance is enabled, FALSE otherwise.
   * @param float $percentage
   *   Insurance percentage of order total, as a decimal.
   * @param bool $limit
   *   If TRUE and the insurance amount is greater than the AusPost maximum, the
   *   maximum will be used. If FALSE, no checks will be made.
   *
   * @return $this
   */
  public function setInsuranceOptions($enabled, $percentage, $limit = TRUE);

  /**
   * Get insurance options.
   *
   * @return array
   *   An array with the keys: enabled, percentage and limit.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getInsuranceOptions();

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
  public function isDomestic();

  /**
   * Check if the request is for a parcel or a letter.
   *
   * @return bool
   *   TRUE if it is a parcel, FALSE if it is a letter.
   */
  public function isParcel();

  /**
   * Get order shipment dimensions.
   *
   * @return array
   *   An array with keys for 'length', 'width', 'height' and 'weight'.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getDimensions();

  /**
   * Get the AusPost service code.
   *
   * @return string
   *   Service code.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getServiceCode();

  /**
   * Any further service options that are sent in the API call.
   *
   * @return array
   *   An array of extra service options.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getExtraServiceOptions();

  /**
   * Get calculated insurance amount.
   *
   * @return integer
   *   Insurance amount rounded up to the closest integer.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getInsuranceAmount();

}
