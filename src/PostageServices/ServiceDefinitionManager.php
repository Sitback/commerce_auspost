<?php

namespace Drupal\commerce_auspost\PostageServices;

use Drupal\commerce_auspost\Annotation\CommerceAusPostServiceDefinition;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\AbstractServiceDefinition;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class ServiceDefinitionManager.
 *
 * @package Drupal\commerce_auspost\PostageServices
 */
class ServiceDefinitionManager extends DefaultPluginManager {

  /**
   * Manager constructor.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend to use to cache plugin definitions.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cacheBackend,
    ModuleHandlerInterface $moduleHandler
  ) {
    parent::__construct(
      'Plugin/Commerce/AusPost/ServiceDefinition',
      $namespaces,
      $moduleHandler,
      AbstractServiceDefinition::class,
      CommerceAusPostServiceDefinition::class
    );

    $this->alterInfo('commerce_auspost_service_definition_info');
    $this->setCacheBackend(
      $cacheBackend,
      'commerce_auspost_service_definition'
    );
  }

}
