<?php

namespace Drupal\commerce_auspost\Event;

use Drupal\commerce_order\Entity\OrderItemInterface;
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
   * Constructs a new BeforePackEvent.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface[] $orderItems
   *   The items to be packed.
   */
  public function __construct($orderItems) {
    $this->orderItems = $orderItems;
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
