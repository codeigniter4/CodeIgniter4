# Upgrade Controllers

## Documentation

- [Controller Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/general/controllers.html)
- [Controller Documentation CodeIgniter 4.x](../incoming/controllers.md)

## What has been changed

- Since namespaces have been added to CodeIgniter 4, the controllers must be changed to support namespaces.
- The constructor of CI4 Controller does not automatically load core classes into the properties.
- CI4's Controller has a special constructor [initController()](../incoming/controllers.md#constructor).
- CI4 provides [Request](../incoming/incomingrequest.md) and [Responses](../outgoing/response.md) objects for you to work with - more powerful than the CI3 way.
- If you want a base controller (`MY_Controller` in CI3), use **app/Controllers/BaseController.php**.
- Calling `echo` within Controllers, as in CI3, is still supported, but it is recommended that a string or Response object be returned from Controllers.

## Upgrade Guide

1.  First, move all controller files to the folder **app/Controllers**.
2.  Add this line just after the opening php tag: `namespace App\Controllers;`
3.  Replace `extends CI_Controller` with `extends BaseController`.
4.  Remove the line `defined('BASEPATH') OR exit('No direct script access allowed');` if it exists.

If you use sub-directories in your controller structure, you have to change the namespace accordingly.

For example, if you have a version 3 controller located in **application/controllers/users/auth/Register.php**, the namespace has to be `namespace App\Controllers\Users\Auth;` and the controller path in version 4 should look like this: **app/Controllers/Users/Auth/Register.php**. Make sure to capitalize the first letters of the sub-directories.

After that, you have to insert a `use` statement below the namespace definition in order to extend the `BaseController`: `use App\Controllers\BaseController;`

## Code Example

### CodeIgniter Version 3.x

Path: **application/controllers**:

<!-- lint-ignore -->
```php
<?php

class Helloworld extends CI_Controller
{
    public function index($name)
    {
        echo 'Hello ' . html_escape($name) . '!';
    }
}
```

### CodeIgniter Version 4.x

Path: **app/Controllers**:

```php
<?php

namespace App\Controllers;

class Helloworld extends BaseController
{
    public function index($name)
    {
        return 'Hello ' . esc($name) . '!';
    }
}
```
