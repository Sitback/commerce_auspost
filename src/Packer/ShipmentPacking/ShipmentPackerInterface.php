<?php

namespace Drupal\commerce_auspost\Packer\ShipmentPacking;

use Drupal\commerce_auspost\PostageServices\ServiceSupport;
use Drupal\commerce_order\Entity\OrderItemInterface;
use DVDoug\BoxPacker\Packer;

/**
 * Defines an interface to wrap \DVDoug\BoxPacker\Packer.
 *
 * @package Drupal\commerce_auspost\Packer\ShipmentPacking
 */
interface ShipmentPackerInterface {

  /**
   * ShipmentPacker constructor.
   *
   * @param \DVDoug\BoxPacker\Packer $packer
   *   The packer.
   * @param \Drupal\commerce_auspost\PostageServices\ServiceSupport $support
   *   Service support helpers.
   */
  public function __construct(Packer $packer, ServiceSupport $support);

  /**
   * Adds a package type to the packing list.
   *
   * @param array $packageType
   *   Package type.
   * @param string $destination
   *   Package destination.
   *
   * @return $this
   *
   * @throws \Drupal\commerce_auspost\Packer\ShipmentPacking\ShipmentPackerException
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   */
  public function addPackageType(array $packageType, $destination);

  /**
   * Adds multiple package types to the packing list.
   *
   * @param array[] $packageTypes
   *   List of package types.
   * @param string $destination
   *   Destination for all package types.
   *
   * @return $this
   *
   * @throws \Drupal\commerce_auspost\Packer\ShipmentPacking\ShipmentPackerException
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   */
  public function addPackageTypes(array $packageTypes, $destination);

  /**
   * Adds an order item to the packing list.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $orderItem
   *   Order item to add.
   *
   * @return $this
   */
  public function addOrderItem(OrderItemInterface $orderItem);

  /**
   * Adds multiple order items to the packing list.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface[] $orderItems
   *   Order items to add.
   *
   * @return $this
   */
  public function addOrderItems(array $orderItems);

  /**
   * Packs all items and returns the packed boxes.
   *
   * @return \Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox[]
   *   Packed boxes.
   */
  public function pack();

}
