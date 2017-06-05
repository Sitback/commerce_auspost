Drupal Commerce
===============

Integrates Australia Post postage estimation with Drupal Commerce 2.x on Drupal 8.

Supporting all FedEx shipping services and an increasing number 
of FedEx's special services, such as Hazardous Materials and Dry Ice,
Commerce FedEx is designed to be your one-stop solution 
for all of your FedEx shipping requirements.

Until this module enters Beta, there will *not* be a 
guaranteed upgrade path between versions. We are doing our best to 
minimize disruption, but pay 
close attention during upgrades as there may be cases where you will 
have to reinstall the module.

Please report any bugs on [the Github issue queue](https://github.com/Sitback/commerce_auspost/issues).

## Requirements

* [An Australia Post PAC API key](https://developers.auspost.com.au/apis/pacpcs-registration)
* Drupal 8
* Drupal Commerce 2.x (latest version)
* Commerce Shipping 2.x (latest version)
* Physical Fields 1.x (latest version)

## Installation

Use [Composer](https://getcomposer.org/) to get Commerce AusPost and all of its
dependencies installed on your Drupal 8 site, installing via a tarball from drupal.org **is not supported**.

Until this is officially merged into the existing `commerce_auspost` module, you can install via the following instructions:

* Edit your project's `composer.json` file and the following to the `repositories` property (if one doesn't exist, create one):

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Sitback/commerce_auspost"
    }
  ]
}
```

* Add the following to the `require` block, also in `composer.json`:
 
```json
{
  "require": {
    "drupal/commerce_auspost": "dev-8.x-1.x"
  }
}
```

* An example `composer.json` would look like:

```json
{
    "name": "chinthakagodawita/my-commerce-project",
    "authors": [
        {
            "name": "Chin Godawita",
            "email": "chin@sitback.com.au"
        }
    ],
    "repositories": [
      {
        "type": "composer",
        "url": "https://packages.drupal.org/8"
      },
      {
        "type": "vcs",
        "url": "https://github.com/Sitback/commerce_auspost"
      }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true,
    "require": {
      "drupal/commerce_auspost": "dev-8.x-1.x"
    }
}
```

* Then simply enable the "AusPost (Commerce Shipping)" module and visit 
`Commerce > Configuration > Shipping Methods` to configure the shipping method.

Do note, the more shipping services you have enabled, the slower postage calculations will be.

## Supporting Organisations
The Drupal 8 version was sponsored by:
* Sitback Solutions - https://www.sitback.com.au

The Drupal 7 version was sponsored by:
* eighty options - http://www.eightyoptions.com.au/
