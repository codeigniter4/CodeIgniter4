# API Resources

When building APIs, you often need to transform your data models into a consistent format before sending them to the client. API Resources, implemented through transformers, provide a clean way to convert your entities, arrays, or objects into structured API responses. They help separate your internal data structure from what you expose through your API, making it easier to maintain and evolve your API over time.

## Quick Example

The following example shows a common usage pattern for transformers in your application.

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'         => $resource['id'],
            'name'       => $resource['name'],
            'email'      => $resource['email'],
            'created_at' => $resource['created_at'],
        ];
    }
}

// In your controller
$user        = model('UserModel')->find(1);
$transformer = new UserTransformer();

return $this->respond($transformer->transform($user));
```

In this example, the `UserTransformer` defines which fields from a user entity should be included in the API response. The `transform()` method converts a single resource, while `transformMany()` handles collections of resources.

## Creating a Transformer

To create a transformer, extend the `BaseTransformer` class and implement the `toArray()` method to define your API resource structure. The `toArray()` method receives the resource being transformed as a parameter, allowing you to access and transform its data.

### Basic Transformer

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'           => $resource['id'],
            'username'     => $resource['name'],  // Renaming the field
            'email'        => $resource['email'],
            'member_since' => date('Y-m-d', strtotime($resource['created_at'])), // Formatting
        ];
    }
}
```

The `toArray()` method receives the resource (entity, array, or object) as its parameter and defines the structure of your API response. You can include any fields you want from the resource, and you can also rename or transform values as needed.

### Generating Transformer Files

CodeIgniter provides a CLI command to quickly generate transformer skeleton files:

```console
php spark make:transformer User
```

This creates a new transformer file at **app/Transformers/User.php** with the basic structure already in place.

#### Command Options

The `make:transformer` command supports several options:

**--suffix**  
Appends "Transformer" to the class name:

```console
php spark make:transformer User --suffix
```

Creates **app/Transformers/UserTransformer.php**

**--namespace**  
Specifies a custom root namespace:

```console
php spark make:transformer User --namespace MyCompany\API
```

**--force**  
Forces overwriting an existing file:

```console
php spark make:transformer User --force
```

#### Subdirectories

You can organize transformers into subdirectories by including the path in the name:

```console
php spark make:transformer api/v1/User
```

This creates **app/Transformers/Api/V1/User.php** with the appropriate namespace `App\Transformers\Api\V1`.

### Using Transformers in Controllers

Once you've created a transformer, you can use it in your controllers to transform data before returning it to the client.

```php
<?php

namespace App\Controllers;

use App\Transformers\UserTransformer;
use CodeIgniter\API\ResponseTrait;

class Users extends BaseController
{
    use ResponseTrait;

    public function show($id)
    {
        $user = model('UserModel')->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        $transformer = new UserTransformer();

        return $this->respond($transformer->transform($user));
    }

    public function index()
    {
        $users = model('UserModel')->findAll();

        $transformer = new UserTransformer();

        return $this->respond($transformer->transformMany($users));
    }
}
```

## Field Filtering

The transformer automatically supports field filtering through the `fields` query parameter of the current URL. This allows API clients to request only the specific fields they need, reducing bandwidth and improving performance.

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'         => $resource['id'],
            'name'       => $resource['name'],
            'email'      => $resource['email'],
            'created_at' => $resource['created_at'],
            'updated_at' => $resource['updated_at'],
        ];
    }
}

// Request: GET /users/1?fields=id,name
// Response: {"id": 1, "name": "John Doe"}
```

A request to `/users/1?fields=id,name` would return only:

```json
{
    "id": 1,
    "name": "John Doe"
}
```

### Restricting Available Fields

By default, clients can request any field defined in your `toArray()` method. You can restrict which fields are allowed by overriding the `getAllowedFields()` method:

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'         => $resource['id'],
            'name'       => $resource['name'],
            'email'      => $resource['email'],
            'created_at' => $resource['created_at'],
            'updated_at' => $resource['updated_at'],
        ];
    }

    protected function getAllowedFields(): ?array
    {
        // Only these fields can be requested
        return ['id', 'name', 'created_at'];
    }
}
```

Now, even if a client requests `/users/1?fields=email`, an `ApiException` will be thrown because `email` is not in the allowed fields list.

## Including Related Resources

Transformers support loading related resources through the `include` query parameter. This follows a common API pattern where clients can specify which relationships they want included. While relationships are the most frequent use case, you can include any additional data you want by defining custom include methods.

### Defining Include Methods

To support including related resources, create methods prefixed with `include` followed by the resource name. Inside these methods, you can access the current resource being transformed via `$this->resource`:

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'    => $resource['id'],
            'name'  => $resource['name'],
            'email' => $resource['email'],
        ];
    }

    protected function includePosts(): array
    {
        // Use $this->resource to access the current resource being transformed
        $posts = model('PostModel')->where('user_id', $this->resource['id'])->findAll();

        return (new PostTransformer())->transformMany($posts);
    }

    protected function includeComments(): array
    {
        $comments = model('CommentModel')->where('user_id', $this->resource['id'])->findAll();

        return (new CommentTransformer())->transformMany($comments);
    }
}
```

Note how the include methods use `$this->resource['id']` to access the ID of the user being transformed. The `$this->resource` property is automatically set by the transformer when `transform()` is called.

Clients can now request: `/users/1?include=posts,comments`

The response would include:

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "posts": [
        {
            "id": 1,
            "title": "First Post"
        }
    ],
    "comments": [
        {
            "id": 1,
            "content": "Great article!"
        }
    ]
}
```

