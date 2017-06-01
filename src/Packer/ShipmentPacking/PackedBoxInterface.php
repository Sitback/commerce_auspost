<?php

namespace Drupal\commerce_auspost\Packer\ShipmentPacking;

use DVDoug\BoxPacker\PackedBox;

interface PackedBoxInterface {

  /**
   * Creates a new packed box instance.
   *
   * @param \DVDoug\BoxPacker\PackedBox $box
   *   Raw backed box instance.
   *
   * @return \Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBoxInterface
   */
  public static function create(PackedBox $box);

  /**
   * Get packed weight.
   *
   * @return \Drupal\physical\WeightUnit
   *   Packed weight as a measurement.
   */
  public function getWeight();

}
