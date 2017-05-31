<?php

namespace Drupal\commerce_auspost\PostageAssessment;

/**
 * Defines an interface for AusPost PAC clients.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
interface ClientInterface {

  /**
   * Client constructor.
   *
   * @param NULL $apiKey
   *   AusPost PAC API key.
   */
  public function __construct($apiKey = NULL);

  /**
   * Set AusPost PAC API key.
   *
   * @param string $key
   *   API key to set.
   *
   * @return $this
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\ClientException
   *   If the API key is not valid.
   */
  public function setApiKey($key);

  /**
   * Get the AusPost Postage client, instantiating if required.
   *
   * @return \Auspost\Postage\PostageClient
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\ClientException
   *   If an error occurs while instantiating the client.
   */
  public function getClient();

  /**
   * Get postage cost from AusPost.
   *
   * @param \Drupal\commerce_auspost\PostageAssessment\RequestInterface $request
   *   The request to send to AusPost.
   *
   * @return string
   *   Postage cost from AusPost
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\commerce_auspost\PostageAssessment\ClientException
   * @throws \Drupal\commerce_auspost\PostageAssessment\RequestException
   */
  public function calculatePostage(RequestInterface $request);

}
