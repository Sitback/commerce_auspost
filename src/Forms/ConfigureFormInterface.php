<?php

namespace Drupal\commerce_auspost\Forms;

use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;

/**
 * Provides an interface use to define configuration forms for shipping methods.
 *
 * @package Drupal\commerce_auspost\Forms
 */
interface ConfigureFormInterface {

  /**
   * Sets the shipping method instance for this configuration form.
   *
   * @param \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase $instance
   *   The shipping method instance.
   *
   * @return $this
   *   Current form instance.
   */
  public function setShippingInstance(ShippingMethodBase $instance);

  /**
   * Get the shipping method instance for this configuration form.
   *
   * @return \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase
   *   Shipping method instance.
   */
  public function getShippingInstance();

  /**
   * Determine if we have the minimum information to use the shipping method.
   *
   * @return bool
   *   TRUE if there is enough information, FALSE otherwise.
   */
  public function isConfigured();

}
