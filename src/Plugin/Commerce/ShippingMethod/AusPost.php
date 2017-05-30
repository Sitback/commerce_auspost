<?php

namespace Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_auspost\Address;
use Drupal\commerce_auspost\ConfigurationException;
use Drupal\commerce_auspost\PostageAssessment\Client;
use Drupal\commerce_auspost\PostageAssessment\ClientResponseException;
use Drupal\commerce_auspost\PostageAssessment\Request;
use Drupal\commerce_auspost\PostageAssessment\SupportedServices;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Error;
use Exception;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Australia Post shipping method.
 *
 * @see \Drupal\commerce_auspost\PostageAssessment\SupportedServices
 *   For further information on supported services.
 *
 * @CommerceShippingMethod(
 *   id = "auspost",
 *   label = @Translation("Australia Post"),
 *   services = {
 *     "AUS_SERVICE_OPTION_STANDARD" = @Translation("Australia Post - Standard Post"),
 *     "AUS_PARCEL_COURIER" = @Translation("Australia Post - Courier Post"),
 *     "AUS_PARCEL_EXPRESS" = @Translation("Australia Post - Express Post"),
 *     "INT_PARCEL_AIR_OWN_PACKAGING" = @Translation("Australia Post - International Economy Air"),
 *     "INT_PARCEL_AIR_OWN_PACK_SIG" = @Translation("Australia Post - International Economy Air (Signature required)"),
 *     "INT_PARCEL_AIR_OWN_PACK_INS" = @Translation("Australia Post - International Economy Air (Insured)"),
 *   }
 * )
 */
class AusPost extends ShippingMethodBase {

  // Package all items in one box, ignoring dimensions.
  const PACKAGE_ALL_IN_ONE = 'allinone';

  // Package each line item in its own box, ignoring dimensions.
  const PACKAGE_INDIVIDUAL = 'individual';

  // Calculate volume to determine how many boxes are needed.
  const PACKAGE_CALCULATE = 'calculate';

  // Currency code.
  const AUD_CURRENCY_CODE = 'AUD';

  /**
   * Watchdog logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $watchdog;

  /**
   * The price rounder.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  private $rounder;

  /**
   * All supported services.
   *
   * @var \Drupal\commerce_auspost\PostageAssessment\SupportedServices
   */
  private $supportedServices;

  /**
   * AusPost client.
   *
   * @var \Drupal\commerce_auspost\PostageAssessment\Client
   */
  private $client;

