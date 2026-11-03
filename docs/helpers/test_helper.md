# Test Helper

The Test Helper file contains functions that assist in testing your project.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('test');
```

## Available Functions

The following functions are available:

#### fake($model[, ?array $overrides = null[, $persist = true]])

- **Parameters**  
    - **$model** `Model|object|string` Instance or name of the model to use with Fabricator
    - **$overrides** `array|null` Overriding data to pass to `Fabricator::setOverrides()`
    - **$persist** `bool` Whether to save the generated item to the database

- **Returns**: A random fake item, saved to the database if `$persist` is true

- **Return type**: `object|array`

Uses `CodeIgniter\Test\Fabricator` to create a random item and optionally add it to the database.

Usage example:

```php
<?php

use CodeIgniter\Test\CIUnitTestCase;

final class MyTestClass extends CIUnitTestCase
{
    public function testUserAccess()
    {
        $user = fake('App\Models\UserModel');

        $this->assertTrue($this->userHasAccess($user));
    }
}
```
