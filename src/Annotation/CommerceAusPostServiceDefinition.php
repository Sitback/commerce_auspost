<?php

namespace Drupal\commerce_auspost\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the AusPost service definition plugin annotation object.
 *
 * Plugin namespace: Plugin\Commerce\AusPost\ServiceDefintion.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceAusPostServiceDefinition extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The service ID.
   *
   * @var string
   */
  public $serviceId;

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $label;

  /**
   * The destination that this service supports (e.g. 'domestic').
   *
   * @var string
   */
  public $destination;

  /**
   * The type of service this is (e.g. 'parcel').
   *
   * @var string
   */
  public $serviceType;

  /**
   * The AusPost service code.
   *
   * @var string
   *
   * @see \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceCodes
   */
  public $serviceCode;

  /**
   * The (optional) AusPost option code.
   *
   * @var string
   *
   * @see \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceOptions
   */
  public $optionCode;

  /**
   * The secondary (optional) AusPost option code.
   *
   * @var string
   *
   * @see \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceOptions
   */
  public $subOptionCode;

  /**
   * The (optional) maximum extra cover amount (in whole AUD).
   *
   * @var int
   *
   * @see \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceOptions
   */
  public $extraCover = 0;

  /**
   * (Optional) max dimensions for this service in millimetres and grams.
   *
   * @var array
   *   An array with the keys: length, width, height and weight.
   */
  public $maxDimensions;

}
