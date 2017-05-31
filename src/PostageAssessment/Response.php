<?php

namespace Drupal\commerce_auspost\PostageAssessment;

/**
 * Defines an AusPost PAC API response.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class Response implements ResponseInterface {

  /**
   * PAC API request.
   *
   * @var \Drupal\commerce_auspost\PostageAssessment\RequestInterface
   */
  private $request;

  /**
   * Raw API response from the AusPost library.
   *
   * @var array
   */
  private $response;

  /**
   * {@inheritdoc}
   */
  public function setRequest(RequestInterface $request) {
    $this->request = $request;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * {@inheritdoc}
   */
  public function setResponse(array $response) {
    $this->response = $response;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * {@inheritdoc}
   */
  public function getPostage() {
    if ($this->response === NULL) {
      throw new ResponseException('No API response is set.');
    }

    if (!array_key_exists('postage_result', $this->response)) {
      throw new ResponseException('API response does not include a valid result.');
    }

    if (!array_key_exists('total_cost', $this->response['postage_result'])) {
      throw new ResponseException('API response does not include a total cost.');
    }

    return (float) $this->response['postage_result']['total_cost'];
  }

}
