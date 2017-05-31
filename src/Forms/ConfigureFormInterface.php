<?php

namespace Drupal\commerce_auspost\Forms;

use Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod\AusPost;
use Drupal\commerce_auspost\PostageServices\ServiceSupport;

/**
 * Provides an interface use to define configuration forms for shipping methods.
 *
 * @package Drupal\commerce_auspost\Forms
 */
interface ConfigureFormInterface {

  /**
   * Sets the shipping method instance for this configuration form.
   *
   * @param \Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod\AusPost $instance
   *   The shipping method instance.
   *
   * @return $this
   *   Current form instance.
   */
  public function setShippingInstance(AusPost $instance);

  /**
   * Get the shipping method instance for this configuration form.
   *
   * @return \Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod\AusPost
   *   Shipping method instance.
   */
  public function getShippingInstance();

  /**
   * Sets the service helpers class this configuration form.
   *
   * @param \Drupal\commerce_auspost\PostageServices\ServiceSupport $serviceSupport
   *   The service support helpers instance.
   *
   * @return $this
   *   Current form instance.
   */
  public function setServiceSupport(ServiceSupport $serviceSupport);

  /**
   * Determine if we have the minimum information to use the shipping method.
   *
   * @return bool
   *   TRUE if there is enough information, FALSE otherwise.
   */
  public function isConfigured();

}
