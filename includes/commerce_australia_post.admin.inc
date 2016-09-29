<?php

/**
 * @file
 * Handles admin settings page for Commerce Australia Post module.
 */

/**
 * Implements hook_settings_form().
 */
function commerce_australia_post_settings_form(array $form, array &$form_state) {
  $form['api'] = array(
    '#type' => 'fieldset',
    '#title' => t('auspost API credentials'),
    '#collapsible' => TRUE,
    '#description' => t('In order to obtain shipping rate estimates, you must have an account with Australia Post. You can apply for Australia Post API credentials at !auspost',
                      array(
                        '!auspost' => l(t('auspost.com.au'),
                        'http://auspost.com.au/devcentre/pacpcs.asp',
                          array('attributes' => array('target' => '_blank'))),
                      )
    ),
  );

  $form['api']['commerce_australia_post_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Australia Post API Key'),
    '#default_value' => variable_get('commerce_australia_post_api_key'),
    '#required' => TRUE,
  );

  $form['origin'] = array(
    '#type' => 'fieldset',
    '#title' => t('Ship From Address'),
    '#collapsible' => TRUE,
  );
  $form['origin']['commerce_australia_post_postal_code'] = array(
    '#type' => 'textfield',
    '#title' => t('Postal Code'),
    '#size' => 5,
    '#default_value' => variable_get('commerce_australia_post_postal_code'),
    '#required' => TRUE,
  );

  $form['services'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enable Australia Post Shipping Services'),
    '#collapsible' => TRUE,
  );

  foreach (_commerce_australia_post_service_list() as $key => $service) {
    if (($service['type'] == 'parcel') && ($service['destination'] == 'domestic')) {
      $array_options[$key] = $service['title'];
    }
  }
  $form['services']['commerce_australia_post_dom_services'] = array(
    '#title' => t('Domestic Parcel Services'),
    '#type' => 'checkboxes',
    '#options' => $array_options,
    '#default_value' => variable_get('commerce_australia_post_dom_services', array()),
  );
  $array_options = array();
  foreach (_commerce_australia_post_service_list() as $key => $service) {
    if (($service['type'] == 'parcel') && ($service['destination'] == 'international')) {
      $array_options[$key] = $service['title'];
    }
  }
  $form['services']['commerce_australia_post_int_services'] = array(
    '#title' => t('International Parcel Services'),
    '#type' => 'checkboxes',
    '#options' => $array_options,
    '#default_value' => variable_get('commerce_australia_post_int_services', array()),
  );

  $array_options = array();

  $form['letters'] = array(
    '#type' => 'fieldset',
    '#title' => t('Enable Australia Post Letter Services'),
    '#collapsible' => TRUE,
    '#description' => t('<strong>NB. If multiple line items are order, each line item will be shipped in a separate envelope.</strong>'),

  );
  foreach (_commerce_australia_post_service_list() as $key => $service) {
    if (($service['type'] == 'letter') && ($service['destination'] == 'domestic')) {
      $array_options[$key] = $service['title'];
    }
  }
  $form['letters']['commerce_australia_post_dom_letters'] = array(
    '#title' => t('Domestic Letter Services'),
    '#type' => 'checkboxes',
    '#options' => $array_options,
    '#default_value' => variable_get('commerce_australia_post_dom_letters', array()),
  );
  $array_options = array();
  foreach (_commerce_australia_post_service_list() as $key => $service) {
    if (($service['type'] == 'letter') && ($service['destination'] == 'international')) {
      $array_options[$key] = $service['title'];
    }
  }
  $form['letters']['commerce_australia_post_int_letters'] = array(
    '#title' => t('International Letter Services'),
    '#type' => 'checkboxes',
    '#options' => $array_options,
    '#default_value' => variable_get('commerce_australia_post_int_letters', array()),
  );

  // Fields for default package size (cm).
  $form['default_package_size'] = array(
    '#type' => 'fieldset',
    '#title' => t('Default/Maximum package size (cm)'),
    '#collapsible' => FALSE,
    '#description' => t('Australia Post requires a package size when determining estimates for parcel services.'),
  );
  $form['default_package_size']['commerce_australia_post_default_package_size_length'] = array(
    '#type' => 'textfield',
    '#title' => t('Length'),
    '#size' => 5,
    '#required' => TRUE,
    '#default_value' => variable_get('commerce_australia_post_default_package_size_length'),
    '#field_suffix' => 'cm',
  );
  $form['default_package_size']['commerce_australia_post_default_package_size_width'] = array(
    '#type' => 'textfield',
    '#title' => t('Width'),
    '#size' => 5,
    '#required' => TRUE,
    '#default_value' => variable_get('commerce_australia_post_default_package_size_width'),
    '#field_suffix' => 'cm',
  );
  $form['default_package_size']['commerce_australia_post_default_package_size_height'] = array(
    '#type' => 'textfield',
    '#title' => t('Height'),
    '#size' => 5,
    '#required' => TRUE,
    '#default_value' => variable_get('commerce_australia_post_default_package_size_height'),
    '#field_suffix' => 'cm',
  );
  $form['default_package_size']['commerce_australia_post_default_package'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use variable package sizes up to maximum size'),
    '#default_value' => variable_get('commerce_australia_post_default_package', FALSE),
  );
  $form['default_package_size']['details'] = array(
    '#markup' => 'The package size is used to determine the number of packages necessary to
      create a Australia Post shipping cost estimate. <strong>Auspost shipping will not be available to items with no dimensions or weight.</strong> The logic implemented works as:
      <ul>
      <li>If the total products exceed the size of one package,</li>
      <li>Pack them into the smallest box possible filling up the maximum size first.</li>
      </ul>',
  );
  // Fields for default package size (cm).
  $form['insurance'] = array(
    '#type' => 'fieldset',
    '#title' => t('Shipment Insurance'),
    '#collapsible' => FALSE,
    '#description' => t('Australia Post offers insurance on shipments.'),
  );
  $form['insurance']['commerce_australia_post_insurance'] = array(
    '#type' => 'textfield',
    '#title' => t('Insurance - Percentage of order value'),
    '#size' => 5,
    '#required' => TRUE,
    '#default_value' => variable_get('commerce_australia_post_insurance'),
    '#field_suffix' => '%',
  );
  $form['insurance']['commerce_australia_post_insurance_limit'] = array(
    '#type' => 'checkbox',
    '#title' => t('Limit insurance value to Australia Posts Insurance Limit'),
    '#default_value' => variable_get('commerce_australia_post_insurance_limit', FALSE),
  );
  $form['insurance']['detail'] = array(
    '#markup' => t('<strong>** WARNING - Enabling this will limit the insurance to $300 for domestic standard services and $5000 for domestic confirmed services and international services.</strong><br>Disabling this will cause Australia Post to reject services where the insurance value is greater than these limit.'),
  );

  $form['advanced_options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['advanced_options']['commerce_australia_post_show_description'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show Australia Post description in addition to the title on Shipping Service selection pane'),
    '#default_value' => variable_get('commerce_australia_post_show_description', 0),
  );
  $form['advanced_options']['commerce_australia_post_log'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Log the following messages for debugging'),
    '#options' => array(
      'request' => t('API request messages'),
      'response' => t('API response messages'),
    ),
    '#default_value' => variable_get('commerce_australia_post_log', array()),
  );
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );

  return $form;
}

