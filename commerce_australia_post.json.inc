<?php

/**
 * @file
 * Handles JSON-related stuff for Commerce Australia Post module.
 */

/*
 * This builds the URL to submit to Australia Post for rates.
 */
function commerce_australia_post_build_rate_request($order) {
  $order_wrapper = entity_metadata_wrapper('commerce_order', $order);

  // Determine the shipping profile reference field name for the order.
  $field_name = commerce_physical_order_shipping_field_name($order);
  $shipping_profile = $order_wrapper->{$field_name}->value();

  // Prepare the shipping address for use in the request.
  if (!empty($order_wrapper->{$field_name}->commerce_customer_address)) {
    $shipping_address = $order_wrapper->{$field_name}->commerce_customer_address->value();
  }
  else {
    $shipping_address = addressfield_default_values();
  }

/******  Start of alternative to pack into smaller boxes *****
  // Create an array of packages
  // $packages = array(
  //    'items' => number of items,
  //    'length' => length 

  // Loop over each line item on the order.
  foreach ($order_wrapper->commerce_line_items as $delta => $line_item_wrapper) {
    // Get the weight value of product line items.
    if (in_array($line_item_wrapper->type->value(), commerce_product_line_item_types())) {
      $line_item_weight = commerce_physical_product_line_item_weight($line_item_wrapper->value());
      $line_item_dimensions = commerce_physical_product_line_item_dimensions($line_item_wrapper->value());

      // Add it to the running total converting it to the required weight unit.
      if (!empty($line_item_weight['weight'])) {
        $converted_weight = physical_weight_convert($line_item_weight, 'cm');

        if (empty($weight['weight'])) {
          $weight = $converted_weight;
        }
        else {
          $weight['weight'] += $converted_weight['weight'];
        }
      }

      if (!physical_field_is_empty($line_item_dimensions, array('type' => 'physical_dimensions'))) {
        $converted_dimensions = physical_dimensions_convert($line_item_dimensions, 'cm');
        $converted_dimensions['volume'] = $converted_dimensions['width'] * $converted_dimensions['length'] * $converted_dimensions['height'] * $line_item_wrapper->quantity->value();

        if (empty($volume['volume'])) {
          // Create a volume value array using the converted unit type.
          $volume = array(
            'volume' => $converted_dimensions['volume'], 
            'unit' => 'cm',
          );
        }
        else {
          $volume['volume'] += $converted_dimensions['volume'];
        }
      }

    }
  }
****/

//FIXME: The following assumes everything is the same density and does not optimally pack

  $weight = commerce_physical_order_weight($order, 'kg');  // this returns $weight['unit'] and $weight['weight']
  $volume = commerce_physical_order_volume($order, 'cm');  // this returns $volume['unit'] and $volume['volume']

  $default_package_volume = variable_get('commerce_australia_post_default_package_size_length', '0') * variable_get('commerce_australia_post_default_package_size_width', '0') * variable_get('commerce_australia_post_default_package_size_height', '0');

  /* If there is no default package volume, we cannot calculate the number of packages and there is no reason to send to Australia Post */
  if ($default_package_volume == 0) {
    drupal_set_message(t('There was an error with the Australia Post configuration.'), 'error', FALSE);
    watchdog('commerce_australia_post', 'The default measurements for the commerce_australia_post module is empty or is set to zero. Please set the default package dimensions in the settings page for the commerce_australia_post module. Without the default measurements this module cannot calculate the number of packages and Australia Post rates will not be displayed.', array(), WATCHDOG_ALERT);
    return FALSE;
  }

  /* If there is no total volume or weight for the order, there is no reason to send the request to Australia Post */
  if ($volume['volume'] == NULL || $weight['weight'] == NULL) {
    return FALSE;
  }

  $number_of_packages = ceil($volume['volume'] / $default_package_volume);

  /* Ship To - Customer Shipping Address */
  $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
  // Prepare the shipping address for use in the request.
  if (!empty($order_wrapper->commerce_customer_shipping->commerce_customer_address)) {
    $shipping_address = $order_wrapper->commerce_customer_shipping->commerce_customer_address->value();
  }

  $attributes = array();

  $attributes['from_postcode'] = variable_get('commerce_australia_post_postal_code');
  $attributes['to_postcode'] = $shipping_address['postal_code'];

  $attributes['length'] = variable_get('commerce_australia_post_default_package_size_length');
  $attributes['width'] = variable_get('commerce_australia_post_default_package_size_width');
  $attributes['height'] = variable_get('commerce_australia_post_default_package_size_height');
  $attributes['weight'] = $weight['weight'] / $number_of_packages;

  return array('packages' => $number_of_packages, 'attributes' => $attributes);
}

/**
 * Submits an API request to the Progistics XML Processor.
 *
 * @param $xml
 *   An XML string to submit to the Progistics XML Processor.
 */
function commerce_australia_post_api_request($attributes) {

  $url = 'https://auspost.com.au/api/postage/parcel/domestic/calculate.json';
  $url .= '?' . drupal_http_build_query($attributes);

  $options = array(
    'headers' => array('AUTH-KEY' => variable_get('commerce_australia_post_api_key')),
  );

  $result = drupal_http_request($url, $options);

  // If we received data back from the server...
  if (!empty($result)) {
    return $result;
  }
  else {
    return FALSE;
  }
}
