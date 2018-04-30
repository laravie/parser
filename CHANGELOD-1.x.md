# Changelog for 1.x

This changelog references the relevant changes (bug and security fixes) done to `parser`.

## 1.3.0

Released: 2018-04-30

### Added

* Added support to parse multi-level attributes from XML.
* Added common helper methods.

### Changes

* Bump minimum PHP version to 5.6.+.

## 1.2.2

Released: 2017-12-25

### Changes

* Revert throws `Laravie\Parser\FileNotFoundException` when loading invalid file.

## 1.2.1

Released: 2017-12-16

### Changes

* Throws `Laravie\Parser\FileNotFoundException` when loading invalid file.

## 1.2.0

Released: 2017-07-26

### Changes

* Replace `anahkiasen/underscore-php` with `illuminate/support` for PHP 7.2 compatibility.
* Bump minimum PHP version to 5.5.x.

## 1.1.1 

Released: 2016-03-02

### Fixes

* Check if index `default` exist before returning the default parameter.

## 1.1.0 

Released: 2016-01-12

### Changes

* Remove dependencies to `illuminate/support` component and utilize `anahkiasen/underscore-php`.

## 1.0.0

Released: 2015-12-18

### New

* Fork initial release from [orchestra/parser](https://github.com/orchestral/parser).
* Add support for handling non-associated array attributes.
