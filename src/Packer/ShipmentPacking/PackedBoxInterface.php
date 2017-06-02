<?php

namespace Drupal\commerce_auspost\Packer\ShipmentPacking;

/**
 * Defines a packed box.
 *
 * @package Drupal\commerce_auspost\Packer\ShipmentPacking
 */
interface PackedBoxInterface {

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
