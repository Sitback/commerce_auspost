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
   * @return \Drupal\physical\Weight
   *   Packed weight as a measurement.
   */
  public function getWeight();

  /**
   * Get box length.
   *
   * @return \Drupal\physical\Length
   *   Box length.
   */
  public function getLength();

  /**
   * Get box width.
   *
   * @return \Drupal\physical\Length
   *   Box width.
   */
  public function getWidth();

  /**
   * Get box height.
   *
   * @return \Drupal\physical\Length
   *   Box height (depth).
   */
  public function getHeight();

  /**
   * Get box volume.
   *
   * @return \Drupal\physical\Volume
   *   Box volume.
   */
  public function getVolume();

}
