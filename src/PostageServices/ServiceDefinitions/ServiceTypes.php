<?php

namespace Drupal\commerce_auspost\PostageServices\ServiceDefinitions;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Defines service types.
 *
 * @package Drupal\commerce_auspost\PostageServices\ServiceDefinitions
 */
final class ServiceTypes extends AbstractEnum {

  // Parcel services.
  const PARCEL = 'parcel';

  // Letter services.
  const LETTER = 'letter';

}
