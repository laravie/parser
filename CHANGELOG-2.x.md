# Changelog for 2.x

This changelog references the relevant changes (bug and security fixes) done to `parser`.

## 2.1.1

Released: 2020-07-24

### Fixes

* Fixes variable usage when filtering using `Closure`.

## 2.1.0

Released: 2020-04-07

### Changes

* Allow broader support to support `Closure` instead of just `string` or `null` when defining `$filter`.
* Performance improvements to `data_get()` based on Laravel Framework changes.

## 2.0.4

Released: 2020-02-27

### Changes

* Add support for `tightenco/collect` `7.0+`.

## 2.0.3

Released: 2019-11-03

### Changes

* Add support for `tightenco/collect` `6.0+`.

## 2.0.2

Released: 2019-07-24

### Changes

* Catch `Throwable` exception from failed `simplexml_load_string()` and `simplexml_load_file()` usage.

## 2.0.1

Released: 2019-03-29

### Changes

* Improve performance by prefixing all global functions calls with `\` to skip the look up and resolve process and go straight to the global function.

## 2.0.0

Released: 2018-09-13

### Added

* Add `Laravie\Parser\Xml\Reader::load()` for loading local XML file.
* Add `Laravie\Parser\Xml\Reader::remote()` for loading remote XML file.
* Throws `Laravie\Parser\FileNotFoundException` if loading local file failed.

### Changes

* Bump minimum support PHP to 7.1.+.
* Replaces `illuminate/support` dependencies with `tightenco/collect`.
