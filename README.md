XML Document Parser PHP
==============


Parser Component is a framework agnostic package that provide a simple way to parse XML to array without having to write a complex logic.

[![tests](https://github.com/laravie/parser/actions/workflows/tests.yml/badge.svg?branch=3.x)](https://github.com/laravie/parser/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/laravie/parser/version)](https://packagist.org/packages/laravie/parser)
[![Total Downloads](https://poser.pugx.org/laravie/parser/downloads)](https://packagist.org/packages/laravie/parser)
[![Latest Unstable Version](https://poser.pugx.org/laravie/parser/v/unstable)](//packagist.org/packages/laravie/parser)
[![License](https://poser.pugx.org/laravie/parser/license)](https://packagist.org/packages/laravie/parser)
[![Coverage Status](https://coveralls.io/repos/github/laravie/parser/badge.svg?branch=2.x)](https://coveralls.io/github/laravie/parser?branch=2.x)

Imagine if you can parse

```xml
<api>
    <user followers="5">
        <id>1</id>
        <email>crynobone@gmail.com</email>
    </user>
</api>
```

to

```php
<?php

$user = [
    'id' => '1',
    'email' => 'crynobone@gmail.com',
    'followers' => '5'
];
```

by just writing this:

```php
<?php

use Laravie\Parser\Xml\Reader;
use Laravie\Parser\Xml\Document;

$xml = (new Reader(new Document()))->load('path/to/above.xml');

$user = $xml->parse([
    'id' => ['uses' => 'user.id'],
    'email' => ['uses' => 'user.email'],
    'followers' => ['uses' => 'user::followers'],
]);
```

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "laravie/parser": "^2.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "laravie/parser=^2.0"

