<?php

namespace Drupal\commerce_auspost\Packer\ShipmentPacking;

use Drupal\commerce_auspost\PostageServices\PackageSizeException;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions;
use Drupal\commerce_auspost\PostageServices\ServiceSupport;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use DVDoug\BoxPacker\Box;

/**
 * Wraps a Commerce package type, allowing it to be packed by the packer.
 *
 * @package Drupal\commerce_auspost\Packer\ShipmentPacking
 */
class PackableCommercePackageType implements Box {

  /**
   * Service support helpers.
   *
   * @var \Drupal\commerce_auspost\PostageServices\ServiceSupport
   */
  private $serviceSupport;

  /**
   * The Commerce package type definition.
   *
   * @var array
   */
  private $packageType;

  /**
   * Package destination.
   *
   * @var string
   */
  private $destination;

  /**
   * CommercePackageTypeBox constructor.
   *
   * @param \Drupal\commerce_auspost\PostageServices\ServiceSupport $support
   *   Service support helpers.
   * @param array $packageType
   *   The Commerce package type definition.
   * @param string $destination
   *   Package destination
   *
   * @throws \Drupal\commerce_auspost\Packer\ShipmentPacking\ShipmentPackerException
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   */
  public function __construct(
    ServiceSupport $support,
    array $packageType,
    $destination
  ) {
    $requiredKeys = [
      'label',
      'dimensions',
      'dimensions.length',
      'dimensions.width',
      'dimensions.height',
      'dimensions.unit',
      'weight',
      'weight.number',
      'weight.unit',
    ];
    foreach ($requiredKeys as $key) {
      if (!$this->arrayValueExists($key, $packageType)) {
        throw new ShipmentPackerException(
          "Required package type value '{$key}' does not exist."
        );
      }
    }

    $this->serviceSupport = $support;
    $this->packageType = $packageType;
    $this->destination = $destination;

    // Confirm that package volume and/or girth are within spec.
    $this->assertPackageMeetsGuidelines();
  }

  /**
   * {@inheritdoc}
   */
  public function getReference() {
    $label = $this->packageType['label'];
    if ($label instanceof TranslatableMarkup) {
      $label = $label->render();
    }
    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function getOuterWidth() {
    // Commerce doesn't support packaging wall thicknesses so we assume that
    // this is equivalent to the inner dimension.
    return $this->getInnerWidth();
  }

  /**
   * {@inheritdoc}
   */
  public function getOuterLength() {
    // Commerce doesn't support packaging wall thicknesses so we assume that
    // this is equivalent to the inner dimension.
    return $this->getInnerLength();
  }

  /**
   * {@inheritdoc}
   */
  public function getOuterDepth() {
    // Commerce doesn't support packaging wall thicknesses so we assume that
    // this is equivalent to the inner dimension.
    return $this->getInnerDepth();
  }

  /**
   * {@inheritdoc}
   */
  public function getEmptyWeight() {
    $weight = new Weight(
      $this->packageType['weight']['number'],
      $this->packageType['weight']['unit']
    );
    return (int) ceil(
      (float) $weight->convert(WeightUnit::GRAM)->getNumber()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInnerWidth() {
    return $this->getConvertedDimension('width');
  }

  /**
   * {@inheritdoc}
   */
  public function getInnerLength() {
    return $this->getConvertedDimension('length');
  }

  /**
   * {@inheritdoc}
   */
  public function getInnerDepth() {
    return $this->getConvertedDimension('height');
  }

  /**
   * {@inheritdoc}
   */
  public function getInnerVolume() {
    // Use bcmath to calculate volume.
    $width = $this->getConvertedDimension('width', FALSE);
    $length = $this->getConvertedDimension('length', FALSE);
    $height = $this->getConvertedDimension('height', FALSE);

    $volume = bcmul($width, bcmul($length, $height, 0), 0);
    return (int) $volume;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   */
  public function getMaxWeight() {
    if ($this->isDomestic) {
      $dimensions = $this->serviceSupport->getMaxParcelDimensions(
        ServiceDefinitions::SERVICE_DEST_DOMESTIC
      );
    } else {
      $dimensions = $this->serviceSupport->getMaxParcelDimensions(
        ServiceDefinitions::SERVICE_DEST_INTERNATIONAL
      );
    }

    /** @var \Drupal\physical\Measurement[] $dimensions */
    return $dimensions['weight']->convert(WeightUnit::GRAM)
      ->getNumber();
  }

  /**
   * Check an array value via dot notation. Only supports one level deep.
   *
   * @param string $key
   *   Array key.
   * @param array $array
   *   Array to check.
   *
   * @return bool
   *   TRUE if value exists, FALSE otherwise.
   */
  private function arrayValueExists($key, array $array) {
    $hasDot = strpos($key, '.') !== FALSE;

    if (!$hasDot) {
      return array_key_exists($key, $array);
    }

    // This only supports one dot deep for simplicity's sake.
    $keys = explode('.', $key);

    return (
      array_key_exists($keys[0], $array) &&
      array_key_exists($keys[1], $array[$keys[0]])
    );
  }

  /**
   * Gets a package dimension value, in mm.
   *
   * @param string $dimension
   *   Dimension name.
   * @param bool $returnInt
   *   If TRUE, will convert the length to an int (rounding up where required),
   *   or if FALSE, will return the raw measurement.
   *
   * @return int|\Drupal\physical\Measurement
   *   The dimension value in mm.
   */
  private function getConvertedDimension($dimension, $returnInt = TRUE) {
    $length = (new Length(
      $this->packageType['dimensions'][$dimension],
      $this->packageType['dimensions']['unit']
    ))->convert(LengthUnit::MILLIMETER);

    // Round up where required.
    if ($returnInt) {
      return (int) ceil((float) $length->getNumber());
    }

    return $length;
  }

  /**
   * Confirm that the package type in use meet's AusPosts size guidelines.
   *
   * @see https://auspost.com.au/parcels-mail/postage-tips-guides/size-weight-guidelines
   *
   * @throws \Drupal\commerce_auspost\PostageServices\ServiceSupportException
   * @throws \Drupal\commerce_auspost\Packer\ShipmentPacking\ShipmentPackerException
   */
  private function assertPackageMeetsGuidelines() {
    /** @var \Drupal\physical\Length $width */
    $width = $this->getConvertedDimension('width', FALSE);
    /** @var \Drupal\physical\Length $length */
    $length = $this->getConvertedDimension('length', FALSE);
    /** @var \Drupal\physical\Length $height */
    $height = $this->getConvertedDimension('height', FALSE);

    try {
      $reason = '';
      $sizeValid = $this->serviceSupport->validatePackageSize(
        $length,
        $width,
        $height,
        $this->destination
      );
    }
    catch (PackageSizeException $e) {
      $sizeValid = FALSE;
      $reason = $e->getMessage();
    }

    if (!$sizeValid) {
      $type = $this->getReference();
      throw new ShipmentPackerException(
        "Package type '{$type}' exceeds Australia Post's maximum size guidelines, see: https://auspost.com.au/parcels-mail/postage-tips-guides/size-weight-guidelines. Reason: {$reason}"
      );
    }
  }

}
