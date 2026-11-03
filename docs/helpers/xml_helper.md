# XML Helper

The XML Helper file contains functions that assist in working with XML data.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('xml');
```

## Available Functions

The following functions are available:

#### xml_convert($str[, $protect_all = false])

- **Parameters**  
    - **$str** `string` The text string to convert
    - **$protect_all** `bool` Whether to protect all content that looks like a potential entity instead of just numbered entities, e.g., &foo;

- **Returns**: XML-converted string

- **Return type**: `string`

Takes a string as input and converts the following reserved XML characters to entities:

- Ampersands: &
- Less than and greater than characters: < \>
- Single and double quotes: ' "
- Dashes: -

This function ignores ampersands if they are part of existing numbered character entities, e.g., &amp;#123;. Example:

```php
<?php

$string = '<p>Here is a paragraph & an entity (&#123;).</p>';
$string = xml_convert($string);
echo $string;
```

Outputs:

```html
&lt;p&gt;Here is a paragraph &amp; an entity (&#123;).&lt;/p&gt;
```
