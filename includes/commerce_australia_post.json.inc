<?php

/**
 * @file
 * Handles JSON-related stuff for Commerce Australia Post module.
 */

/**
 * This builds the URL to submit to Australia Post for rates.
 *
 * @param object $order
 *   stdClass of order to be rated.
 * @param array $shipping_service
 *   Array of shipping service info to be rated.
 *
 * @return array
 *   Array containing the attributes that will be sent to AusPost PAC API
 */
function commerce_australia_post_build_rate_request(stdClass $order, array $shipping_service) {
  $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
  // Determine the shipping profile reference field name for the order.
  $field_name = commerce_physical_order_shipping_field_name($order);
  // Prepare the shipping address for use in the request.
  if (!empty($order_wrapper->{$field_name}->commerce_customer_address)) {
    $shipping_address = $order_wrapper->{$field_name}->commerce_customer_address->value();
  }
  else {
    $field = field_info_field($field_name);
    $instance = field_info_instance('commerce_customer_profile', 'commerce_customer_address', 'shipping');
    $shipping_address = addressfield_default_values($field, $instance);
  }

  // FIXME: The following assumes everything is the same density.
  if ($shipping_service['type'] == 'parcel') {
    // Get the order weight. Returns $weight['unit'] and $weight['weight'].
    $weight = commerce_physical_order_weight($order, 'kg');
    // Get the order volume. Returns $volume['unit'] and $volume['volume'].
    $volume = commerce_physical_order_volume($order, 'cm');
    $package_dimensions = array(
      'length' => variable_get('commerce_australia_post_default_package_size_length', '0'),
      'width' => variable_get('commerce_australia_post_default_package_size_width', '0'),
      'height' => variable_get('commerce_australia_post_default_package_size_height', '0'),
    );
    $default_package_volume = $package_dimensions['length'] *
                              $package_dimensions['width'] *
                              $package_dimensions['height'];
  }
  else {
    $weight = commerce_physical_order_weight($order, 'g');
    $volume = commerce_physical_order_volume($order, 'mm');
    $default_package_volume = $shipping_service['max_dimensions']['length'] *
                              $shipping_service['max_dimensions']['width'] *
                              $shipping_service['max_dimensions']['thickness'];
  }

  // If there is no default package volume, we cannot calculate the number of
  // packages and there is no reason to send to Australia Post.
  if ($default_package_volume == 0) {
    drupal_set_message(t('There was an error with the Australia Post configuration.'), 'error', FALSE);
    watchdog('commerce_australia_post', 'The default measurements for the commerce_australia_post module is empty or is set to zero. Please set the default package dimensions in the settings page for the commerce_australia_post module. Without the default measurements this module cannot calculate the number of packages and Australia Post rates will not be displayed.', array(), WATCHDOG_ALERT);
    return FALSE;
  }

  // If there is no total volume or weight for the order, there is no reason
  // to send the request to Australia Post.
  if ($volume['volume'] == NULL || $weight['weight'] == NULL) {
    return FALSE;
  }
  // Determine if each item will fit into an envelope and total order is not
  // too heavy. Count the number of items as each item will be sent in a
  // separate envelope.
  if ($shipping_service['type'] == 'letter') {
    $number_of_packages = 0;
    foreach ($order_wrapper->commerce_line_items as $line_item_wrapper) {
      $line_item_dimensions = commerce_physical_product_line_item_dimensions($line_item_wrapper->value());
      $line_item_dimensions = physical_dimensions_convert($line_item_dimensions, 'mm');
      $packages = $line_item_wrapper->quantity->value();
      sort($line_item_dimensions);
      list($units, $thickness, $width, $length) = $line_item_dimensions;
      if (($length > $shipping_service['max_dimensions']['length']) ||
          ($width > $shipping_service['max_dimensions']['width']) ||
          ($thickness > $shipping_service['max_dimensions']['thickness'])) {
        return FALSE;
      }
      $number_of_packages += $packages;
      $weight_letter = $shipping_service['max_dimensions']['weight'] /
                       $number_of_packages;
    }
  }
  else {
    $number_of_packages = ceil($volume['volume'] / $default_package_volume);
    // Determine volume of last package in multi-package shipment.
    $volume_last_package = $volume['volume'] % $default_package_volume;
    // Determine weight of default package in multi-package shipments.
    if (variable_get('commerce_australia_post_default_package', 0)) {
      $weight_box = $weight['weight'] / abs(($volume['volume'] / $default_package_volume));
    }
    else {
      // Determine weight of default package in fixed package size shipments.
      $weight_box = $weight['weight'] / $number_of_packages;
    }
    // Determine weight of default package in single-package shipments.
    if ($number_of_packages == 1) {
      $weight_box = $weight['weight'];
    }

    // Loop through the number of calculated packages to create the
    // return array.
    for ($i = 1; $i <= $number_of_packages; $i++) {
      if (($i == $number_of_packages) && ($volume_last_package != 0) && variable_get('commerce_australia_post_default_package', 0)) {
        $scale = pow($volume_last_package, (1 / 3)) + 1;
        $scale = round($scale, 1);

        $package_dimensions['length'] = $scale;
        $package_dimensions['width'] = $scale;
        $package_dimensions['height'] = $scale;
        $weight_box = $weight['weight'] * ($volume_last_package / $volume['volume']);
      }
      $package_line_items[$i - 1] = array(
        'weight' => round(max(array(0.1, $weight_box)), 2),
        'dimensions' => array(
          'length' => $package_dimensions['length'],
          'width' => $package_dimensions['width'],
          'height' => $package_dimensions['height'],
        ),
      );
    }
  }

  // Ship To - Customer Shipping Address.
  // Prepare the shipping address for use in the request.
  if (!empty($order_wrapper->commerce_customer_shipping->commerce_customer_address)) {
    $shipping_address = $order_wrapper->commerce_customer_shipping->commerce_customer_address->value();
  }

  for ($i = 1; $i <= $number_of_packages; $i++) {
    $attributes[$i - 1] = array(
      'from_postcode' => check_plain(variable_get('commerce_australia_post_postal_code')),
      'to_postcode' => check_plain($shipping_address['postal_code']),
      'country_code' => check_plain($shipping_address['country']),
    );
    if ($shipping_service['type'] == 'letter') {
      $ship_dims[$i - 1] = array(
        'pieces' => array(
          'weight' => $weight_letter,
        ),
      );
    }
    else {
      $ship_dims[$i - 1] = array(
        'pieces' => array(
          'length' => $package_line_items[$i - 1]['dimensions']['length'],
          'width'  => $package_line_items[$i - 1]['dimensions']['width'],
          'height' => $package_line_items[$i - 1]['dimensions']['height'],
          'weight' => $package_line_items[$i - 1]['weight'],
        ),
      );
    }
    $attributes[$i - 1] = array_merge($attributes[$i - 1], $ship_dims[$i - 1]);
  }
  return array('packages' => $number_of_packages, 'attributes' => $attributes);
}

/**
 * Submits an API request to the Australia Post API.
 *
 * @param array $attributes
 *   An array of parameters to create the JSON string to submit to the
 *   Australia Post API.
 * @param array $shipping_service
 *   Array of shipping service info to be rated.
 */
function commerce_australia_post_api_request(array $attributes, array $shipping_service) {
  $url = 'https://digitalapi.auspost.com.au/postage/' .
          $shipping_service['type'] . '/' .
          $shipping_service['destination'] . '/calculate.json';
  $url .= '?' . drupal_http_build_query($attributes);
  // Strip out array elements where multiple option_codes/suboption_codes
  // are needed.
  $elements = array('[0]', '[1]', '[2]', '[3]');
  $url = str_replace($elements, "", $url);

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
                '@options' => print_r($options, TRUE),
              )
    );
  }

  $result = drupal_http_request($url, $options);

  if (isset($logging['response']) && $logging['response']) {
    watchdog('auspost',
             'API response received:<pre>@result</pre>',
              array(
                '@result' => print_r($result, TRUE),
              )
    );
  }

  // If we received data back from the server...
  if (!empty($result)) {
    return $result;
  }
  else {
    return FALSE;
  }
}
