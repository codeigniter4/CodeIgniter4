# Typography

The Typography library contains methods that help you format text in semantically relevant ways.

## Loading the Library

Like all services in CodeIgniter, it can be loaded via `Config\Services`, though you usually will not need to load it manually:

```php
<?php

$typography = service('typography');
```

## Available Instance Methods

The following functions are available:

#### autoTypography($str[, $reduceLinebreaks = false])

- **Parameters**  
    - **$str** `string` Input string
    - **$reduceLinebreaks** `bool` Whether to reduce multiple instances of double newlines to two

- **Returns**: HTML-formatted typography-safe string

- **Return type**: `string`

Formats text so that it is semantically and typographically correct HTML.

Usage example:

```php
<?php

$string = $typography->autoTypography($string);
```

!!! note "Note"
    Typographic formatting can be processor-intensive, particularly if you have a lot of content being formatted. If you choose to use this function, you may want to consider [caching](../general/caching.md) your pages.

#### formatCharacters($str)

- **Parameters**  
    - **$str** `string` Input string

- **Returns**: String with formatted characters.

- **Return type**: `string`

This function mainly converts double and single quotes to curly entities, but it also converts em-dashes, double spaces, and ampersands.

Usage example:

```php
<?php

$string = $typography->formatCharacters($string);
```

#### nl2brExceptPre($str)

- **Parameters**  
    - **$str** `string` Input string

- **Returns**: String with HTML-formatted line breaks

- **Return type**: `string`

Converts newlines to `<br />` tags unless they appear within `<pre>` tags. This function is identical to the native PHP `nl2br()` function, except that it ignores `<pre>` tags.

Usage example:

```php
<?php

$string = $typography->nl2brExceptPre($string);
```
