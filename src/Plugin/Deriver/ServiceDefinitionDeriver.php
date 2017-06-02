<?php

namespace Drupal\commerce_auspost\Plugin\Deriver;

use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionDefaults;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\PluginBase;

class ServiceDefinitionDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $defaultServices = ServiceDefinitionDefaults::services();
    $basePluginId = $basePluginDefinition['id'];
    $separator = PluginBase::DERIVATIVE_SEPARATOR;

    foreach ($defaultServices as $serviceKey => $service) {
      $this->derivatives[$serviceKey] = [
        'id' => "{$basePluginId}{$separator}{$serviceKey}",
        'service_id' => $serviceKey,
        'label' => $service['description'],
        'destination' => $service['destination'],
        'service_type' => $service['type'],
        'service_code' => $service['service_code'],
        'option_code' => $service['option_code'],
        'sub_option_code' => $service['sub_option_code'],
        'extra_cover' => $service['extra_cover'],
      ] + $basePluginDefinition;
    }

    return $this->derivatives;
  }

}
