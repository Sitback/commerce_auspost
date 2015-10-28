<?php

/**
 * @file
 * Packaging strategy. Packages all products in one package, break by weight.
 *
 * Puts all products into packages, subject only to package maximum weight.
 *
 * The "All-in-one" strategy is a general-purpose packaging strategy which
 * attempts to put all products into one package, subject only to a maximum
 * weight. When the maximum weight is exceeded, a new package will be created.
 *
 * Products are added to packages one-by-one, in order of weight with the
 * heaviest products added first. If adding a product will exceed the package
 * maximum weight, this strategy looks for available space in any
 * previously-created packages and attempts to add the product to one of those.
 * If the product won't fit into any existing packages, a new package is
 * created.
 *
 * If your product weights are small compared to your maximum package weight
 * this strategy will approximate an optimal packing. However, no attempt is
 * made to optimize packing, so the number of packages returned by this
 * strategy is not guaranteed to be the minimum possible number. This strategy
 * mimics how a human would put products into packages, which is also not
 * guaranteed to be optimal, but is what will occur in practice.
 *
 * This strategy will always return the same results if given the same set of
 * products.
 */

/**
 * Commerce Australia Post Last Fit 2D class.
 */
class CommerceAustraliaPostLastFit2D implements PackagingStrategy {

  /**
   * Implements PackagingStrategy::getDescription().
   */
  public function getDescription() {
    return t("The 'All-in-one' strategy is a general-purpose packaging strategy which attempts to put all products into one package, subject to a maximum weight and default package volume. When the maximum weight or volume is exceeded, a new package will be created.

Products are added to packages one-by-one, in order of weight with the heaviest products added first. If adding a product will exceed the package maximum weight or default package volume, this strategy looks for available space in any previously-created packages and attempts to add the product to one of those.  If the product won't fit into any existing packages, a new package is created.

If your product weights and volume are small compared to your maximum package weight this strategy will approximate an optimal packing. However, no attempt is made to optimize packing, so the number of packages returned by this strategy is not guaranteed to be the minimum possible number. This strategy mimics how a human would put products into packages, which is also not guaranteed to be optimal, but is what will occur in practice.

This strategy will always return the same results if given the same set of products.");
  }

  /**
   * Implements PackagingStrategy::packageProducts().
   */
  public function packageProducts(PackagingContext $context, array $products) {

    // Loop over products.
    foreach ($products as $product) {
      // Get item weight. Weight units are set on a per-product basis, so we
      // convert as necessary in order to perform all calculations in the
      // default weight units.
      $product->setWeight($product->getWeight() * packaging_weight_conversion($product->getWeightUnits(), $context->getDefaultWeightUnits()));
      $product->setWeightUnits($context->getDefaultWeightUnits());
      $product->setVolume($product->getVolume());
      $product->setLengthUnits($context->getDefaultLengthUnits());

    }

    // Sort products from heaviest to lightest to make packaging deterministic.
    // Products have to all have the same weight units for a valid comparison!
    usort($products, 'CommerceAustraliaPostLastFit2D::compareWeights');

    // Create first package.
    $packages = array();
    $package  = new PackagingPackage();
    $package->setWeightUnits($context->getDefaultWeightUnits());
    $package->setLengthUnits($context->getDefaultLengthUnits());

    // Save the package to the array.
    $packages[] = $package;

    // Loop over products.
    foreach ($products as $product) {
      // Get product weight.
      $product_weight = $product->getWeight();
      $product_volume = $product->getVolume();

      if (($context->exceedsMaximumPackageWeight($product_weight)) ||
          ($context->exceedsMaximumPackageVolume($product_volume))) {
        // This product is too heavy to ship - quit with error.
        return array();
      }

      // Loop over quantity of each product.
      for ($item = 0; $item < $product->getQuantity(); $item++) {
        // Try to fit this item into one of the existing packages. If this
        // item is small it might fit into the available space rather than
        // needing a new package.
        $found_space = FALSE;
        foreach ($packages as &$existing_package) {
          // Will it fit?
          if ((!$context->exceedsMaximumPackageWeight($existing_package->getWeight() + $product_weight)) &&
              (!$context->exceedsMaximumPackageVolume($existing_package->getVolume() + $product_volume))) {
            // Yes, update values for this existing package.
            $found_space = TRUE;
            $existing_package->setQuantity($existing_package->getQuantity() + 1);
            $existing_package->setPrice($existing_package->getPrice() + $product->getPrice());
            $existing_package->setVolume($existing_package->getVolume() + $product_volume);
            $existing_package->setWeight($existing_package->getWeight() + $product_weight);
            // Because we've added a product, we need to adjust the weight
            // markup weight of this existing package.
            $existing_package->setShipWeight(packaging_weight_markup($existing_package->getWeight()));
            // Found some free room for this item.
            break;
          }
        }
        if (!$found_space) {
          // If we reach here, the item won't fit into an existing package,
          // so we need to close off the current package and start a new one.
          // Markup weight of current package.
          $package->setShipWeight(packaging_weight_markup($package->getWeight()));

          // Put this item into a new package.
          $package = new PackagingPackage();
          $package->setQuantity(1);
          $package->setPrice($product->getPrice());
          $package->setWeight($product_weight);
          $package->setWeightUnits($context->getDefaultWeightUnits());
          $package->setVolume($product_volume);
          $package->setLengthUnits($context->getDefaultLengthUnits());

          // Save new package to head of the array.
          array_unshift($packages, $package);
        }
      }
    }

    // No more products left to package.
    // Take care of the partially-filled package we were working on.
    // Markup weight of partially-filled package.
    $package->setShipWeight(packaging_weight_markup($package->getWeight()));

    return $packages;
  }

  /**
   * Sorts array of PackagingProduct objects by weight from high to low.
   */
  protected static function compareWeights(PackagingProduct $a, PackagingProduct $b) {
    $weight_a = $a->getWeight();
    $weight_b = $b->getWeight();

    if ($weight_a == $weight_b) {
      return 0;
    }
    // Reverse order.
    return ($weight_a < $weight_b) ? 1 : -1;
  }

}
