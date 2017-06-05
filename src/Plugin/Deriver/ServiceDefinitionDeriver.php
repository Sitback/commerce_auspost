<?php

namespace Drupal\commerce_auspost\Plugin\Deriver;

use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionDefaults;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\PluginBase;

/**
 * Derives service definitions from defined defaults.
 *
 * @package Drupal\commerce_auspost\Plugin\Deriver
 */
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
      ];

      if (array_key_exists('max_dimensions', $service)) {
        $validDimension = true;
        $dimensionKeys = ['length', 'width', 'height', 'weight'];
        foreach ($dimensionKeys as $dimensionKey) {
          if (!array_key_exists($dimensionKey, $service['max_dimensions'])) {
            $validDimension = false;
            break;
          }
        }

        if ($validDimension) {
          $this->derivatives[$serviceKey]['max_dimensions'] = $service['max_dimensions'];
        }
      }

      // Add defaults.
      $this->derivatives[$serviceKey] +=  $basePluginDefinition;
    }

    return $this->derivatives;
  }

}