/**
 * Implements hook_form_validate().
 */
function commerce_australia_post_settings_form_validate(array $form, array &$form_state) {

  $values = $form_state['values'];

  if (!is_numeric($values['commerce_australia_post_default_package_size_length']) || $values['commerce_australia_post_default_package_size_length'] <= 0) {
    form_set_error('commerce_australia_post_default_package_size_length', t('Length must be a positive number.'));

  }
  if (!is_numeric($values['commerce_australia_post_default_package_size_width']) || $values['commerce_australia_post_default_package_size_width'] <= 0) {
    form_set_error('commerce_australia_post_default_package_size_width', t('Width must be a positive number.'));

  }
  if (!is_numeric($values['commerce_australia_post_default_package_size_height']) || $values['commerce_australia_post_default_package_size_height'] <= 0) {
    form_set_error('commerce_australia_post_default_package_size_height', t('Height must be a positive number.'));

  }

  // Ensure the default package size is less than the maximum.
  $dimensions = array(
    $values['commerce_australia_post_default_package_size_length'],
    $values['commerce_australia_post_default_package_size_width'],
    $values['commerce_australia_post_default_package_size_height'],
  );
  sort($dimensions);
  list($height, $width, $length) = $dimensions;
  $girth = 2 * $width + 2 * $height;
  if ($length > 105) {
    form_set_error('commerce_australia_post_default_package_size_length', t('The greatest dimension of the package size must be 105 cm or less.'));
  }
  if ($girth > 140) {
    form_set_error('commerce_australia_post_default_package_size_length', t('The girth (2*width + 2*height) of the package size must be 140 cm or less.'));
  }
}

