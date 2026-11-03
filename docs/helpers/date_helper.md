# Date Helper

The Date Helper file contains functions that assist in working with dates.

!!! note "Note"
    Many functions previously found in the CodeIgniter 3 `date_helper` have been moved to the [Time](../libraries/time.md) class in CodeIgniter 4.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('date');
```

## Available Functions

The following functions are available:

#### now([$timezone = null])

- **Parameters**  
    - **$timezone** `string` Timezone

- **Returns**: UNIX timestamp

- **Return type**: `int`

!!! note "Note"
    It is recommended to use the [Time](../libraries/time.md) class instead. Use `Time::now()->getTimestamp()` to get the current UNIX timestamp.

If a timezone is not provided, it will return the current UNIX timestamp using `time()`.

```php
<?php

echo now();
```

If any PHP-supported timezone is provided, it will return a timestamp that is offset by the time difference. It is not the same as the current UNIX timestamp.

If you do not intend to set your master time reference to any other PHP-supported timezone (which you'll typically do if you run a site that lets each user set their own timezone settings), there is no benefit to using this function over PHP's `time()` function.

#### timezone_select([$class = '', $default = '', $what = \DateTimeZone::ALL, $country = null])

- **Parameters**  
    - **$class** `string` Optional class to apply to the select field
    - **$default** `string` Default value for initial selection
    - **$what** `int` DateTimeZone class constants (see [listIdentifiers](https://www.php.net/manual/en/datetimezone.listidentifiers.php))
    - **$country** `string` A two-letter ISO 3166-1 compatible country code (see [listIdentifiers](https://www.php.net/manual/en/datetimezone.listidentifiers.php))

- **Returns**: Preformatted HTML select field

- **Return type**: `string`

Generates a `select` form field of available timezones (optionally filtered by `$what` and `$country`). You can supply an optional class to apply to the field to make formatting easier, as well as a default selected value.

```php
<?php

echo timezone_select('custom-select', 'America/New_York');
```
