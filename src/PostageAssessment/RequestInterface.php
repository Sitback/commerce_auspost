<?php

namespace Drupal\commerce_auspost\PostageAssessment;

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
   * Set the order address.
   *
   * @param \Drupal\commerce_auspost\Address $address
   *   Order address.
   *
   * @return $this
   */
  public function setAddress($address);

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
  public function setShipment($shipment);

  /**
   * Get order shipment.
   *
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface Order shipment.
   *   Order shipment.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getShipment();

  /**
   * Set service definition.
   *
   * @param array $serviceDefinition
   *   Service definition.
   *
   * @return $this
   */
  public function setServiceDefinition(array $serviceDefinition);

  /**
   * Get service definition.
   *
   * @return array
   *   Service definition.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function getServiceDefinition();

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

}
