# Upgrading from 4.3.5 to 4.3.6

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

- `AutoRouterInterface::getRoute()` has a new second parameter `string $httpVerb`. If you implement it, add the parameter.

## Breaking Enhancements

- The method signatures of `ValidationInterface::check()` and `Validation::check()` have been changed. If you implement or extend them, update the signatures.

## Project Files

Version 4.3.6 did not alter any executable code in project files.

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **composer.json**
