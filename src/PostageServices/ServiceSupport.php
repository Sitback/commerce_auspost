<?php

namespace Drupal\commerce_auspost\PostageServices;

use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDefinitionDefaults;
use Drupal\commerce_auspost\PostageServices\ServiceDefinitions\ServiceDestinations;
use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\physical\Volume;
use Drupal\physical\VolumeUnit;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;

/**
 * Defines some AusPost service support helpers.
 *
 * @package Drupal\commerce_auspost\PostageAssessment
 */
class ServiceSupport implements ServiceSupportInterface {

  /**
   * {@inheritdoc}
   */
  public function getMaxParcelDimensions($destination) {
    $dimensions = ServiceDefinitionDefaults::maxParcelDimensions();

    if (array_key_exists($destination, $dimensions)) {
      return $dimensions[$destination];
    }

    throw new ServiceSupportException(
      "Unknown package destination '{$destination}'."
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateParcelCubicWeight(Volume $volume) {
    // Calculate cubic weight as per AusPost guidelines.
    $cubicWeightNumber = bcmul(
      ServiceDefinitionDefaults::CUBIC_WEIGHT_DENSITY,
      $volume->convert(VolumeUnit::CUBIC_METER)->getNumber()
    );
    return new Weight($cubicWeightNumber, WeightUnit::KILOGRAM);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateParcelWeight(Volume $volume, Weight $weight) {
    // Standardise weight unit.
    $weight = $weight->convert(WeightUnit::KILOGRAM);
    $cubicWeight = $this->calculateParcelCubicWeight($volume);
    $oneKg = new Weight('1', WeightUnit::KILOGRAM);

    // Use cubic weight if it's greater than the parcel's weight.
    if ($weight->greaterThan($oneKg) &&  $cubicWeight->greaterThan($weight)) {
      $weight = $cubicWeight;
    }

    return $weight;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePackageSize(
    Length $length,
    Length $width,
    Length $height,
    $destination
  ) {
    $isDomestic = $destination === ServiceDestinations::DOMESTIC;
    /** @var \Drupal\physical\Measurement[] $max */
    $max = $this->getMaxParcelDimensions($destination);

    // Convert all units where required.
    $length = $length->convert(LengthUnit::CENTIMETER);
    $width = $width->convert(LengthUnit::CENTIMETER);
    $height = $height->convert(LengthUnit::CENTIMETER);

    // All destinations have length requirements, confirm that no edge of the
    // package exceeds this.
    if (
      $max['length']->lessThan($width) ||
      $max['length']->lessThan($length) ||
      $max['length']->lessThan($height)
    ) {
      throw new PackageSizeException(
        "Package width, length or height are greater than the AusPost maximum of '{$max['length']}'."
      );
    }

    // Domestic packages have volume requirements.
    if ($isDomestic) {
      // Volume is calculated in m^3.
      $widthNumber = $width->convert(LengthUnit::METER)
        ->getNumber();
      $heightNumber = $height->convert(LengthUnit::METER)
        ->getNumber();

      $volumeCalc = $length->convert(LengthUnit::METER)
        ->multiply($widthNumber)
        ->multiply($heightNumber);
      $volume = new Volume($volumeCalc->getNumber(), VolumeUnit::CUBIC_METER);

      if ($max['volume']->lessThan($volume)) {
        throw new PackageSizeException(
          "Package volume ({$volume}) is greater than the AusPost maximum of '{$max['volume']}'."
        );
      }

      // Make sure the package doesn't have a cubic weight greater than the
      // AusPost guidelines.
      $cubicWeight = $this->calculateParcelCubicWeight($volume);
      if ($max['weight']->lessThan($cubicWeight)) {
        throw new PackageSizeException(
          "Package cubic weight ({$cubicWeight}) is greater than the AusPost maximum of '{$max['weight']}'."
        );
      }
    }
    else {
      // International packages have girth requirements,
      // (where girth = ((width + height) * 2)).
      // Each package edge combination needs to be checked.
      $girthVariations = [
        ['width', 'height'],
        ['width', 'length'],
        ['length', 'height'],
      ];
      // @codingStandardsIgnoreStart (coder thinks dynamic vars are undefined)
      foreach ($girthVariations as list($fieldA, $fieldB)) {
        /** @var \Drupal\physical\Length $fieldAVal */
        $fieldAVal = ${$fieldA};
        /** @var \Drupal\physical\Length $fieldBVal */
        $fieldBVal = ${$fieldB};

        $girth = $fieldAVal->add($fieldBVal)
          ->multiply('2');

        if ($max['girth']->lessThan($girth)) {
          throw new PackageSizeException(
            "Package girth ({$girth}, calculated using '{$fieldA}' and '{$fieldB}') is greater than the AusPost maximum of '{$max['girth']}'."
          );
        }
      }
      // @codingStandardsIgnoreEnd
    }

    return TRUE;
  }

}
