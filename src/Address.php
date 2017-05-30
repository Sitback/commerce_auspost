<?php

namespace Drupal\commerce_auspost;

use Drupal\address\Plugin\Field\FieldType\AddressItem;
use Drupal\commerce_shipping\Entity\ShipmentInterface;

/**
 * Class Address.
 *
 * @package Drupal\commerce_auspost
 */
class Address {

  /**
   * Order shipment.
   *
   * @var \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  private $shipment;

  /**
   * Address constructor.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The order shipment.
   */
  public function __construct(ShipmentInterface $shipment) {
    $this->shipment = $shipment;
  }

  /**
   * @return \Drupal\address\AddressInterface|\Drupal\Core\TypedData\TypedDataInterface
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getAddress() {
    $address = $this->shipment->getShippingProfile()->get('address');
    if ($address->isEmpty()) {
      return NULL;
    }

    return $address->first();
  }

  /**
   * Check if address is empty.
   *
   * @return bool
   *   TRUE if address is empty, FALSE otherwise.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isEmpty() {
    $address = $this->getAddress();
    return $address === NULL;
  }

  /**
   * Checks if the order address is domestic.
   *
   * @return bool
   *   TRUE if the address is domestic, FALSE otherwise.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isDomestic() {
    $address = $this->getAddress();

    if ($address instanceof AddressItem) {
      $storeCountryCode = $this->shipment->getOrder()
        ->getStore()
        ->getAddress()
        ->getCountryCode();

      $addressCountryCode = $address->getCountryCode();

      return $storeCountryCode === $addressCountryCode;
    }

    return NULL;
  }

  /**
   * Get the order recipient's post code.
   *
   * @return null|string
   *   The post code or NULL if the order address was empty.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getRecipientPostcode() {
    if ($this->isEmpty()) {
      return NULL;
    }
    return (int) $this->getAddress()->getPostalCode();
  }

  /**
   * Get the shipper's post code.
   *
   * @return string
   *   Shipper post code.
   */
  public function getShipperPostcode() {
    return (int) $this->shipment->getOrder()
      ->getStore()
      ->getAddress()
      ->getPostalCode();
  }

  /**
   * Get the order recipient's country code.
   *
   * @return null|string
   *   The country code or NULL if the order address was empty.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getRecipientCountrycode() {
    if ($this->isEmpty()) {
      return NULL;
    }
    return $this->getAddress()->getCountryCode();
  }

}
