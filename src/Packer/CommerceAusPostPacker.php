<?php

namespace Drupal\commerce_auspost\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Packer\DefaultPacker;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Class CommerceAusPostPacker
 *
 * @package Drupal\commerce_auspost\Packer
 */
class CommerceAusPostPacker extends DefaultPacker {

  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order, ProfileInterface $shippingProfile) {
    $i = 0;
    return parent::pack($order, $shippingProfile);
  }

}
