XML Document Parser PHP
==============


Parser Component is a framework agnostic package that provide a simple way to parse XML to array without having to write a complex logic.

[![Latest Stable Version](https://img.shields.io/github/release/laravie/parser.svg?style=flat-square)](https://packagist.org/packages/laravie/parser)
[![Total Downloads](https://img.shields.io/packagist/dt/laravie/parser.svg?style=flat-square)](https://packagist.org/packages/laravie/parser)
[![MIT License](https://img.shields.io/packagist/l/laravie/parser.svg?style=flat-square)](https://packagist.org/packages/laravie/parser)
[![Build Status](https://img.shields.io/travis/laravie/parser/master.svg?style=flat-square)](https://travis-ci.org/laravie/parser)
[![Coverage Status](https://img.shields.io/coveralls/laravie/parser/master.svg?style=flat-square)](https://coveralls.io/r/laravie/parser?branch=master)
[![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/laravie/parser/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravie/parser/)

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
use Laravie\Xml\Reader;
use Laravie\Xml\Document;

$xml = (new Reader(new Document())->load('path/to/above.xml');

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
        "laravie/parser": "~1.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "laravie/parser=~1.0"

