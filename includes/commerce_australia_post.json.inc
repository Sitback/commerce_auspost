<?php

/**
 * @file
 * Handles JSON-related stuff for Commerce Australia Post module.
 */

/**
 * This builds the URL to submit to Australia Post for rates.
 */
function commerce_australia_post_build_rate_request($order) {
  $order_wrapper = entity_metadata_wrapper('commerce_order', $order);

  // Determine the shipping profile reference field name for the order.
  $field_name = commerce_physical_order_shipping_field_name($order);
  // unused:
  // $shipping_profile = $order_wrapper->{$field_name}->value();
  // Prepare the shipping address for use in the request.
  if (!empty($order_wrapper->{$field_name}->commerce_customer_address)) {
    $shipping_address = $order_wrapper->{$field_name}->commerce_customer_address->value();
  }
  else {
    $shipping_address = addressfield_default_values();
  }

  //FIXME: The following assumes everything is the same density and does not optimally pack
  // Get the order weight. Returns $weight['unit'] and $weight['weight'].
  $weight = commerce_physical_order_weight($order, 'kg');
  // Get the order volume. Returns $volume['unit'] and $volume['volume'].
  $volume = commerce_physical_order_volume($order, 'cm');

  $default_package_volume = variable_get('commerce_australia_post_default_package_size_length', '0') *
                            variable_get('commerce_australia_post_default_package_size_width', '0') *
                            variable_get('commerce_australia_post_default_package_size_height', '0');

  // If there is no default package volume, we cannot calculate the number of packages and there is no reason to send to Australia Post.
  if ($default_package_volume == 0) {
    drupal_set_message(t('There was an error with the Australia Post configuration.'), 'error', FALSE);
    watchdog('commerce_australia_post', 'The default measurements for the commerce_australia_post module is empty or is set to zero. Please set the default package dimensions in the settings page for the commerce_australia_post module. Without the default measurements this module cannot calculate the number of packages and Australia Post rates will not be displayed.', array(), WATCHDOG_ALERT);
    return FALSE;
  }

  // If there is no total volume or weight for the order, there is no reason to send the request to Australia Post.
  if ($volume['volume'] == NULL || $weight['weight'] == NULL) {
    return FALSE;
  }

  $number_of_packages = ceil($volume['volume'] / $default_package_volume);

  // Ship To - Customer Shipping Address.
  // Prepare the shipping address for use in the request.
  if (!empty($order_wrapper->commerce_customer_shipping->commerce_customer_address)) {
    $shipping_address = $order_wrapper->commerce_customer_shipping->commerce_customer_address->value();
  }

  $attributes = array();

  $attributes['from_postcode'] = check_plain(variable_get('commerce_australia_post_postal_code'));
  $attributes['to_postcode'] = check_plain($shipping_address['postal_code']);
  $attributes['country_code'] = check_plain($shipping_address['country']);
  $attributes['length'] = check_plain(variable_get('commerce_australia_post_default_package_size_length'));
  $attributes['width'] = check_plain(variable_get('commerce_australia_post_default_package_size_width'));
  $attributes['height'] = check_plain(variable_get('commerce_australia_post_default_package_size_height'));
  $attributes['weight'] = $weight['weight'] / $number_of_packages;

  return array('packages' => $number_of_packages, 'attributes' => $attributes);
}

/**
 * Submits an API request to the Progistics XML Processor.
 *
 * @param $xml
 *   An XML string to submit to the Progistics XML Processor.
 */
function commerce_australia_post_api_request($attributes, $destination = 'domestic') {

  $url = 'https://auspost.com.au/api/postage/parcel/' . $destination . '/calculate.json';
  $url .= '?' . drupal_http_build_query($attributes);

  $options = array(
    'headers' => array('AUTH-KEY' => check_plain(variable_get('commerce_australia_post_api_key'))),
  );

  // Log the API request if specified.
  $message = '';
  $logging = variable_get('commerce_australia_post_log', array());
  if (isset($logging['request']) && $logging['request']) {
    if (empty($message)) {
      $message = t('Submitting API request to Australia Post');
    }
    watchdog('auspost',
             '@message:<pre>@url</pre><pre>@options</pre>',
              array(
                '@message' => $message,
                '@url' => $url,
                '@options' => print_r($options, TRUE)
              )
    );
  }

  $result = drupal_http_request($url, $options);

  if (isset($logging['response']) && $logging['response']) {
    watchdog('auspost',
             'API response received:<pre>' . print_r($result, TRUE) . '</pre>');
  }

  // If we received data back from the server...
  if (!empty($result)) {
    return $result;
  }
  else {
    return FALSE;
  }
}
