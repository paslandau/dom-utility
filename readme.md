#DomUtility
[![Build Status](https://travis-ci.org/paslandau/DomUtility.svg?branch=master)](https://travis-ci.org/paslandau/DomUtility)

Library to extend PHP core functions by common (missing) DOM functions

##Description
[todo]

##Requirements

- PHP >= 5.5

##Installation

The recommended way to install DomUtility is through [Composer](http://getcomposer.org/).

    curl -sS https://getcomposer.org/installer | php

Next, update your project's composer.json file to include DomUtility:

    {
        "repositories": [
            {
                "type": "git",
                "url": "https://github.com/paslandau/DomUtility.git"
            }
        ],
        "require": {
             "paslandau/DomUtility": "~0"
        }
    }

After installing, you need to require Composer's autoloader:
```php

require 'vendor/autoload.php';
```