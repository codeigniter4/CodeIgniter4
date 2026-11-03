# Upgrading from 4.1.6 to 4.1.7

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

- The output of `get_cookie()` when `$xssClean` is true has changed. Now it uses `FILTER_SANITIZE_FULL_SPECIAL_CHARS`, not `FILTER_SANITIZE_STRING`. Make sure the change is acceptable. Note that using XSS filtering is a bad practice. It does not prevent XSS attacks perfectly. Using `esc()` with the correct `$context` in the views is recommended.
