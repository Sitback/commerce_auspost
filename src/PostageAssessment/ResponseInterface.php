<?php

namespace Drupal\commerce_auspost\PostageAssessment;

/**
 * Defines an interface for a PAC API response.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
interface ResponseInterface {

  /**
   * Set PAC request.
   *
   * @param \Drupal\commerce_auspost\PostageAssessment\RequestInterface $request
   *   API request.
   *
   * @return $this;
   */
  public function setRequest(RequestInterface $request);

  /**
   * Get original API request.
   *
   * @return \Drupal\commerce_auspost\PostageAssessment\RequestInterface
   *   API request.
   */
  public function getRequest();

  /**
   * Set raw API response from the AusPost library
   *
   * @param $response array
   *   Raw API response from the AusPost library.
   *
   * @return $this
   */
  public function setResponse(array $response);

  /**
   * Get raw API response.
   *
   * @return array
   *   Raw API response from the AusPost library.
   */
  public function getResponse();

  /**
   * Get postage cost.
   *
   * @return float
   *   Postage cost.
   *
   * @throws \Drupal\commerce_auspost\PostageAssessment\ResponseException
   *   If API response doesn't exist or could not be parsed.
   */
  public function getPostage();

}
