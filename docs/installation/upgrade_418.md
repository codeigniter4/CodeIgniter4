# Upgrading from 4.1.7 to 4.1.8

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

- Due to a security issue in the `API\ResponseTrait`, all trait methods are now scoped to `protected`. See the [Security advisory GHSA-7528-7jg5-6g62](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-7528-7jg5-6g62) for more information.
