<?php

namespace Drupal\commerce_auspost\PostageServices\ServiceDefinitions;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Defines service destinations.
 *
 * @package Drupal\commerce_auspost\PostageServices\ServiceDefinitions
 */
final class ServiceDestinations extends AbstractEnum {

  // Domestic services.
  const DOMESTIC = 'domestic';

  // International services.
  const INTERNATIONAL = 'international';

}
