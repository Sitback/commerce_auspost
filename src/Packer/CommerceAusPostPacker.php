<?php

namespace Drupal\commerce_auspost\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\commerce_shipping\Packer\DefaultPacker;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Class CommerceAusPostPacker.
 *
 * Based off \Drupal\commerce_fedex\Packer\CommerceFedExPacker.
 *
 * @package Drupal\commerce_auspost\Packer
 */
class CommerceAusPostPacker extends DefaultPacker {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \InvalidArgumentException
   */
  public function pack(OrderInterface $order, ProfileInterface $shippingProfile) {
    $shipments = [
      [
        'title' => $this->t('Primary Shipment'),
        'items' => [],
      ],
    ];

    foreach ($order->getItems() as $orderItem) {
      $purchased_entity = $orderItem->getPurchasedEntity();

      // Ship only shippable purchasable entity types.
      if (!$purchased_entity || !$purchased_entity->hasField('weight')) {
        continue;
      }

      $quantity = $orderItem->getQuantity();

      $shipments[0]['items'][] = new ShipmentItem([
        'order_item_id' => $orderItem->id(),
        'title' => $orderItem->getTitle(),
        'quantity' => $quantity,
        'weight' => $this->getWeight($orderItem)->multiply($quantity),
        'declared_value' => $orderItem->getUnitPrice()->multiply($quantity),
      ]);
    }

    $proposed_shipments = [];

    foreach ($shipments as $shipment) {
      if (!empty($shipment['items'])) {
        $proposed_shipments[] = new ProposedShipment([
          'type' => $this->getShipmentType($order),
          'order_id' => $order->id(),
          'title' => $shipment['title'],
          'items' => $shipment['items'],
          'shipping_profile' => $shippingProfile,
        ]);
      }
    }

    return $proposed_shipments;
  }

  /**
   * Gets the weight of the order item.
   *
   * The weight will be empty if the shippable trait was added but the existing
   * entities were not updated.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $orderItem
   *   The order item.
   *
   * @return \Drupal\physical\Weight
   *   The order item's weight.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function getWeight(OrderItemInterface $orderItem) {
    $purchasedEntity = $orderItem->getPurchasedEntity();

    if ($purchasedEntity->get('weight')->isEmpty()) {
      $weight = new Weight(0, WeightUnit::KILOGRAM);
    }
    else {
      /** @var \Drupal\physical\Plugin\Field\FieldType\MeasurementItem $weightItem */
      $weightItem = $purchasedEntity->get('weight')->first();
      $weight = $weightItem->toMeasurement();
    }

    return $weight;
  }

}
