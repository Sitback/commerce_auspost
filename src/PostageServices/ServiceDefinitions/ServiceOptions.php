<?php

namespace Drupal\commerce_auspost\PostageServices\ServiceDefinitions;

use CommerceGuys\Enum\AbstractEnum;

/**
 * Defines AusPost service options.
 *
 * @package Drupal\commerce_auspost\PostageServices\ServiceDefinitions
 */
final class ServiceOptions extends AbstractEnum {

  // Domestic standard service.
  const AUS_SERVICE_OPTION_STANDARD = 'AUS_SERVICE_OPTION_STANDARD';

  // Domestic extra cover.
  const AUS_SERVICE_OPTION_EXTRA_COVER = 'AUS_SERVICE_OPTION_EXTRA_COVER';

  // Domestic signature on delivery.
  const AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY = 'AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY';

  // Domestic delivery confirmation
  const AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION = 'AUS_SERVICE_OPTION_DELIVERY_CONFIRMATION';

  // Domestic registered post.
  const AUS_SERVICE_OPTION_REGISTERED_POST = 'AUS_SERVICE_OPTION_REGISTERED_POST';

  // Domestic (registered) person to person.
  const AUS_SERVICE_OPTION_PERSON_TO_PERSON = 'AUS_SERVICE_OPTION_PERSON_TO_PERSON';

  // Domestic cash-on-delivery for postage fees.
  const AUS_SERVICE_OPTION_COD_POSTAGE_FEES = 'AUS_SERVICE_OPTION_COD_POSTAGE_FEES';

  // Domestic cash-on-delivery with payment for the item.
  const AUS_SERVICE_OPTION_COD_MONEY_COLLECTION = 'AUS_SERVICE_OPTION_COD_MONEY_COLLECTION';

  // const AUS_SERVICE_OPTION_COURIER_EXTRA_COVER_SERVICE = 'AUS_SERVICE_OPTION_COURIER_EXTRA_COVER_SERVICE';


  // International tracking.
  const INT_TRACKING = 'INT_TRACKING';

  // International extra cover.
  const INT_EXTRA_COVER = 'INT_EXTRA_COVER';

  // International SMS tracking advice.
  const INT_SMS_TRACK_ADVICE = 'INT_SMS_TRACK_ADVICE';

  // International signature on delivery.
  const INT_SIGNATURE_ON_DELIVERY = 'INT_SIGNATURE_ON_DELIVERY';

}
