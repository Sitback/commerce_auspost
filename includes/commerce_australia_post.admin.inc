<?php

/**
 * @file
 * Handles admin settings page for Commerce Australia Post module.
 */

/**
 * Implements hook_settings_form().
 */
function commerce_australia_post_settings_form($form, &$form_state) {
  $form['api'] = array(
    '#type' => 'fieldset',
    '#title' => t('auspost API credentials'),
    '#collapsible' => TRUE,
    '#description' => t('In order to obtain shipping rate estimates, you must have an account with Australia Post. You can apply for Australia Post API credentials at !auspost',
                      array('!auspost' => l(t('auspost.com.au'), 'http://auspost.com.au/devcentre/pacpcs.asp', array('attributes' => array('target' => '_blank'))))),
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
    $array_options[$key] = $service['title'];
  }
  $form['services']['commerce_australia_post_services'] = array(
    '#type' => 'checkboxes',
    '#options' => $array_options,
    '#default_value' => variable_get('commerce_australia_post_services', array()),
  );

  // Fields for default package size (cm).
  $form['default_package_size'] = array(
    '#type' => 'fieldset',
    '#title' => t('Default maximum package size (cm)'),
    '#collapsible' => FALSE,
    '#description' => t('Australia Post requires a package size when determining estimates.'),
  );
  $form['default_package_size']['commerce_australia_post_default_package_size_length'] = array(
    '#type' => 'textfield',
    '#title' => t('Length'),
    '#size' => 5,
    '#required' => TRUE,
    '#default_value' => variable_get('commerce_australia_post_default_package_size_length'),
  );
  $form['default_package_size']['commerce_australia_post_default_package_size_width'] = array(
    '#type' => 'textfield',
    '#title' => t('Width'),
    '#size' => 5,
    '#required' => TRUE,
    '#default_value' => variable_get('commerce_australia_post_default_package_size_width'),
  );
  $form['default_package_size']['commerce_australia_post_default_package_size_height'] = array(
    '#type' => 'textfield',
    '#title' => t('Height'),
    '#size' => 5,
    '#required' => TRUE,
    '#default_value' => variable_get('commerce_australia_post_default_package_size_height'),
  );
  $form['default_package_size']['details'] = array(
    '#markup' => 'The package size is used to determine the number of packages necessary to
      create a Australia Post shipping cost estimate. <strong>Auspost shipping will not be available to items with no dimensions or weight.</strong> The logic implemented works as:
      <ul>
      <li>If the total products exceed the size of one package,</li>
      <li>Pack them into the smallest box possible filling up the maximum size first.</li>
      </ul>',
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
function commerce_australia_post_settings_form_validate($form, &$form_state) {

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

  // Ensure the default package size is less than the maximum
  $dimensions = array($values['commerce_australia_post_default_package_size_length'],
                      $values['commerce_australia_post_default_package_size_width'],
                      $values['commerce_australia_post_default_package_size_height']);
  sort($dimensions);
  list($height, $width, $length) = $dimensions;
  $girth = 2*$width + 2*$height;
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
function commerce_australia_post_settings_form_submit($form, &$form_state) {

  $services = variable_get('commerce_australia_post_services', NULL);

  // If the selected services have changed then rebuild caches.
  if ($services !== $form_state['values']['commerce_australia_post_services']) {
    variable_set('commerce_australia_post_services', $form_state['values']['commerce_australia_post_services']);

    commerce_shipping_services_reset();
    entity_defaults_rebuild();
    rules_clear_cache(TRUE);
    menu_rebuild();
  }

  variable_set('commerce_australia_post_api_key', $form_state['values']['commerce_australia_post_api_key']);
  variable_set('commerce_australia_post_postal_code', $form_state['values']['commerce_australia_post_postal_code']);
  variable_set('commerce_australia_post_default_package_size_length', $form_state['values']['commerce_australia_post_default_package_size_length']);
  variable_set('commerce_australia_post_default_package_size_width', $form_state['values']['commerce_australia_post_default_package_size_width']);
  variable_set('commerce_australia_post_default_package_size_height', $form_state['values']['commerce_australia_post_default_package_size_height']);



  drupal_set_message(t('The Australia Post configuration options have been saved.'));
}
