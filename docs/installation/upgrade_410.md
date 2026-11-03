# Upgrading from 4.0.5 to 4.1.0 or 4.1.1

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

### Legacy Autoloading

The `Autoloader::loadLegacy()` method was originally for the transition to CodeIgniter v4. Since v4.1.0, this support was removed. All classes must be namespaced.
