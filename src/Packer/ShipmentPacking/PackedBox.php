<?php

namespace Drupal\commerce_auspost\Packer\ShipmentPacking;

use BadMethodCallException;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use DVDoug\BoxPacker\PackedBox as RawPackedBox;
use ReflectionClass;

/**
 * Defines a packed box.
 *
 * @package Drupal\commerce_auspost\Packer\ShipmentPacking
 */
class PackedBox implements PackedBoxInterface {

  /**
   * Underlying packed box instance.
   *
   * @var \DVDoug\BoxPacker\PackedBox
   */
  private $box;

  /**
   * PackedBox constructor.
   *
   * @param \DVDoug\BoxPacker\PackedBox $box
   *   Underlying packed box instance.
   */
  private function __construct(RawPackedBox $box) {
    $this->box = $box;
  }

  /**
   * Pass-through any function calls to the underlying box instance.
   *
   * @param string $name
   *   Function name.
   * @param array $arguments
   *   Function arguments.
   *
   * @throws \ReflectionException
   *   If reflection could not be used with the underlying packed box class.
   * @throws \BadMethodCallException
   *   If method doesn't exist.
   */
  public function __call($name, array $arguments) {
    $reflection = new ReflectionClass($this->box);
    if ($reflection->hasMethod($name)) {
      return $this->box->{$name}(...$arguments);
    }

    throw new BadMethodCallException("Method '{$name}' does not exist.");
  }

  /**
   * {@inheritdoc}
   */
  public static function create(RawPackedBox $box) {
    return new static($box);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return new Weight($this->box->getWeight(), WeightUnit::GRAM);
  }

}
