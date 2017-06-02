<?php

namespace Drupal\commerce_auspost\PostageAssessment;

use Auspost\Common\Auspost;

/**
 * Defines an AusPost PAC client.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class Client implements ClientInterface {

  /**
   * AusPost PAC API key.
   *
   * @var string
   *
   * @see https://developers.auspost.com.au/apis/pac/getting-started
   */
  private $apiKey;

  /**
   * API client.
   *
   * @var \Auspost\Postage\PostageClient
   */
  private $client;

  /**
   * {@inheritdoc}
   */
  public function __construct($apiKey = NULL) {
    $this->apiKey = $apiKey;
  }

  /**
   * {@inheritdoc}
   */
  public function setApiKey($key) {
    if (empty($key)) {
      throw new ClientException('Please provide a valid AusPost PAC API key.');
    }

    $this->apiKey = $key;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getClient() {
    if ($this->apiKey === NULL) {
      throw new ClientException('No API key provided, please set one.');
    }

    if ($this->client === NULL) {
      $this->client = Auspost::factory([
        'developer_mode' => TRUE,
        'auth_key' => $this->apiKey,
      ])->get('postage');
    }

    return $this->client;
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePostage(RequestInterface $request) {
    $address = $request->getAddress();
    $dimensions = $request->getDimensions();
    $opts = [
      'service_code' => $request->getServiceCode(),
    ] + $dimensions;

    if ($request->isDomestic()) {
      $opts['from_postcode'] = $address->getShipperPostcode();
      $opts['to_postcode'] = $address->getRecipientPostcode();
    }
    else {
      $opts['country_code'] = $address->getRecipientCountrycode();
    }

    $extraOpts = [];

    // Translate from AusPost key name to the key name used by the API library.
    $optKeyMapping = [
      'sub_opt_code' => 'suboption_code',
    ];

    foreach ($request->getExtraServiceOptions() as $optKey => $optValue) {
      if (array_key_exists($optKey, $optKeyMapping)) {
        $optKey = $optKeyMapping[$optKey];
      }

      $extraOpts[$optKey] = $optValue;
    }

    $opts += $extraOpts;

    // Figure out method call.
    $method = 'calculate';
    if ($request->isDomestic()) {
      $method .= 'Domestic';
    }
    else {
      $method .= 'International';
    }
    if ($request->isParcel()) {
      $method .= 'Parcel';
    }
    else {
      $method .= 'Letter';
    }
    $method .= 'Postage';

    // Errors are thrown but caught by the caller.
    $apiResponse = $this->getClient()->{$method}($opts);

    return (new Response())
      ->setRequest($request)
      ->setResponse($apiResponse);
  }

}
