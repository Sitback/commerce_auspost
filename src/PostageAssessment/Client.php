<?php

namespace Drupal\commerce_auspost\PostageAssessment;

use Auspost\Common\Auspost;
use Guzzle\Http\Exception\ClientErrorResponseException;

/**
 * Defines an AusPost PAC client.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class Client {

  /**
   * AusPost PAC API key.
   *
   * @see https://developers.auspost.com.au/apis/pac/getting-started
   *
   * @var string
   */
  private $apiKey;

  /**
   * API client.
   *
   * @var \Auspost\Postage\PostageClient
   */
  private $client;

  /**
   * Request constructor.
   *
   * @param NULL $apiKey
   *   AusPost PAC API key.
   */
  public function __construct($apiKey = NULL) {
    $this->apiKey = $apiKey;
  }

  /**
   * Set API key.
   *
   * @param string $key
   *   API key to set.
   *
   * @return $this
   * @throws \Drupal\commerce_auspost\PostageAssessment\ClientException
   */
  public function setApiKey($key) {
    if (empty($key)) {
      throw new ClientException('Please provide a valid AusPost PAC API key.');
    }

    $this->apiKey = $key;
    return $this;
  }

  /**
   * Get the AusPost Postage client, instantiating if required.
   *
   * @return \Auspost\Postage\PostageClient
   * @throws \Drupal\commerce_auspost\PostageAssessment\ClientException
   */
  public function getClient() {
    if ($this->apiKey === NULL) {
      throw new ClientException('No API key provided, please set one.');
    }

    if ($this->client === NULL) {
      $this->client = Auspost::factory([
        'developer_mode' => true,
        'auth_key' => $this->apiKey,
      ])->get('postage');
    }

    return $this->client;
  }

  public function calculatePostage(Request $request) {
    $address = $request->getAddress();
    $dimensions = $request->getDimensions();
    $opts = [
      'service_code' => $request->getServiceCode(),
    ] + $dimensions;

    if ($request->isDomestic()) {
      $opts['from_postcode'] = $address->getShipperPostcode();
      $opts['to_postcode'] = $address->getRecipientPostcode();
    } else {
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
    } else {
      $method .= 'International';
    }
    if ($request->isParcel()) {
      $method .= 'Parcel';
    } else {
      $method .= 'Letter';
    }
    $method .= 'Postage';

    // Errors are thrown but caught by the caller.
    $response = $this->getClient()->{$method}($opts);
    return $response['postage_result']['total_cost'];
  }

}