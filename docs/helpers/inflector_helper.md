# Inflector Helper

The Inflector Helper file contains functions that permit you to change **English** words to plural, singular, camel case, etc.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('inflector');
```

## Available Functions

The following functions are available:

#### singular($string)

- **Parameters**  
    - **$string** `string` Input string

- **Returns**: A singular word

- **Return type**: `string`

Changes a plural word to singular. Example:

```php
<?php

echo singular('dogs'); // Prints 'dog'
```

#### plural($string)

- **Parameters**  
    - **$string** `string` Input string

- **Returns**: A plural word

- **Return type**: `string`

Changes a singular word to plural. Example:

```php
<?php

echo plural('dog'); // Prints 'dogs'
```

#### counted($count, $string)

- **Parameters**  
    - **$count** `int` Number of items
    - **$string** `string` Input string

- **Returns**: A singular or plural phrase

- **Return type**: `string`

Changes a word and its count to a phrase. Example:

```php
<?php

echo counted(3, 'dog'); // Prints '3 dogs'
```

#### camelize($string)

- **Parameters**  
    - **$string** `string` Input string

- **Returns**: Camel case string

- **Return type**: `string`

Changes a string of words separated by spaces or underscores to camel case. Example:

```php
<?php

echo camelize('my_dog_spot'); // Prints 'myDogSpot'
```

#### pascalize($string)

- **Parameters**  
    - **$string** `string` Input string

- **Returns**: Pascal case string

- **Return type**: `string`

Changes a string of words separated by spaces or underscores to Pascal case, which is camel case with the first letter capitalized. Example:

```php
<?php

echo pascalize('my_dog_spot'); // Prints 'MyDogSpot'
```

#### underscore($string)

- **Parameters**  
    - **$string** `string` Input string

- **Returns**: String containing underscores instead of spaces

- **Return type**: `string`

Takes multiple words separated by spaces and underscores them. Example:

```php
<?php

echo underscore('my dog spot'); // Prints 'my_dog_spot'
```

#### decamelize($string)

- **Parameters**  
    - **$string** `string` Input string

- **Returns**: String containing underscores between words

- **Return type**: `string`

Takes multiple words in camelCase or PascalCase and converts them to snake_case. Example:

```php
<?php

echo decamelize('myDogSpot'); // Prints 'my_dog_spot'
```

#### humanize($string[, $separator = '\_'])

- **Parameters**  
    - **$string** `string` Input string
    - **$separator** `string` Input separator

- **Returns**: Humanized string

- **Return type**: `string`

Takes multiple words separated by underscores and adds spaces between them. Each word is capitalized.

Example:

```php
<?php

echo humanize('my_dog_spot'); // Prints 'My Dog Spot'
```

To use dashes instead of underscores:

```php
<?php

echo humanize('my-dog-spot', '-'); // Prints 'My Dog Spot'
```

#### is_pluralizable($word)

- **Parameters**  
    - **$word** `string` Input string

- **Returns**: true if the word is countable or false if not

- **Return type**: `bool`

Checks if the given word has a plural version. Example:

```php
<?php

is_pluralizable('equipment'); // Returns false
```

#### dasherize($string)

- **Parameters**  
    - **$string** `string` Input string

- **Returns**: Dasherized string

- **Return type**: `string`

Replaces underscores with dashes in the string. Example:

```php
<?php

dasherize('hello_world'); // Returns 'hello-world'
```

#### ordinal($integer)

- **Parameters**  
    - **$integer** `int` The integer to determine the suffix for

- **Returns**: Ordinal suffix

- **Return type**: `string`

Returns the suffix that should be added to a number to denote the position, such as 1st, 2nd, 3rd, 4th. Example:

```php
<?php

ordinal(1); // Returns 'st'
```

#### ordinalize($integer)

- **Parameters**  
    - **$integer** `int` The integer to ordinalize

- **Returns**: Ordinalized integer

- **Return type**: `string`

Turns a number into an ordinal string used to denote the position, such as 1st, 2nd, 3rd, 4th. Example:

```php
<?php

ordinalize(1); // Returns '1st'
```