  /**
   * AusPost constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $packageTypeManager
   *   The package type manager.
   * @param \Psr\Log\LoggerInterface $watchdog
   *   Watchdog logger.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The price rounder.
   * @param \Drupal\commerce_auspost\PostageAssessment\SupportedServices $supportedServices
   *   AusPost PAC supported services.
   * @param \Drupal\commerce_auspost\PostageAssessment\Client $client
   *   AusPost PAC client.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    PackageTypeManagerInterface $packageTypeManager,
    LoggerInterface $watchdog,
    RounderInterface $rounder,
    SupportedServices $supportedServices,
    Client $client
  ) {
    parent::__construct(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $packageTypeManager
    );
    $this->watchdog = $watchdog;
    $this->rounder = $rounder;
    $this->supportedServices = $supportedServices;
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    /** @noinspection PhpParamsInspection */
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('plugin.manager.commerce_package_type'),
      $container->get('logger.channel.commerce_auspost'),
      $container->get('commerce_price.rounder'),
      $container->get('commerce_auspost.postage_assessment.services'),
      $container->get('commerce_auspost.postage_assessment.client')
    );
  }

  /**
   * Determine if we have the minimum information to connect to AusPost.
   *
   * @return bool
   *   TRUE if there is enough information to connect, FALSE otherwise.
   */
  private function isConfigured() {
    return !empty($this->configuration['api_information']['api_key']);
  }

  /**
   * Logs requests and responses from AusPost.
   *
   * @param string $message
   *   The message to log.
   * @param mixed $data
   *   The AusPost request or response object.
   * @param string $level
   *   The log level.
   * @param bool $skipCheck
   *   Whether to skip the check to log or not.
   */
  private function logRequest($message, $data, $level = LogLevel::INFO, $skipCheck = FALSE) {
    if ($skipCheck || $this->configuration['options']['log']['request']) {
      $this->watchdog->log($level, "$message <br>@rate_request", [
        '@rate_request' => var_export($data, TRUE),
      ]);
    }
  }

  /**
   * Log an exception to watchdog.
   *
   * @param \Exception $exception
   *   Exception to log.
   * @param string $message
   *   Additional information.
   */
  private function logException(Exception $exception, $message = '') {
    $variables = Error::decodeException($exception);

    $defaultMessage = '%type: @message in %function (line %line of %file).';
    if (!empty($message)) {
      $message = "{$defaultMessage} {$message}";
    } else {
      $message = $defaultMessage;
    }

    $this->watchdog->error($message, $variables);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\commerce_auspost\ConfigurationException
   * @throws \Drupal\commerce_auspost\PostageAssessment\ServiceNotFoundException
   * @throws \Drupal\commerce_auspost\PostageAssessment\ClientException
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function calculateRates(ShipmentInterface $shipment) {
    if (!$this->isConfigured()) {
      throw new ConfigurationException(
        'The commerce_auspost shipping method is not configured.'
      );
    }

    $address = new Address($shipment);
    if ($address->isEmpty()) {
      return [];
    }

    $this->client->setApiKey(
      $this->configuration['api_information']['api_key']
    );

    // @TODO: Allow altering of rate labels.
    $serviceDefinitions = $this->supportedServices->getServicesByKeys(
      array_keys($this->services)
    );

    // Calculate postage for all services.
    $rates = [];
    foreach ($serviceDefinitions as $definitionKey => $definition) {
      try {
        $request = (new Request())
          ->setAddress($address)
          ->setPackageType($definition['type'])
          ->setShipment($shipment)
          ->setServiceDefinition($definition);
        $postage = $this->client->calculatePostage($request);
      } catch (ClientErrorResponseException $e) {
        $this->logException($e, 'Error fetching rates from AusPost.');
        continue;
      }

      $rates[] = new ShippingRate(
        $definitionKey,
        $this->services[$definitionKey],
        new Price($postage, static::AUD_CURRENCY_CODE)
      );
    }

    return $rates;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_information' => [
        'api_key' => '',
      ],
      'options' => [
        'packaging' => static::PACKAGE_ALL_IN_ONE,
        'insurance' => FALSE,
        'rate_multiplier' => 1.0,
        'round' => PHP_ROUND_HALF_UP,
        'log' => [],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Select all services by default.
    if (empty($this->configuration['services'])) {
      $serviceIds = array_keys($this->services);
      $this->configuration['services'] = array_combine($serviceIds, $serviceIds);
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
      '#default_value' => $this->configuration['api_information']['api_key'],
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('AusPost Options'),
      '#description' => $this->t('Additional options for AusPost'),
    ];
    $form['options']['packaging'] = [
      '#type' => 'select',
      '#title' => $this->t('Packaging strategy'),
      '#description' => $this->t('Select your packaging strategy. "All items in one box" will ignore package type and product dimensions, and assume all items go in one box. "Each item in its own box" will create a box for each line item in the order, "Calculate" will attempt to figure out how many boxes are needed based on package type volumes and product volumes, similar to commerce_auspost 7.x.'),
      '#options' => [
        static::PACKAGE_ALL_IN_ONE => $this->t('All items in one box'),
        static::PACKAGE_INDIVIDUAL => $this->t('Each item in its own box'),
        static::PACKAGE_CALCULATE => $this->t('Calculate'),
      ],
      '#default_value' => $this->configuration['options']['packaging'],
    ];
    $form['options']['insurance'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include insurance'),
      '#description' => $this->t('Include insurance value of shippable line items in AusPost rate requests'),
      '#default_value' => $this->configuration['options']['insurance'],
    ];
    $form['options']['rate_multiplier'] = [
      '#type' => 'number',
      '#title' => $this->t('Rate multiplier'),
      '#description' => $this->t('A number that each rate returned from AusPost will be multiplied by. For example, enter 1.5 to mark up shipping costs to 150%.'),
      '#min' => 0.1,
      '#step' => 0.1,
      '#size' => 5,
      '#default_value' => $this->configuration['options']['rate_multiplier'],
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
      '#default_value' => $this->configuration['options']['round'],
    ];
    $form['options']['log'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Log the following messages for debugging'),
      '#options' => [
        'request' => $this->t('API request messages'),
        'response' => $this->t('API response messages'),
      ],
      '#default_value' => $this->configuration['options']['log'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $values = $this->value($form_state->getValue($form['#parents']));

      $this->configuration['api_information']['api_key'] = $values['api_information']['api_key'];
      $this->configuration['options']['packaging'] = $values['options']['packaging'];
      $this->configuration['options']['insurance'] = $values['options']['insurance'];
      $this->configuration['options']['rate_multiplier'] = $values['options']['rate_multiplier'];
      $this->configuration['options']['round'] = $values['options']['round'];
      $this->configuration['options']['log'] = $values['options']['log'];
    }

    parent::submitConfigurationForm($form, $form_state);
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
