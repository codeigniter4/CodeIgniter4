# Upgrading from 4.7.6 to 4.7.7

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Mandatory File Changes

## Breaking Changes

### File Cache Rejects Directory Separators

For security reasons, `FileHandler::validateKey()` now rejects `/`, `\` and null bytes in the cache item file name, independently of the `Config\Cache::$reservedCharacters` setting. A cache key can no longer point outside the cache directory when that setting is relaxed.

With the default configuration most applications are unaffected, because `/`, `\` and `:` are already reserved characters. Applications that emptied `$reservedCharacters` to share one key namespace between handlers and used directory separators in File cache keys now get an `InvalidArgumentException`. Build keys from identifiers with a controlled format, or hash the input, instead of relaxing `$reservedCharacters`. See [Caching](../libraries/caching.md).

## Breaking Enhancements

## Project Files

Some files in the **project space** (root, app, public, writable) received updates. Due to these files being outside of the **system** scope they will not be changed without your intervention.

!!! note "Note"
    There are some third-party CodeIgniter modules available to assist with merging changes to the project space: [Explore on Packagist](https://packagist.org/explore/?query=codeigniter4%20updates).

### Content Changes

The following files received significant changes (including deprecations or visual adjustments) and it is recommended that you merge the updated versions with your application:

#### Config

- @TODO

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- @TODO
