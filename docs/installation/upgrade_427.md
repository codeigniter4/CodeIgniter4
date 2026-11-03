# Upgrading from 4.2.6 to 4.2.7

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Breaking Changes

### set_cookie()

Due to a bug, `set_cookie()` and `CodeIgniter\HTTP\Response::setCookie()` in the previous versions did not use the `$secure` and `$httponly` values in `Config\Cookie`. The following code did not issue a cookie with the secure flag even if you set `$secure = true` in `Config\Cookie`:

```
helper('cookie');

$cookie = [
    'name'  => $name,
    'value' => $value,
];
set_cookie($cookie);
// or
$this->response->setCookie($cookie);
```

But now the values in `Config\Cookie` are used for the options that are not specified. The above code issues a cookie with the secure flag if you set `$secure = true` in `Config\Cookie`.

If your code depends on this bug, please change it to explicitly specify the necessary options:

```
$cookie = [
    'name'     => $name,
    'value'    => $value,
    'secure'   => false, // Set explicitly
    'httponly' => false, // Set explicitly
];
set_cookie($cookie);
// or
$this->response->setCookie($cookie);
```

### Others

- `Time::__toString()` is now locale-independent. It returns database-compatible strings like '2022-09-07 12:00:00' in any locale. Most locales are not affected by this change. But in a few locales like `ar`, `fa`, `Time::__toString()` (or `(string) $time` or implicit casting to a string) no longer returns a localized datetime string. If you want to get a localized datetime string, use [Time::toDateTimeString()](../libraries/time.md#todatetimestring) instead.
- The logic of Validation rule `required_without` has been changed to validate each array item separately when validating fields with asterisk (`*`), and the method signature of the rule method has also been changed. Extending classes should likewise update the parameters so as not to break LSP.

## Project Files

Version 4.2.7 did not alter any executable code in project files.

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **app/Common.php**
