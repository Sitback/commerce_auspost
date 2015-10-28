<?php

/**
 * @file
 * Handles JSON-related stuff for Commerce Australia Post module.
 */

/**
 * This builds the URL to submit to Australia Post for rates.
 */
function commerce_australia_post_build_rate_request($order) {
  drupal_load('module', 'packaging');
  module_load_include('inc', 'packaging', 'classes/package');
  module_load_include('inc', 'packaging', 'classes/product');
  module_load_include('inc', 'packaging', 'classes/context');
  module_load_include('inc', 'packaging', 'classes/strategy');
  module_load_include('inc', 'commerce_australia_post', 'strategies/commerce_australia_post_last_fit_2d');
  module_load_include('inc', 'packaging', 'strategies/package_each_in_own');
  module_load_include('inc', 'packaging', 'strategies/package_all_in_one');
  module_load_include('inc', 'packaging', 'strategies/package_last_fit');
  module_load_include('inc', 'packaging', 'strategies/package_next_fit');
  module_load_include('inc', 'packaging', 'strategies/package_one_package');
  module_load_include('inc', 'packaging', 'strategies/package_average_volume');
  module_load_include('inc', 'packaging', 'strategies/package_by_volume');
  module_load_include('inc', 'packaging', 'strategies/package_average_weight');
  module_load_include('inc', 'packaging', 'strategies/package_by_key');
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
    $field = field_info_field($field_name);
    $instance = field_info_instance('commerce_customer_profile', 'commerce_customer_address', 'shipping');
    $shipping_address = addressfield_default_values($field, $instance);
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

  // Ship To - Customer Shipping Address.
  // Prepare the shipping address for use in the request.
  if (!empty($order_wrapper->commerce_customer_shipping->commerce_customer_address)) {
    $shipping_address = $order_wrapper->commerce_customer_shipping->commerce_customer_address->value();
  }
  // Domestics shipments have a weight limit of 22kg
  // International shipments have a weight limit of 20kg
  if ($shipping_address['country'] == 'AU') {
    $max_package_weight = 22;
  }
  else {
    $max_package_weight = 20;
  }
  // Determine the shipping profile reference field name for the order.
  $context = new PackagingContext();
  $context->setMaximumPackageWeight($max_package_weight);
  $context->setMaximumPackageVolume($default_package_volume);
  $context->setDefaultWeightUnits('KG');
  $context->setDefaultLengthUnits('CM');
  $strategy = packaging_get_instance(variable_get('commerce_australia_post_packaging_strategy', 'allinone'));
  $context->setStrategy($strategy);
  $products = array();
  foreach ($order_wrapper->commerce_line_items as $line_item_wrapper) {
    $line_item_weight = commerce_physical_product_line_item_weight($line_item_wrapper->value());
    $line_item_dimensions = commerce_physical_product_line_item_dimensions($line_item_wrapper->value());
    $product = new PackagingProduct();
    $product->type = $line_item_wrapper->type->value();
    $product->model = $line_item_wrapper->line_item_label->value();
    $product->setPrice((int) ($line_item_wrapper->commerce_unit_price->amount->value()));
    $product->setWeight($line_item_weight['weight'] / $line_item_wrapper->quantity->value());
    $product->setWeightUnits(strtoupper($line_item_weight['unit']));
    $product->setDimensions($line_item_dimensions);
    $product->setLengthUnits(strtoupper($line_item_dimensions['unit']));
    $product->setPackageQuantity(1);
    $product->setQuantity((int) ($line_item_wrapper->quantity->value()));
    $product->shippable = TRUE;
    $products[] = $product;
  }
  $packages = packaging_package_products($context, $products);

  if (!count($packages)) {
    return FALSE;
  }

  $number_of_packages = count($packages);

  foreach ($packages as $package) {
    $attributes[] = array(
      'from_postcode' => check_plain(variable_get('commerce_australia_post_postal_code')),
      'to_postcode' => check_plain($shipping_address['postal_code']),
      'country_code' => check_plain($shipping_address['country']),
      'length' => check_plain(variable_get('commerce_australia_post_default_package_size_length')),
      'width' => check_plain(variable_get('commerce_australia_post_default_package_size_width')),
      'height' => check_plain(variable_get('commerce_australia_post_default_package_size_height')),
      'weight' => $package->getWeight(),
    );
  }
  return array('packages' => $number_of_packages, 'attributes' => $attributes);
}

/**
 * Submits an API request to the Australia Post API.
 *
 * @param array $attributes
 *   An array of packages to get quotes for.
 * @param 
 */
function commerce_australia_post_api_request(array $attributes, $destination = 'domestic') {

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
