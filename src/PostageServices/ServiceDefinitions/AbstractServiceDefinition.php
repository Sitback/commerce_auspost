<?php

namespace Drupal\commerce_auspost\PostageServices\ServiceDefinitions;

use Drupal\Core\Plugin\PluginBase;
use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base for all service definitions.
 *
 * @package Drupal\commerce_auspost\PostageServices\ServiceDefinitions
 */
abstract class AbstractServiceDefinition extends PluginBase implements
    ServiceDefinitionInterface {

  /**
   * Service ID.
   *
   * @var string
   */
  protected $serviceId;

  /**
   * Service label.
   *
   * @var string
   */
  protected $label;

  /**
   * Service destination.
   *
   * @var string
   */
  protected $destination;

  /**
   * Service type.
   *
   * @var string
   */
  protected $serviceType;

  /**
   * Service code.
   *
   * @var string
   */
  protected $serviceCode;

  /**
   * Service option code.
   *
   * @var string
   */
  protected $optionCode;

  /**
   * Service sub-option code.
   *
   * @var string
   */
  protected $subOptionCode;

  /**
   * Extra cover amount (if any).
   *
   * @var int
   */
  protected $extraCover = 0;

  /**
   * Max dimensions for this service (if any).
   *
   * @var array
   */
  protected $maxDimensions = [];

  /**
   * AbstractServiceDefinition constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   *
   * @throws \InvalidArgumentException
   *   If configuration is invalid.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this
      ->setLabel($configuration['label'])
      ->setServiceId($configuration['service_id'])
      ->setDestination($configuration['destination'])
      ->setServiceType($configuration['service_type'])
      ->setServiceCode($configuration['service_code']);

    $optionalVars = [
      'option_code' => 'setOptionCode',
      'sub_option_code' => 'setSubOptionCode',
      'extra_cover' => 'setExtraCover',
      'max_dimensions' => 'setMaxDimensions',
    ];
    foreach ($optionalVars as $var => $method) {
      if (!empty($configuration[$var])) {
        $this->{$method}($configuration[$var]);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   *   If configuration is invalid.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceId() {
    return $this->serviceId;
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceId($id) {
    $this->serviceId = $id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDestination() {
    return $this->destination;
  }

  /**
   * {@inheritdoc}
   */
  public function setDestination($destination) {
    ServiceDestinations::assertExists($destination);
    $this->destination = $destination;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceType() {
    return $this->serviceType;
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceType($serviceType) {
    ServiceTypes::assertExists($serviceType);
    $this->serviceType = $serviceType;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceCode() {
    return $this->serviceCode;
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceCode($serviceCode) {
    ServiceCodes::assertExists($serviceCode);
    $this->serviceCode = $serviceCode;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionCode() {
    return $this->optionCode;
  }

  /**
   * {@inheritdoc}
   */
  public function setOptionCode($optionCode) {
    ServiceOptions::assertExists($optionCode);
    $this->optionCode = $optionCode;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubOptionCode() {
    return $this->subOptionCode;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubOptionCode($subOptionCode) {
    ServiceOptions::assertExists($subOptionCode);
    $this->subOptionCode = $subOptionCode;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtraCover() {
    return $this->extraCover;
  }

  /**
   * {@inheritdoc}
   */
  public function setExtraCover($extraCover) {
    $this->extraCover = $extraCover;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxDimensions() {
    // static $dimensions;
    // if ($dimensions !== NULL) {
    //   return $dimensions;
    // }

    // Reformat dimensions into an array of measurements.
    $dimensions = $this->maxDimensions;
    if (!empty($dimensions)) {
      // Weight is a special case.
      $dimensions['weight'] = new Weight(
        $dimensions['weight'],
        WeightUnit::GRAM
      );

      $lengthProps = ['height', 'width', 'length'];
      foreach ($lengthProps as $prop) {
        $dimensions[$prop] = new Length(
          $dimensions[$prop],
          LengthUnit::MILLIMETER
        );
      }
    }

    return $dimensions;
  }

  /**
   * {@inheritdoc}
   */
  public function setMaxDimensions(array $dimensions) {
    $requiredKeys = ['height', 'width', 'length', 'weight'];
    foreach ($requiredKeys as $key) {
      if (!array_key_exists($key, $dimensions)) {
        throw new \InvalidArgumentException(
          "Required dimension key '{$key}' not provided."
        );
      }
    }

    $this->maxDimensions = $dimensions;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllOptions() {
    $options = [
      'option_code' => $this->getOptionCode(),
      'sub_opt_code' => $this->getSubOptionCode(),
    ];

    // Remove empty values.
    return array_filter($options);
  }

}
