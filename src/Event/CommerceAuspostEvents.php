<?php

namespace Drupal\commerce_auspost\Event;

use Auspost\Common\Enum;

/**
 * Class CommerceAuspostEvents
 *
 * @package Drupal\commerce_auspost\Event
 */
final class CommerceAuspostEvents extends Enum {

  /**
   * Name of the event fired before packing items.
   *
   * This event allows modules to modify the order items before the packing
   * operation. The event listener method receives
   * \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *
   * @Event
   *
   * @see \Drupal\commerce_auspost\Event\BeforePackEvent
   *
   * @var string
   */
  const BEFORE_PACK = 'commerce_auspost.before_pack';

}
