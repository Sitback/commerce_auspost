<?php

namespace Drupal\commerce_auspost\Forms;

use Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod\AusPost;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDestinations;
use Drupal\commerce_auspost\PostageServices\ServiceSupport;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines the form used to configure the AusPost shipping method.
 *
 * @package Drupal\commerce_auspost\Forms
 */
class ConfigureForm extends FormBase implements ConfigureFormInterface {

  /**
   * The shipping method instance for this configuration form.
   *
   * @var \Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase
   */
  private $shippingMethod;

  /**
   * Service support helpers.
   *
   * @var \Drupal\commerce_auspost\PostageServices\ServiceSupport
   */
  private $serviceSupport;

  /**
   * {@inheritdoc}
   */
  public function setShippingInstance(AusPost $instance) {
    $this->shippingMethod = $instance;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippingInstance() {
    return $this->shippingMethod;
  }

  /**
   * {@inheritdoc}
   */
  public function setServiceSupport(ServiceSupport $serviceSupport) {
    $this->serviceSupport = $serviceSupport;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    $config = $this->getShippingInstance()->getConfiguration();
    return !empty($config['api_information']['api_key']);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_auspost_configuration_form';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\commerce_auspost\ConfigurationException
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getShippingInstance()->getConfiguration();

    // Select all services by default.
    if (empty($configuration['services'])) {
      $serviceIds = array_keys($this->getShippingInstance()->getServices());
      $configuration['services'] = array_combine($serviceIds, $serviceIds);
    }

    $selectPackageTypesIfDefault = function (array $types, &$config) {
      if (empty($config)) {
        $packageTypeIds = array_keys($types);
        $config = array_combine(
          $packageTypeIds,
          $packageTypeIds
        );
      }
    };

    $allPackageTypes = $this->getPackageTypes();
    $form['enabled_package_types'] = [
      '#type' => 'details',
      '#title' => $this->t('Enabled package types'),
      '#description' => $this->t(
        'Select the package types that shipments will be packed into. You can add custom package types on <a href=":url">the package types page</a>.',
        [
          ':url' => Url::fromRoute(
            'entity.commerce_package_type.collection'
          )->toString(),
        ]
      ),
      '#access' => count($allPackageTypes) > 1,
    ];
    foreach (ServiceDestinations::getAll() as $dest) {
      $packageTypes = $this->getPackageTypes($dest);

      // Select all package types by default.
      $selectPackageTypesIfDefault(
        $packageTypes,
        $configuration['enabled_package_types'][$dest]
      );

      $form['enabled_package_types'][$dest] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Enabled package types for %dest', [
          '%dest' => ucfirst($dest),
        ]),
        '#options' => $packageTypes,
        '#default_value' => $configuration['enabled_package_types'][$dest],
        '#required' => TRUE,
        '#access' => count($packageTypes) > 1,
      ];
    }

    $form['api_information'] = [
      '#type' => 'details',
      '#title' => $this->t('API information'),
      '#description' => $this->isConfigured() ? $this->t('Update your AusPost API information.') : $this->t('Fill in your AusPost API information.'),
      '#weight' => $this->isConfigured() ? 10 : -10,
      '#open' => !$this->isConfigured(),
    ];
    $form['api_information']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#description' => $this->t('Enter your AusPost API key.'),
      '#default_value' => $configuration['api_information']['api_key'],
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('AusPost Options'),
      '#description' => $this->t('Additional options for AusPost'),
    ];
    $form['options']['insurance'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include insurance'),
      '#description' => $this->t('Include insurance value of shippable line items in AusPost rate requests'),
      '#default_value' => $configuration['options']['insurance'],
      '#attributes' => [
        'data-states-name' => 'insurance',
      ],
    ];
    $form['options']['insurance_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Insurance settings'),
      '#description' => $this->t('Insurance calculation settings'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          'input[data-states-name="insurance"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    $form['options']['insurance_settings']['insurance_percentage'] = [
      '#type' => 'number',
      '#title' => $this->t('Percentage of order value'),
      '#description' => $this->t('Percentage of order to add on as insurance. For example, enter 1.5 to add 150% extra insurance cover.'),
      '#min' => 0.1,
      '#step' => 0.1,
      '#size' => 5,
      '#default_value' => $configuration['options']['insurance_percentage'],
      '#states' => [
        'required' => [
          'input[data-states-name="insurance"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    $form['options']['insurance_settings']['insurance_limit'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Limit insurance value to Australia Post's insurance limit"),
      '#description' => $this->t('<strong>** WARNING - Enabling this will limit the insurance to $300 for domestic standard services and $5000 for domestic confirmed services and international services.</strong><br>Disabling this will cause Australia Post to reject services where the insurance value is greater than these limit.'),
      '#default_value' => $configuration['options']['insurance_limit'],
    ];
    $form['options']['rate_multiplier'] = [
      '#type' => 'number',
      '#title' => $this->t('Rate multiplier'),
      '#description' => $this->t('A number that each rate returned from AusPost will be multiplied by. For example, enter 1.5 to mark up shipping costs to 150%.'),
      '#min' => 0.1,
      '#step' => 0.1,
      '#size' => 5,
      '#default_value' => $configuration['options']['rate_multiplier'],
    ];
    $form['options']['round'] = [
      '#type' => 'select',
      '#title' => $this->t('Round type'),
      '#description' => $this->t('Choose how the shipping rate should be rounded.'),
      '#options' => [
        PHP_ROUND_HALF_UP => 'Half up',
        PHP_ROUND_HALF_DOWN => 'Half down',
        PHP_ROUND_HALF_EVEN => 'Half even',
        PHP_ROUND_HALF_ODD => 'Half odd',
      ],
      '#default_value' => $configuration['options']['round'],
    ];
    $form['options']['log'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Log the following messages for debugging'),
      '#options' => [
        'request' => $this->t('API request messages'),
        'response' => $this->t('API response messages'),
      ],
      '#default_value' => $configuration['options']['log'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $configuration = $this->getShippingInstance()->getConfiguration();
      $values = $this->value($form_state->getValue($form['#parents']));
      $insuranceOpts = $values['options']['insurance_settings'];

      $configuration['enabled_package_types'] = $values['enabled_package_types'];
      $configuration['api_information']['api_key'] = $values['api_information']['api_key'];
      $configuration['options']['insurance'] = $values['options']['insurance'];
      $configuration['options']['insurance_percentage'] = $insuranceOpts['insurance_percentage'];
      $configuration['options']['insurance_limit'] = $insuranceOpts['insurance_limit'];
      $configuration['options']['rate_multiplier'] = $values['options']['rate_multiplier'];
      $configuration['options']['round'] = $values['options']['round'];
      $configuration['options']['log'] = $values['options']['log'];

      $this->getShippingInstance()->setConfiguration($configuration);
    }
  }

  /**
   * Get all package types for use in a select element.
   *
   * @param string $dest
   *   An (optional) package destination (one of 'domestic' or 'international)
   *   to filter by. If not specified, all package types are returned.
   *
   * @return array
   *   A list of package types, formatted for use in a select form element.
   *
   * @throws \Drupal\commerce_auspost\ConfigurationException
   */
  private function getPackageTypes($dest = NULL) {
    $packageTypes = $this->getShippingInstance()
      ->getPossiblePackageTypes($dest);
    return array_map(function ($package_type) {
      return $package_type['label'];
    }, $packageTypes);
  }

  /**
   * Converts a variable reference to a copy of that variable.
   *
   * @param mixed $value
   *   Reference to copy.
   *
   * @return mixed
   *   De-referenced value.
   */
  private function value($value) {
    return $value;
  }

}
