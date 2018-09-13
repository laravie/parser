# Changelog for 2.x

This changelog references the relevant changes (bug and security fixes) done to `parser`.

## 2.0.0

Released: 2018-09-13

### Added

* Add `Laravie\Parser\Xml\Reader::load()` for loading local XML file.
* Add `Laravie\Parser\Xml\Reader::remote()` for loading remote XML file.
* Throws `Laravie\Parser\FileNotFoundException` if loading local file failed.

### Changes

* Bump minimum support PHP to 7.1.+.
* Replaces `illuminate/support` dependencies with `tightenco/collect`.
