<?php

namespace Drupal\commerce_auspost\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_auspost\Address;
use Drupal\commerce_auspost\ConfigurationException;
use Drupal\commerce_auspost\Forms\ConfigureForm;
use Drupal\commerce_auspost\Packer\ShipmentPacking\ShipmentPackerException;
use Drupal\commerce_auspost\PostageAssessment\ClientInterface;
use Drupal\commerce_auspost\PostageAssessment\RequestInterface;
use Drupal\commerce_auspost\PostageAssessment\ResponseException;
use Drupal\commerce_auspost\PostageAssessment\ResponseInterface;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitionManager;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDestinations;
use Drupal\commerce_auspost\PostageServices\ServiceSupport;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Error;
use DVDoug\BoxPacker\ItemTooLargeException;
use Exception;
use Guzzle\Http\Exception\ClientErrorResponseException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Australia Post shipping method.
 *
 * @codingStandardsIgnoreStart
 * @CommerceShippingMethod(
 *   id = "auspost",
 *   label = @Translation("Australia Post"),
 *   services = {
 *     "commerce_auspost_service_definition:AUS_SERVICE_OPTION_STANDARD" =
 *        @Translation("Australia Post Standard Post - 2-6 Days"),
 *     "commerce_auspost_service_definition:AUS_SERVICE_OPTION_SIGNATURE" =
 *        @Translation("Australia Post Standard Post, Signature required - 2-6 Days"),
 *     "commerce_auspost_service_definition:AUS_SERVICE_OPTION_INS" =
 *        @Translation("Australia Post Standard Post (Insured) - 2-6 Days"),
 *     "commerce_auspost_service_definition:AUS_SERVICE_OPTION_SIG_INS" =
 *        @Translation("Australia Post Standard Post (Insured), Signature required - 2-6 Days"),
 *     "commerce_auspost_service_definition:AUS_PARCEL_EXPRESS" =
 *        @Translation("Australia Post Express Post - 1-3 Days"),
 *     "commerce_auspost_service_definition:AUS_PARCEL_EXPRESS_SIGNATURE" =
 *        @Translation("Australia Post Express Post, Signature required - 1-3 Days"),
 *     "commerce_auspost_service_definition:AUS_PARCEL_EXPRESS_INS" =
 *        @Translation("Australia Post Express Post (Insured) - 1-3 Days"),
 *     "commerce_auspost_service_definition:AUS_PARCEL_EXPRESS_SIG_INS" =
 *        @Translation("Australia Post Express Post (Insured), Signature required - 1-3 Days"),
 *     "commerce_auspost_service_definition:AUS_PARCEL_COURIER" =
 *        @Translation("Australia Post Courier Post - Same Day Delivery"),
 *     "commerce_auspost_service_definition:AUS_PARCEL_COUR_INS" =
 *        @Translation("Australia Post Courier Post (Insured) - Same Day Delivery"),
 *     "commerce_auspost_service_definition:INT_PARCEL_SEA_OWN_PACKAGING" =
 *        @Translation("Australia Post International Economy Sea - 30+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_SEA_OWN_PACK_SIG" =
 *        @Translation("Australia Post International Economy Sea, Signature required - 30+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_SEA_OWN_PACK_INS" =
 *        @Translation("Australia Post International Economy Sea (Insured) - 30+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_AIR_OWN_PACKAGING" =
 *        @Translation("Australia Post International Economy Air - 10+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_AIR_OWN_PACK_SIG" =
 *        @Translation("Australia Post International Economy Air, Signature required - 10+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_AIR_OWN_PACK_INS" =
 *        @Translation("Australia Post International Economy Air (Insured) - 10+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_STD_OWN_PACKAGING" =
 *        @Translation("Australia Post International Standard - 6+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_STD_OWN_PACK_SIG" =
 *        @Translation("Australia Post International Standard, Signature required - 6+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_STD_OWN_PACK_INS" =
 *        @Translation("Australia Post International Standard (Insured) - 6+ Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_EXP_OWN_PACKAGING" =
 *        @Translation("Australia Post International Express - 2-4 Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_EXP_OWN_PACK_INS" =
 *        @Translation("Australia Post International Express (Insured) - 2-4 Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_COR_OWN_PACKAGING" =
 *        @Translation("Australia Post International Courier - 1-2 Days"),
 *     "commerce_auspost_service_definition:INT_PARCEL_COR_OWN_PACK_INS" =
 *        @Translation("Australia Post International Courier (Insured) - 1-2 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_SM" =
 *        @Translation("Australia Post Standard Letter - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_SM_PRIORITY" =
 *        @Translation("Australia Post Standard Letter Priority - 1-4 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_LG" =
 *        @Translation("Australia Post Standard Letter - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_LG_PRIORITY" =
 *        @Translation("Australia Post Standard Letter Priority - 1-4 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_SM_REG_POST" =
 *        @Translation("Australia Post Registered Post Letter - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_SM_REG_CONF" =
 *        @Translation("Australia Post Registered Post Letter - Confirmation - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_SM_REG_P2P" =
 *        @Translation("Australia Post Registered Post Letter - Person to Person - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_LG_REG_POST" =
 *        @Translation("Australia Post Registered Post Letter Large - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_LG_REG_POST_CONF" =
 *        @Translation("Australia Post Registered Post Letter Large - Confirmation - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_LG_REG_P2P" =
 *        @Translation("Australia Post Registered Post Letter - Person to Person - 2-6 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_SM_EXP_POST" =
 *        @Translation("Australia Post Express Post Envelope Small - 1-3 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_SM_EXP_SIG" =
 *        @Translation("Australia Post Express Post Envelope Small - Signature - 1-3 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_MD_EXP" =
 *        @Translation("Australia Post Express Post Envelope Medium - 1-3 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_MD_EXP_SIG" =
 *        @Translation("Australia Post Express Post Envelope Medium - Signature - 1-3 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_LG_EXPRESS_POST" =
 *        @Translation("Australia Post Express Post Envelope Large - 1-3 Days"),
 *     "commerce_auspost_service_definition:L_AUS_LETTER_LG_EXP_POST_SIG" =
 *        @Translation("Australia Post Express Post Envelope Large - Signature - 1-3 Days"),
 *     "commerce_auspost_service_definition:L_INTL_SERVICE_AIR_MAIL_LGT" =
 *        @Translation("Australia Post Air Mail Light - 6+ Days"),
 *     "commerce_auspost_service_definition:L_INTL_SERVICE_AIR_MAIL_MED" =
 *        @Translation("Australia Post Air Mail Medium - 6+ Days"),
 *     "commerce_auspost_service_definition:L_INTL_SERVICE_AIR_MAIL_HVY" =
 *        @Translation("Australia Post Air Mail Heavy - 6+ Days"),
 *     "commerce_auspost_service_definition:L_INT_LETTER_REG_SMALL" =
 *        @Translation("Australia Post International Registered Prepaid DL Envelope - 6+ Days"),
 *     "commerce_auspost_service_definition:L_INT_LETTER_REG_LARGE" =
 *        @Translation("Australia Post International Registered Prepaid B4 Envelope - 6+ Days"),
 *     "commerce_auspost_service_definition:L_INT_LET_EXP_OWN_PKG" =
 *        @Translation("Australia Post International Express Letter - 2+ Days"),
 *     "commerce_auspost_service_definition:L_INT_LET_EXP_OWN_PKG_INS" =
 *        @Translation("Australia Post International Express Letter (Insured) - 2+ Days"),
 *     "commerce_auspost_service_definition:L_INT_LET_COR_OWN_PKG" =
 *        @Translation("Australia Post International Courier Letter - 2+ Days"),
 *     "commerce_auspost_service_definition:L_INT_LET_COR_OWN_PKG_INS" =
 *        @Translation("Australia Post International Courier Letter (Insured) - 2+ Days")
 *   }
 * )
 * @codingStandardsIgnoreEnd
 */
class AusPost extends ShippingMethodBase {

  // Currency code.
  const AUD_CURRENCY_CODE = 'AUD';

  /**
   * Service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $container;

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
   * The service definition manager.
   *
   * @var \Drupal\commerce_auspost\PostageServices\ServiceDefinitionManager
   */
  private $serviceDefinitionManager;

  /**
   * AusPost client.
   *
   * @var \Drupal\commerce_auspost\PostageAssessment\ClientInterface
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
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $packageTypeManager
   *   The package type manager.
   * @param \Psr\Log\LoggerInterface $watchdog
   *   Watchdog logger.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The price rounder.
   * @param \Drupal\commerce_auspost\Forms\ConfigureForm $configurationForm
   *   The configuration form.
   * @param \Drupal\commerce_auspost\PostageServices\ServiceSupport $serviceSupport
   *   AusPost PAC supported services.
   * @param \Drupal\commerce_auspost\PostageServices\ServiceDefinitionManager $serviceManager
   *   The service definition manager.
   * @param \Drupal\commerce_auspost\PostageAssessment\ClientInterface $client
   *   AusPost PAC client.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ContainerInterface $container,
    PackageTypeManagerInterface $packageTypeManager,
    LoggerInterface $watchdog,
    RounderInterface $rounder,
    ConfigureForm $configurationForm,
    ServiceSupport $serviceSupport,
    ServiceDefinitionManager $serviceManager,
    ClientInterface $client
  ) {
    $this->container = $container;
    $this->watchdog = $watchdog;
    $this->rounder = $rounder;
    $this->serviceDefinitionManager = $serviceManager;
    $this->client = $client;

    $configurationForm->setShippingInstance($this)
      ->setServiceSupport($serviceSupport);
    $this->configurationForm = $configurationForm;

    parent::__construct(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $packageTypeManager
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
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
      $container,
      $container->get('plugin.manager.commerce_package_type'),
      $container->get('logger.channel.commerce_auspost'),
      $container->get('commerce_price.rounder'),
      new ConfigureForm(),
      $container->get('commerce_auspost.postage_services.service_support'),
      $container->get('commerce_auspost.postage_services.service_manager'),
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
   */
  private function logApi($message, $data, $level = LogLevel::INFO) {
    $doLog = FALSE;
    $config = $this->configuration;
    if ($data instanceof RequestInterface &&
        array_key_exists('request', $config['options']['log'])) {
      $doLog = TRUE;
    }
    elseif ($data instanceof ResponseInterface &&
              array_key_exists('response', $config['options']['log'])) {
      $doLog = TRUE;
    }

    if ($doLog) {
      $this->watchdog->log($level, "$message <br>@details", [
        '@details' => json_encode($data),
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
    }
    else {
      $message = $defaultMessage;
    }

    $this->watchdog->error($message, $variables);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\commerce_auspost\PostageAssessment\ClientException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\commerce_auspost\ConfigurationException
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
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

    // Calculate postage for all services.
    $rates = [];
    $serviceDefinitions = $this->serviceDefinitionManager->getDefinitions();
    foreach ($serviceDefinitions as $serviceId => $serviceConfig) {
      // Skip services that aren't enabled.
      if (!in_array($serviceId, $this->configuration['services'], TRUE)) {
        continue;
      }

      /** @var \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface $serviceDefinition */
      $serviceDefinition = $this->serviceDefinitionManager->createInstance(
        $serviceId,
        $serviceConfig
      );

      $packageTypes = $this->getEnabledPackageTypes(
        $serviceDefinition->getDestination()
      );
      $postagePrice = new Price(0, static::AUD_CURRENCY_CODE);

      try {
        $packedBoxes = $this->getPackedBoxes(
          $packageTypes,
          $shipment->getOrder()->getItems(),
          $serviceDefinition
        );
      }
      catch (ItemTooLargeException $e) {
        $this->logException($e, 'No package type large enough could be found.');
        continue;
      }

      foreach ($packedBoxes as $packedBox) {
        try {
          $request = $this->container->get('commerce_auspost.postage_assessment.request')
            ->setAddress($address)
            ->setShipment($shipment)
            ->setPackedBox($packedBox)
            ->setPackageType($serviceDefinition->getServiceType())
            ->setServiceDefinition($serviceDefinition);
          // Log request if enabled.
          $this->logApi('Sending AusPost PAC API request', $request);

          $response = $this->client->calculatePostage($request);

          // Log response as well.
          $this->logApi('Received AusPost PAC API response', $response);

          $postage = (string) $response->getPostage();
        }
        catch (ClientErrorResponseException $e) {
          $this->logException($e, 'Error fetching rates from AusPost.');
          // Skip this service.
          continue 2;
        }
        catch (ResponseException $e) {
          $this->logException($e, 'Error fetching rates from AusPost.');
          // Skip this service.
          continue 2;
        }

        // Apply any modifiers to the postage cost if necessary.
        $postagePrice = $postagePrice->add(
          $this->getModifiedPostageCost(
            new Price($postage, static::AUD_CURRENCY_CODE)
          )
        );
      }

      // Use the title off the service definition instead (it allows it to be
      // altered).
      $service = new ShippingService(
        $serviceDefinition->getServiceId(),
        $serviceDefinition->getLabel()
      );

      $rates[] = new ShippingRate(
        $serviceId,
        $service,
        $postagePrice
      );
    }

    return $rates;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = [
      'enabled_package_types' => [],
      'api_information' => [
        'api_key' => '',
      ],
      'options' => [
        'insurance' => FALSE,
        'rate_multiplier' => 1.0,
        'round' => PHP_ROUND_HALF_UP,
        'log' => [],
      ],
    ];

    foreach (ServiceDestinations::getAll() as $dest) {
      $defaults['enabled_package_types'][$dest] = [];
    }

    return $defaults + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\commerce_auspost\ConfigurationException
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

  /**
   * Get all possible package types for this shipping method.
   *
   * This includes disabled package types.
   *
   * @param string $dest
   *   An (optional) package destination (one of 'domestic' or 'international)
   *   to filter by. If not specified, all package types are returned.
   *
   * @return array
   *   A list of package type definitions.
   *
   * @throws \Drupal\commerce_auspost\ConfigurationException
   */
  public function getPossiblePackageTypes($dest = NULL) {
    $types = $this->packageTypeManager->getDefinitionsByShippingMethod(
      $this->getPluginId()
    );
    if ($dest === NULL) {
      return $types;
    }

    try {
      ServiceDestinations::assertExists($dest);
    }
    catch (InvalidArgumentException $e) {
      throw new ConfigurationException("Unknown package destination '{$dest}'.");
    }

    // Filter out any destination-specific packages.
    return array_filter($types, function ($typeKey) use ($dest) {
      // Ignore any custom types.
      if (strpos($typeKey, 'commerce_auspost') !== 0) {
        return TRUE;
      }

      // Strip out the prefix then check if the destination is valid for this
      // package type.
      // @see commerce_auspost.commerce_package_types.yml for all our package
      // types.
      $prefix = 'commerce_auspost:';
      $typeKey = substr($typeKey, strlen($prefix));
      return strpos($typeKey, $dest) === 0;
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * Get all enabled package types for this shipping method.
   *
   * @param string $dest
   *   An (optional) package destination (one of 'domestic' or 'international)
   *   to filter by. If not specified, all enabled package types are returned.
   *
   * @return array
   *   A list of enabled package type definitions.
   *
   * @throws \Drupal\commerce_auspost\ConfigurationException
   */
  public function getEnabledPackageTypes($dest = NULL) {
    // Assume all types are fair game unless told otherwise.
    $types = $this->getPossiblePackageTypes($dest);

    if ($dest !== NULL && !empty($this->configuration['enabled_package_types'][$dest])) {
      $types = array_intersect_key(
        $types,
        array_filter($this->configuration['enabled_package_types'][$dest])
      );
    }

    return $types;
  }

  /**
   * Packs order items into boxes and returns the packed boxes.
   *
   * @param array[] $packageTypes
   *   A list package type to use for packing.
   * @param \Drupal\commerce_order\Entity\OrderItemInterface[] $orderItems
   *   A list of purchased order items.
   * @param \Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionInterface $service
   *   Shipping service definition.
   *
   * @return \Drupal\commerce_auspost\Packer\ShipmentPacking\PackedBox[]
   *   A list of packed boxes.
   *
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
   */
  private function getPackedBoxes(
    array $packageTypes,
    array $orderItems,
    ServiceDefinitionInterface $service
  ) {
    $packer = $this->container->get('commerce_auspost.shipment_packer');

    // Get rates for each packed box.
    foreach ($packageTypes as $packageType) {
      try {
        $packer->addPackageType($packageType, $service->getDestination());
      }
      catch (ShipmentPackerException $e) {
        $this->logException($e, 'Invalid package type skipped.');
        // Ignore invalid packages.
        continue;
      }
    }

    // Add order items multiple times depending on quantity.
    foreach ($orderItems as $orderItem) {
      $quantity = (int) $orderItem->getQuantity();
      for ($i = 0; $i < $quantity; $i++) {
        $packer->addOrderItem($orderItem);
      }
    }

    return $packer->pack();
  }

  /**
   * Applies any multipliers or roundings to the raw postage cost.
   *
   * @param \Drupal\commerce_price\Price $postage
   *   Raw postage cost.
   *
   * @see \Drupal\commerce_price\RounderInterface::round()
   * @see \Drupal\commerce_price\Price::multiply()
   *
   * @return \Drupal\commerce_price\Price
   *   Tweaked postage price.
   *
   * @throws \InvalidArgumentException
   */
  private function getModifiedPostageCost(Price $postage) {
    // Get rounding and multiplier from configuration.
    $multiplier = 1.0;
    if (!empty($this->configuration['options']['rate_multiplier'])) {
      $multiplier = (float) $this->configuration['options']['rate_multiplier'];
    }
    $rounding = PHP_ROUND_HALF_UP;
    if (!empty($this->configuration['options']['round'])) {
      $rounding = (int) $this->configuration['options']['round'];
    }

    if ($multiplier > 1) {
      $postage = $postage->multiply((string) $multiplier);
    }

    $postage = $this->rounder->round($postage, $rounding);

    return $postage;
  }

}
