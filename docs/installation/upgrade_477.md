# Upgrading from 4.7.6 to 4.7.7

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Mandatory File Changes

### Worker Mode

If you are using Worker Mode, you should update **public/frankenphp-worker.php** after upgrading to benefit from stateless config retention between requests. The easiest way is to re-run the install command:

```console
php spark worker:install --force
```

## Breaking Changes

## Breaking Enhancements

## Project Files

Some files in the **project space** (root, app, public, writable) received updates. Due to these files being outside of the **system** scope they will not be changed without your intervention.

!!! note "Note"
    There are some third-party CodeIgniter modules available to assist with merging changes to the project space: [Explore on Packagist](https://packagist.org/explore/?query=codeigniter4%20updates).

### Content Changes

The following files received significant changes (including deprecations or visual adjustments) and it is recommended that you merge the updated versions with your application:

#### Config

- **app/Config/WorkerMode.php**
    - `Config\WorkerMode::$persistentFactories` has been added, with a default value set to `['config']`. See [Worker Mode](worker_mode.md#persistent-factories) for details.

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **app/Config/WorkerMode.php**
