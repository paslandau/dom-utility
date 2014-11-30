#dom-utility
[![Build Status](https://travis-ci.org/paslandau/dom-utility.svg?branch=master)](https://travis-ci.org/paslandau/dom-utility)

Library to extend PHP core functions by common (missing) DOM functions

##Description
[todo]

##Requirements

- PHP >= 5.5

##Installation

The recommended way to install dom-utility is through [Composer](http://getcomposer.org/).

    curl -sS https://getcomposer.org/installer | php

Next, update your project's composer.json file to include DomUtility:

    {
        "repositories": [ { "type": "composer", "url": "http://packages.myseosolution.de/"} ],
        "minimum-stability": "dev",
        "require": {
             "paslandau/dom-utility": "dev-master"
        }
    }

After installing, you need to require Composer's autoloader:
```php

require 'vendor/autoload.php';
```