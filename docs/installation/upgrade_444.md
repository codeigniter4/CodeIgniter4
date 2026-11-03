# Upgrading from 4.4.3 to 4.4.4

Please refer to the upgrade instructions corresponding to your installation method.

- [Composer Installation App Starter Upgrading](installing_composer.md#upgrading)
- [Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading](installing_composer.md#upgrading_1)
- [Manual Installation Upgrading](installing_manual.md#upgrading)

## Mandatory File Changes

### Error Files

Update the following files to show correct error messages:

- **app/Views/errors/cli/error_exception.php**
- **app/Views/errors/html/error_exception.php**

## Breaking Changes

### Validation with Dot Array Syntax

If you are using [dot array syntax](../libraries/validation.md#setting-rules-for-array-data) in validation rules, a bug where `*` would validate data in incorrect dimensions has been fixed.

In previous versions, the rule key `contacts.*.name` captured data with any level like `contacts.*.name`, `contacts.*.*.name`, `contacts.*.*.*.name`, etc., incorrectly.

The following code explains the details:

```php
use Config\Services;

$validation = Services::validation();

$data = [
    'contacts' => [
        'name' => 'Joe Smith',
        'just' => [
            'friends' => [
                ['name' => 'SATO Taro'],
                ['name' => 'Li Ming'],
                ['name' => 'Heinz Müller'],
            ],
        ],
    ],
];

$validation->setRules(
    ['contacts.*.name' => 'required|max_length[8]'],
);

$validation->run($data); // false

d($validation->getErrors());
/*
 Before: Captured `contacts.*.*.*.name` incorrectly.
 [
   contacts.just.friends.0.name => "The contacts.*.name field cannot exceed 8 characters in length.",
   contacts.just.friends.2.name => "The contacts.*.name field cannot exceed 8 characters in length.",
 ]

 After: Captures no data for `contacts.*.name`.
 [
   contacts.*.name => string (38) "The contacts.*.name field is required.",
 ]
*/
```

If you have code that depends on the bug, fix the rule key.

### Validation rules matches and differs

Because bugs have been fixed in the case where `matches` and `differs` in the Strict and Traditional rules validate data of non-string types, if you are using these rules and validate non-string data, the validation results might be changed (fixed).

Note that Traditional Rules should not be used to validate data that is not a string.

### The use of the `ssl_key` option in CURLRequest was removed

The CURLRequest option `ssl_key` is not recognized anymore. If in use, the option `ssl_key` must be replaced with the option `verify` in order to define the path to a CA bundle for CURLRequest.

The CURLRequest option `verify` can also take *boolean* values as usual.

## Project Files

Some files in the **project space** (root, app, public, writable) received updates. Due to these files being outside of the **system** scope, they will not be changed without your intervention.

There are some third-party CodeIgniter modules available to assist with merging changes to the project space: [Explore on Packagist](https://packagist.org/explore/?query=codeigniter4%20updates).

### All Changes

This is a list of all files in the **project space** that received changes; many will be simple comments or formatting that have no effect on the runtime:

- **app/Config/App.php**
- **app/Config/Autoload.php**
- **app/Config/Boot/development.php**
- **app/Config/Boot/testing.php**
- **app/Config/Cache.php**
- **app/Config/Email.php**
- **app/Config/Filters.php**
- **app/Config/Kint.php**
- **app/Config/Modules.php**
- **app/Config/Publisher.php**
- **app/Config/Session.php**
- **app/Views/errors/cli/error_exception.php**
- **app/Views/errors/html/error_exception.php**
- **composer.json**
- env
- spark