/**
 * Implements hook_form_submit().
 */
function commerce_australia_post_settings_form_submit(array $form, array &$form_state) {

  $services = variable_get('commerce_australia_post_dom_services', array());
  $services = array_merge($services, variable_get('commerce_australia_post_int_services', array()));
  $services = array_merge($services, variable_get('commerce_australia_post_dom_letters', array()));
  $services = array_merge($services, variable_get('commerce_australia_post_int_letters', array()));
  $form_services = $form_state['values']['commerce_australia_post_dom_services'];
  $form_services = array_merge($form_services, $form_state['values']['commerce_australia_post_int_services']);
  $form_services = array_merge($form_services, $form_state['values']['commerce_australia_post_dom_letters']);
  $form_services = array_merge($form_services, $form_state['values']['commerce_australia_post_int_letters']);
  // If the selected services have changed then rebuild caches.
  if ($services !== $form_services) {
    variable_set('commerce_australia_post_dom_services', $form_state['values']['commerce_australia_post_dom_services']);
    variable_set('commerce_australia_post_int_services', $form_state['values']['commerce_australia_post_int_services']);
    variable_set('commerce_australia_post_dom_letters', $form_state['values']['commerce_australia_post_dom_letters']);
    variable_set('commerce_australia_post_int_letters', $form_state['values']['commerce_australia_post_int_letters']);

    commerce_shipping_services_reset();
    entity_defaults_rebuild();
    rules_clear_cache(TRUE);
    menu_rebuild();
  }

  variable_set('commerce_australia_post_api_key', $form_state['values']['commerce_australia_post_api_key']);
  variable_set('commerce_australia_post_postal_code', $form_state['values']['commerce_australia_post_postal_code']);
  variable_set('commerce_australia_post_default_package', $form_state['values']['commerce_australia_post_default_package']);
  variable_set('commerce_australia_post_default_package_size_length', $form_state['values']['commerce_australia_post_default_package_size_length']);
  variable_set('commerce_australia_post_default_package_size_width', $form_state['values']['commerce_australia_post_default_package_size_width']);
  variable_set('commerce_australia_post_default_package_size_height', $form_state['values']['commerce_australia_post_default_package_size_height']);
  variable_set('commerce_australia_post_show_description', $form_state['values']['commerce_australia_post_show_description']);
  variable_set('commerce_australia_post_log', $form_state['values']['commerce_australia_post_log']);
  variable_set('commerce_australia_post_insurance', $form_state['values']['commerce_australia_post_insurance']);
  variable_set('commerce_australia_post_insurance_limit', $form_state['values']['commerce_australia_post_insurance_limit']);

  drupal_set_message(t('The Australia Post configuration options have been saved.'));
}
