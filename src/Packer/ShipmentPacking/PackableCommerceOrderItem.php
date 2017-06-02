<?php

namespace Drupal\commerce_auspost\Packer\ShipmentPacking;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\WeightUnit;
use DVDoug\BoxPacker\Item;

/**
 * Wraps a Commerce order item, allowing it to be packed by the packer.
 *
 * @package Drupal\commerce_auspost\Packer\ShipmentPacking
 */
class PackableCommerceOrderItem implements Item {

  /**
   * Commerce order item.
   *
   * @var \Drupal\commerce_order\Entity\OrderItemInterface
   */
  private $orderItem;

  /**
   * CommerceOrderItem constructor.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $orderItem
   *   Commerce order item.
   */
  public function __construct(OrderItemInterface $orderItem) {
    $this->orderItem = $orderItem;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->orderItem->getTitle();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getWidth() {
    return $this->getDimension('width', LengthUnit::MILLIMETER);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getLength() {
    return $this->getDimension('length', LengthUnit::MILLIMETER);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getDepth() {
    return $this->getDimension('height', LengthUnit::MILLIMETER);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getWeight() {
    $purchasedEntity = $this->orderItem->getPurchasedEntity();
    $values = $purchasedEntity->get('weight');

    if (!$values->isEmpty()) {
      /** @var \Drupal\physical\Plugin\Field\FieldType\MeasurementItem $value */
      $value = $values->first();
      $measurement = $value->toMeasurement()
        ->convert(WeightUnit::GRAM);

      // Round up, the packer library only supports whole numbers.
      $number = $measurement->getNumber();
      return (int) ceil((float) $number);
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getVolume() {
    // Use bcmath to calculate volume.
    $width = $this->getDimension(
      'width',
      LengthUnit::MILLIMETER,
      FALSE
    );
    $length = $this->getDimension(
      'length',
      LengthUnit::MILLIMETER,
      FALSE
    );
    $height = $this->getDimension(
      'height',
      LengthUnit::MILLIMETER,
      FALSE
    );

    $volume = bcmul($width, bcmul($length, $height));
    return (int) ceil((float) $volume);
  }

  /**
   * {@inheritdoc}
   */
  public function getKeepFlat() {
    // Not currently supported, assume that no order item needs to be kept flat.
    return FALSE;
  }

  /**
   * Get the value of a particular dimension, optionally converting it.
   *
   * @param $name
   *   Name of the dimension (e.g. 'length');
   * @param string $convertTo
   *   The (optional) unit to convert the dimension value to.
   * @param bool $returnInt
   *   If TRUE will return an int instead of a string.
   *
   * @return int|string
   *   The dimension value.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function getDimension($name, $convertTo = NULL, $returnInt = true) {
    $purchasedEntity = $this->orderItem->getPurchasedEntity();
    $dimensionValues = $purchasedEntity->get('dimensions');

    if (!$dimensionValues->isEmpty()) {
      /** @var \Drupal\physical\Plugin\Field\FieldType\DimensionsItem $dimensionValue */
      $dimensionValue = $dimensionValues->first();
      $dimensionData = $dimensionValue->getValue();
      /** @var \Drupal\physical\Length $measurement */
      $measurement = new Length(
        $dimensionData[$name],
        $dimensionData['unit']
      );

      if ($convertTo !== NULL) {
        $measurement = $measurement->convert($convertTo);
      }

      $measurementNumber = $measurement->getNumber();

      if ($returnInt) {
        // Round up, the underlying packer only supports whole numbers.
        return (int) ceil((float) $measurementNumber);
      }

      return $measurementNumber;
    }

    if ($returnInt) {
      return 0;
    }
    return '0';
  }

}
