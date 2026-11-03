# Security Helper

The Security Helper file contains security-related functions.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('security');
```

## Available Functions

The following functions are available:

#### sanitize_filename($filename[, $relativePath = false])

- **Parameters**  
    - **$filename** `string` Filename
    - **$relativePath** `bool` Whether the relative path is acceptable (available since v4.6.2)

- **Returns**: Sanitized file name

- **Return type**: `string`

Provides protection against directory traversal.

Backslashes are always removed. If `$relativePath` is `true`, forward slashes are preserved to allow relative paths such as `path/to/file.txt`, while parent-directory sequences (`../`) are removed.

!!! note "Note"
    When passing relative paths to this helper, use forward slashes (`/`) as directory separators, including on Windows.

For more info, please see the [Security Library](../libraries/security.md) documentation.

#### strip_image_tags($str)

- **Parameters**  
    - **$str** `string` Input string

- **Returns**: The input string with no image tags

- **Return type**: `string`

This is a security function that will strip image tags from a string. It leaves the image URL as plain text.

Example:

```php
<?php

$string = strip_image_tags($string);
```

#### encode_php_tags($str)

- **Parameters**  
    - **$str** `string` Input string

- **Returns**: Safely formatted string

- **Return type**: `string`

This is a security function that converts PHP tags to entities.

Example:

```php
<?php

$string = encode_php_tags($string);
```
