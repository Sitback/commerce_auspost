
-- SUMMARY --

Integrates Australia Post postage estimation with Commerce Shipping.

For a full description of the module, visit the project page:
  http://drupal.org/sandbox/jhesketh/1851514

To submit bug reports and feature suggestions, or to track changes:
  http://drupal.org/project/issues/1851514


-- REQUIREMENTS --

commerce
http://drupal.org/project/commerce

commerce_shipping (7.x-2.x)
http://drupal.org/project/commerce_shipping

commerce_physical
http://drupal.org/project/commerce_physical

This module requires an API key with Australia Post in order to access their
shipping calculations. 
You can apply for one free here: http://developers.auspost.com.au

-- INSTALLATION --

* Install as usual, see http://drupal.org/node/70151 for further information.


-- CONFIGURATION --

* Go to Store -> Shipping -> Shipping Methods
* Edit the Australia Post Method
  * Put in your Australia Post API key
  * Enter the ship from postcode
  * Select the shipping methods you wish to make available in your store
  * Enter the default package size
  * Choose an insurance percentage to use with insured services
* Go to Store -> Products -> Product Types -> Edit your product type
  * Add physical dimensions and physical weight to your product
  * Update your available products with physical details
* Done! (See your shipping checkout page).

-- CONTACT --

Current maintainers:
* Joshua Hesketh - http://drupal.org/user/100807
* Craig Herbert - http://drupal.org/user/3209007

This project has been sponsored by:
* eighty options - http://www.eightyoptions.com.au/