!!! note "Note"
    The `fields` and `include` query parameters describe the **root** resource only. Transformers instantiated inside an `include*()` method (such as `PostTransformer` above) are treated as nested resources: when created **without an explicit request** they do **not** inherit the root request's `fields`/`include` state. This prevents the parent's query parameters from leaking into related resources, which could otherwise cause incorrect field filtering or unexpected/recursive includes. If you deliberately pass a request to a nested transformer, that request's scope is honored.

### Restricting Available Includes

Similar to field filtering, you can restrict which relationships can be included by overriding the `getAllowedIncludes()` method:

```php
<?php

namespace App\Transformers;

use App\Transformers\CommentTransformer;
use App\Transformers\PostTransformer;
use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'    => $resource['id'],
            'name'  => $resource['name'],
            'email' => $resource['email'],
        ];
    }

    protected function getAllowedIncludes(): ?array
    {
        // Only these relationships can be included
        return ['posts', 'comments'];
    }

    protected function includePosts(): array
    {
        $posts = model('PostModel')->where('user_id', $this->resource['id'])->findAll();

        return (new PostTransformer())->transformMany($posts);
    }

    protected function includeComments(): array
    {
        $comments = model('CommentModel')->where('user_id', $this->resource['id'])->findAll();

        return (new CommentTransformer())->transformMany($comments);
    }

    protected function includeOrders(): array
    {
        // This method exists but won't be callable from the API
        // because 'orders' is not in getAllowedIncludes()
        $orders = model('OrderModel')->where('user_id', $this->resource['id'])->findAll();

        return (new OrderTransformer())->transformMany($orders);
    }
}
```

If you want to disable all includes, return an empty array:

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'    => $resource['id'],
            'name'  => $resource['name'],
            'email' => $resource['email'],
        ];
    }

    protected function getAllowedIncludes(): ?array
    {
        // Return empty array to disable all includes
        return [];
    }
}
```

### Include Validation

The transformer automatically validates that any requested includes have corresponding `include*()` methods defined in your transformer class. If a client requests an include that doesn't exist, an `ApiException` will be thrown.

For example, if a client requests:

```
GET /api/users?include=invalid
```

And your transformer doesn't have an `includeInvalid()` method, an exception will be thrown with the message: "Missing include method for: invalid".

This helps catch typos and prevents unexpected behavior.

## Transforming Collections

The `transformMany()` method makes it easy to transform arrays of resources:

```php
<?php

namespace App\Controllers;

use App\Transformers\UserTransformer;
use CodeIgniter\API\ResponseTrait;

class Users extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $users = model('UserModel')->findAll();

        $transformer = new UserTransformer();
        $data        = $transformer->transformMany($users);

        return $this->respond($data);
    }
}
```

The `transformMany()` method applies the same transformation logic to each item in the collection, including any field filtering or includes specified in the request.

## Working with Different Data Types

Transformers can handle various data types, not just entities.

### Transforming Entities

When you pass an `Entity` instance to `transform()`, it automatically calls the entity's `toArray()` method to get the data:

```php
<?php

use App\Entities\User;
use App\Transformers\UserTransformer;

$user = new User([
    'id'    => 1,
    'name'  => 'John Doe',
    'email' => 'john@example.com',
]);

$transformer = new UserTransformer();
$result      = $transformer->transform($user);
```

### Transforming Arrays

You can transform plain arrays as well:

```php
<?php

use App\Transformers\UserTransformer;

$userData = [
    'id'    => 1,
    'name'  => 'John Doe',
    'email' => 'john@example.com',
];

$transformer = new UserTransformer();
$result      = $transformer->transform($userData);
```

### Transforming Objects

Any object can be cast to an array and transformed:

```php
<?php

use App\Transformers\UserTransformer;

$user        = new \stdClass();
$user->id    = 1;
$user->name  = 'John Doe';
$user->email = 'john@example.com';

$transformer = new UserTransformer();
$result      = $transformer->transform($user);
```

### Using toArray() Only

If you don't pass a resource to `transform()`, it will use the data from your `toArray()` method:

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class StaticDataTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'version' => '1.0',
            'status'  => 'active',
            'message' => 'API is running',
        ];
    }
}

// Usage
$transformer = new StaticDataTransformer();
$result      = $transformer->transform(null); // No resource passed
```

## Class Reference

## CodeIgniter\API

### BaseTransformer

#### \__construct(?IncomingRequest $request = null)

- **Parameters**  
    - **$request** `IncomingRequest|null` Optional request instance. If not provided, the global request will be used.

Initializes the transformer and extracts the `fields` and `include` query parameters from the request.

