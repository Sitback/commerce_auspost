<?php

namespace Drupal\commerce_auspost\Event;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\ShippingRate;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines the commerce auspost before pack event.
 *
 * @see \Drupal\commerce_auspost\Event\CommerceAuspostEvents
 */
class BeforePackEvent extends Event {

  /**
   * The order items.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface[]
   */
  protected $orderItems;

  /**
   * The shipping postage for this shipment.
   *
   * @var Price
   */
  protected $postage;

  /**
   * Constructs a new BeforePackEvent.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   * @param Price $postage
   *   Postage prior to packing.
   */
  public function __construct(ShipmentInterface $shipment, Price $postage) {
    $this->orderItems = $shipment->getOrder()->getItems();
    $this->postage = $postage;
  }

  /**
   * Get postage.
   *
   * @return Price
   *    The shipping postage.
   */
  public function getPostage() {
    return $this->postage;
  }

  /**
   * Set postage.
   *
   * @param Price $postage
   *    The shipping postage.
   */
  public function setPostage($postage) {
    $this->postage = $postage;
  }

  /**
   * Gets the order items.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface[] $orderItems
   *   The items to be packed.
   */
  public function setOrderItems($orderItems) {
    $this->orderItems = $orderItems;
  }

  /**
   * Gets the order items.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface[] $orderItems
   *   The items to be packed.
   */
  public function getOrderItems() {
    return $this->orderItems;
  }

}
