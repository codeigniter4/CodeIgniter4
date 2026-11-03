# Auto Routing & Your First Endpoint

In this section, we enable CodeIgniter's *Improved Auto Routing* feature and create a simple JSON endpoint to confirm everything is wired correctly.

## Why Auto-Routing?

The previous tutorial showed how to define routes manually in **app/Config/Routes.php**. While powerful and flexible, this can be tedious for RESTful APIs with many endpoints that follow a common pattern. Auto-Routing simplifies this by mapping URL patterns to controller classes and methods based on conventions, and its focus on HTTP verbs works well for RESTful APIs.

## Enable Improved Auto Routing

By default, Auto-Routing is turned off. Enable it so your controllers automatically handle REST-style methods.

Open **app/Config/Feature.php** and confirm this flag is `true` (this is the default):

```php
public bool $autoRoutesImproved = true;
```

The "Improved" auto router is more secure and reliable than the legacy version, so it's recommended for all new projects.

Then, in **app/Config/Routing.php**, confirm auto-routing is **enabled**:

```php
public bool $autoRoute = true;
```

That's all you need for CodeIgniter to automatically map your controller classes to URIs like `GET /api/ping` or `POST /api/ping`.

## Create a Ping Controller

To understand how a basic API endpoint works, let's generate a controller to serve as our first API endpoint. This will provide a simple "ping" response to confirm our setup is correct.

```console
php spark make:controller Api/Ping
```

This creates **app/Controllers/Api/Ping.php**.

Edit the file so it looks like this:

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Ping extends BaseController
{
    use ResponseTrait;

    public function getIndex(): ResponseInterface
    {
        return $this->respond(['status' => 'ok'], 200);
    }
}
```

Here we:

- Use the `ResponseTrait`, which already includes REST helpers such as `respond()` and proper status codes.
- Define a `getIndex()` method. The `get` prefix means it responds to `GET` requests, and the `Index` name means it matches the base URI (`/api/ping`).

## Test the route

Start the development server if it isn't running:

```console
php spark serve
```

Now visit:

- **Browser:** `http://localhost:8080/api/ping`
- **cURL:** `curl http://localhost:8080/api/ping`

Expected response:

```json
{
    "status": "ok"
}
```

Congratulations — that's your first working JSON endpoint!

## Understand how it works

When you request `/api/ping`:

1.  The **Improved Auto Router** finds the `App\Controllers\Api\Ping` class.
2.  It detects the HTTP verb (`GET`).
3.  It calls the corresponding method name: `getIndex()`.
4.  `ResponseTrait` provides helper methods to produce consistent output.

Here's how other verbs would map if you added them later:

| HTTP Verb          | Method Name     |
|--------------------|-----------------|
| `GET /api/ping`    | `getIndex()`    |
| `POST /api/ping`   | `postIndex()`   |
| `DELETE /api/ping` | `deleteIndex()` |

## Content Negotiation with the Format Class

The `ResponseTrait` uses `CodeIgniter\Format\Format` to format response data. When its `$format` property is `null`, it negotiates a supported format from the request's `Accept` header.

The default `$format` is `'json'`. Set it to `'xml'` for XML responses, or to `null` for content negotiation. This tutorial keeps the default JSON format:

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Ping extends BaseController
{
    use ResponseTrait;

    public function getIndex(): ResponseInterface
    {
        return $this->respond(['status' => 'ok'], 200);
    }
}
```

## Optional: Return More Data

The `respond()` method can return additional data:

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\CodeIgniter;
use CodeIgniter\HTTP\ResponseInterface;

class Ping extends BaseController
{
    use ResponseTrait;

    public function getIndex(): ResponseInterface
    {
        return $this->respond([
            'status'  => 'ok',
            'time'    => date('c'),
            'version' => CodeIgniter::CI_VERSION,
        ]);
    }
}
```

You now have a working endpoint tested in both the browser and cURL. In the next section, we'll create our first real database resource. You'll define a **migration**, **seeder**, and **model** for a simple `books` table that powers the API's CRUD endpoints.