!!! note "Note"
    When no request is explicitly passed, the global request's `fields` and `include` are read **only for the root transformer**. A transformer constructed during a nested transformation (i.e. inside an `include*()` method) without an explicit request still uses the global request object, but does not read `fields`/`include` from it, to avoid leaking the root's scope. Passing an explicit request always applies that request's scope, regardless of nesting.

#### toArray(mixed $resource)

- **Parameters**  
    - **$resource** `mixed` The resource being transformed (Entity, array, object, or null)

- **Returns**: The array representation of the resource

- **Return type**: `array`

This abstract method must be implemented by child classes to define the structure of the API resource. The resource parameter contains the data being transformed. Return an array with the fields you want to include in the API response, accessing data from the `$resource` parameter.

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class ProductTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'          => $resource['id'],
            'name'        => $resource['name'],
            'price'       => $resource['price'],
            'in_stock'    => $resource['stock_quantity'] > 0,
            'description' => $resource['description'],
        ];
    }
}
```

#### transform($resource = null)

- **Parameters**  
    - **$resource** `mixed` The resource to transform (Entity, array, object, or null)

- **Returns**: The transformed array

- **Return type**: `array`

Transforms the given resource into an array by calling `toArray()` with the resource data. If `$resource` is `null`, passes `null` to `toArray()`. If it's an Entity, extracts its array representation first. Otherwise, casts it to an array.

The resource is also stored in `$this->resource` so include methods can access it.

The method automatically applies field filtering and includes based on query parameters.

```php
<?php

use App\Transformers\UserTransformer;

$user = model('UserModel')->find(1);

$transformer = new UserTransformer();

// Transform an entity
$result = $transformer->transform($user);

// Transform an array
$userData = ['id' => 1, 'name' => 'John Doe'];
$result   = $transformer->transform($userData);

// Use toArray() data
$result = $transformer->transform();
```

#### transformMany(array $resources)

- **Parameters**  
    - **$resources** `array` The array of resources to transform

- **Returns**: Array of transformed resources

- **Return type**: `array`

Transforms a collection of resources by calling `transform()` on each item. Field filtering and includes are applied consistently to all items.

```php
<?php

use App\Transformers\UserTransformer;

$users = model('UserModel')->findAll();

$transformer = new UserTransformer();
$results     = $transformer->transformMany($users);

// $results is an array of transformed user arrays
foreach ($results as $user) {
    // Each $user is the result of calling transform() on an individual user
}
```

#### getAllowedFields()

- **Returns**: Array of allowed field names, or `null` to allow all fields
- **Return type**: `array|null`

Override this method to restrict which fields can be requested via the `fields` query parameter. Return `null` (the default) to allow all fields from `toArray()`. Return an array of field names to create a whitelist of allowed fields.

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'         => $resource['id'],
            'name'       => $resource['name'],
            'email'      => $resource['email'],
            'created_at' => $resource['created_at'],
        ];
    }

    protected function getAllowedFields(): ?array
    {
        // Clients can only request id, name, and created_at
        // Attempting to request 'email' will throw an ApiException
        return ['id', 'name', 'created_at'];
    }
}
```

#### getAllowedIncludes()

- **Returns**: Array of allowed include names, or `null` to allow all includes
- **Return type**: `array|null`

Override this method to restrict which related resources can be included via the `include` query parameter. Return `null` (the default) to allow all includes that have corresponding methods. Return an array of include names to create a whitelist. Return an empty array to disable all includes.

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class UserTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'    => $resource['id'],
            'name'  => $resource['name'],
            'email' => $resource['email'],
        ];
    }

    protected function getAllowedIncludes(): ?array
    {
        // Only 'posts' can be included via ?include=posts
        // Attempting to include 'orders' will throw an ApiException
        return ['posts'];
    }

    protected function includePosts(): array
    {
        $posts = model('PostModel')->where('user_id', $this->resource['id'])->findAll();

        return (new PostTransformer())->transformMany($posts);
    }

    protected function includeOrders(): array
    {
        // This method exists but cannot be called via the API
        $orders = model('OrderModel')->where('user_id', $this->resource['id'])->findAll();

        return (new OrderTransformer())->transformMany($orders);
    }
}
```

## Exception Reference

### ApiException

#### forInvalidFields(string $field)

- **Parameters**  
    - **$field** `string` The invalid field name(s)

- **Returns**: ApiException instance

- **Return type**: `ApiException`

Thrown when a client requests a field via the `fields` query parameter that is not in the allowed fields list.

#### forInvalidIncludes(string $include)

- **Parameters**  
    - **$include** `string` The invalid include name(s)

- **Returns**: ApiException instance

- **Return type**: `ApiException`

Thrown when a client requests an include via the `include` query parameter that is not in the allowed includes list.

#### forMissingInclude(string $include)

- **Parameters**  
    - **$include** `string` The missing include method name

- **Returns**: ApiException instance

- **Return type**: `ApiException`

Thrown when a client requests an include via the `include` query parameter, but the corresponding `include*()` method does not exist in the transformer class. This validation ensures that all requested includes have proper handler methods defined.
