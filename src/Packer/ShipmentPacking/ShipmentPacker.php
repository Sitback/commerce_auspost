<?php

namespace Drupal\commerce_auspost\Packer\ShipmentPacking;

use Drupal\commerce_auspost\PostageServices\ServiceSupport;
use Drupal\commerce_order\Entity\OrderItemInterface;
use DVDoug\BoxPacker\PackedBoxList;
use DVDoug\BoxPacker\Packer;

/**
 * Class ShipmentPacker.
 *
 * @package Drupal\commerce_auspost\Packer\ShipmentPacking
 */
class ShipmentPacker implements ShipmentPackerInterface {

  /**
   * The underlying packer.
   *
   * @var \DVDoug\BoxPacker\Packer
   */
  private $packer;

  /**
   * Service support helpers.
   *
   * @var \Drupal\commerce_auspost\PostageServices\ServiceSupport
   */
  private $serviceSupport;

  /**
   * All package types that are part of the current packing request.
   *
   * @var \Drupal\commerce_auspost\Packer\ShipmentPacking\PackableCommercePackageType[]
   */
  private $packageTypes = [];

  /**
   * All order items that are part of the current packing request.
   *
   * @var \Drupal\commerce_auspost\Packer\ShipmentPacking\PackableCommerceOrderItem[]
   */
  private $orderItems = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(Packer $packer, ServiceSupport $support) {
    $this->packer = $packer;
    $this->serviceSupport = $support;
  }

  /**
   * {@inheritdoc}
   */
  public function addPackageType(array $packageType, $destination) {
    $this->packageTypes[] = new PackableCommercePackageType(
      $this->serviceSupport,
      $packageType,
      $destination
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addPackageTypes(array $packageTypes, $destination) {
    foreach ($packageTypes as $packageType) {
      $this->addPackageType($packageType, $destination);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addOrderItem(OrderItemInterface $orderItem) {
    $this->orderItems[] = new PackableCommerceOrderItem($orderItem);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addOrderItems(array $orderItems) {
    foreach ($orderItems as $orderItem) {
      $this->addOrderItem($orderItem);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function pack() {
    foreach ($this->packageTypes as $packageType) {
      $this->packer->addBox($packageType);
    }

    foreach ($this->orderItems as $orderItem) {
      $this->packer->addItem($orderItem);
    }

    return $this->getPackedBoxes($this->packer->pack());
  }

  /**
   * Wraps a set of packed boxes in our wrapping packed box class.
   *
   * @param \DVDoug\BoxPacker\PackedBoxList $boxes
   *   Boxes to wrap.
   *
   * @codingStandardsIgnoreStart (Coder thinks Generator returns aren't valid.)
   *
   * @return \Generator
   *   A generator that yields packed box instances.
   *
   * @codingStandardsIgnoreEnd
   */
  private function getPackedBoxes(PackedBoxList $boxes) {
    foreach ($boxes as $box) {
      yield new PackedBox($box);
    }
  }

}
