XML Document Parser PHP
==============


Parser Component is a framework agnostic package that provide a simple way to parse XML to array without having to write a complex logic.

[![Build Status](https://travis-ci.org/snowlyg/parser.svg?branch=master)](https://travis-ci.org/snowlyg/parser)
[![Latest Stable Version](https://poser.pugx.org/snowlyg/parser/version)](https://packagist.org/packages/snowlyg/parser)
[![Total Downloads](https://poser.pugx.org/snowlyg/parser/downloads)](https://packagist.org/packages/snowlyg/parser)
[![Latest Unstable Version](https://poser.pugx.org/snowlyg/parser/v/unstable)](//packagist.org/packages/snowlyg/parser)
[![License](https://poser.pugx.org/snowlyg/parser/license)](https://packagist.org/packages/snowlyg/parser)
[![Coverage Status](https://coveralls.io/repos/github/snowlyg/parser/badge.svg?branch=master)](https://coveralls.io/github/snowlyg/parser?branch=master)

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
$user = [
    'id' => '1',
    'email' => 'crynobone@gmail.com',
    'followers' => '5'
];
```

by just writing this:

```php
use Snowlyg\Parser\Xml\Reader;
use Snowlyg\Parser\Xml\Document;

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
        "snowlyg/parser": "^2.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "snowlyg/parser=^2.0"

