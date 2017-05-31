<?php

namespace Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_auspost\Address;
use Drupal\commerce_auspost\ConfigurationException;
use Drupal\commerce_auspost\Forms\ConfigureForm;
use Drupal\commerce_auspost\PostageAssessment\Client;
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
   * The configuration form.
   *
   * @var \Drupal\commerce_auspost\Forms\ConfigureForm
   */
  private $configurationForm;

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
   * @param \Drupal\commerce_auspost\Forms\ConfigureForm $configurationForm
   *   The configuration form.
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
    ConfigureForm $configurationForm,
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

    $configurationForm->setShippingInstance($this);
    $this->configurationForm = $configurationForm;
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
      new ConfigureForm(),
      $container->get('commerce_auspost.postage_assessment.services'),
      $container->get('commerce_auspost.postage_assessment.client')
    );
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
    if (!$this->configurationForm->isConfigured()) {
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
    return $this->configurationForm->buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configurationForm->submitForm($form, $form_state);
    parent::submitConfigurationForm($form, $form_state);
  }

}
