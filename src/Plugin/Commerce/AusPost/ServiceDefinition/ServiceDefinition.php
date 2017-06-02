<?php

namespace Drupal\commerce_auspost\Plugin\Commerce\AusPost\ServiceDefinition;

use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\AbstractServiceDefinition;

/**
 * Defines a new default AusPost service, instances are derived.
 *
 * @CommerceAusPostServiceDefinition(
 *     id = "commerce_auspost_service_definition",
 *     deriver = "Drupal\commerce_auspost\Plugin\Deriver\ServiceDefinitionDeriver"
 * )
 *
 * @package Drupal\commerce_auspost\PostageServices\ServiceDefinitions
 */
class ServiceDefinition extends AbstractServiceDefinition {
}
