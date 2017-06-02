<?php

namespace Drupal\commerce_auspost\PostageServices\ServiceDefinitions;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an AusPost shipping service.
 *
 * @package Drupal\commerce_auspost\PostageServices\ServiceDefinitions
 */
interface ServiceDefinitionInterface extends
    ContainerFactoryPluginInterface,
    PluginInspectionInterface,
    DerivativeInspectionInterface {

  /**
   * Get service ID.
   *
   * @return string
   *   Service ID.
   */
  public function getServiceId();

  /**
   * Set service ID.
   *
   * @param string $id
   *   Service ID.
   *
   * @return $this
   */
  public function setServiceId($id);

  /**
   * Get service label.
   *
   * @return string
   *   Service label.
   */
  public function getLabel();

  /**
   * Set service label.
   *
   * @param string $label
   *   Service label.
   *
   * @return $this
   */
  public function setLabel($label);

  /**
   * Get destination.
   *
   * @return string
   *   destination.
   */
  public function getDestination();

  /**
   * Set destination.
   *
   * @param string $destination
   *   Destination.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If destination is invalid.
   */
  public function setDestination($destination);

  /**
   * Get service type.
   *
   * @return string
   *   Service type.
   */
  public function getServiceType();

  /**
   * Set service type.
   *
   * @param string $type
   *   Service type.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If service type is invalid.
   */
  public function setServiceType($type);

  /**
   * Get service code.
   *
   * @return string
   *   Service code.
   */
  public function getServiceCode();

  /**
   * Set service code.
   *
   * @param string $code
   *   Service code.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If service code is invalid.
   */
  public function setServiceCode($code);

  /**
   * Get option code.
   *
   * @return string
   *   option code.
   */
  public function getOptionCode();

  /**
   * Set option code.
   *
   * @param string $code
   *   Option code.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If option code is invalid.
   */
  public function setOptionCode($code);

  /**
   * Get sub-option code.
   *
   * @return string
   *   sub-option code.
   */
  public function getSubOptionCode();

  /**
   * Set sub-option code.
   *
   * @param string $code
   *   Sub-option code.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If sub-option code is invalid.
   */
  public function setSubOptionCode($code);

  /**
   * Get extra cover.
   *
   * @return int
   *   Extra cover.
   */
  public function getExtraCover();

  /**
   * Set extra cover.
   *
   * @param int $cover
   *   Extra cover.
   *
   * @return $this
   */
  public function setExtraCover($cover);

  /**
   * Get all options (i.e. option_code, sub_option_code & extra_cover) if set.
   *
   * @return string[]
   *   A list of all options where data exists with keys in snake_case.
   */
  public function getAllOptions();

}
