# API Responses

Much of modern PHP development requires building APIs, whether simply to provide data for a JavaScript-heavy single-page application, or as a standalone product. CodeIgniter provides a couple of traits that can be used with any controller to make common response types simple, with no need to remember which HTTP status code should be returned for which response types.

## Response Examples

The following example shows a common usage pattern within your controllers.

```php
<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class Users extends Controller
{
    use ResponseTrait;

    public function createUser()
    {
        $model = new UserModel();
        $user  = $model->save($this->request->getPost());

        // Respond with 201 status code
        return $this->respondCreated();
    }
}
```

In this example, an HTTP status code of 201 is returned, with the generic status message, 'Created'. Methods exist for the most common use cases:

```php
<?php

// Generic response method
$this->respond($data, 200);

// Generic failure response
$this->fail($errors, 400);

// Item created response
$this->respondCreated($data);

// Item successfully deleted
$this->respondDeleted($data);

// Command executed by no response required
$this->respondNoContent($message);

// Client isn't authorized
$this->failUnauthorized($description);

// Forbidden action
$this->failForbidden($description);

// Resource Not Found
$this->failNotFound($description);

// Data was not validated
$this->failValidationErrors($errors);

// Resource already exists
$this->failResourceExists($description);

// Resource previously deleted
$this->failResourceGone($description);

// Client made too many requests
$this->failTooManyRequests($description);
```

## Handling Response Types

When you pass your data to any of these methods, they will determine the data type to format the results as based on the following criteria:

- The format is determined according to the controller's `$this->format` value. If that is `null`, it will try to negotiate the content type with what the client asked for, defaulting to the first element (JSON by default) in the `$supportedResponseFormats` property within **app/Config/Format.php**.
- The data will be formatted according to the format. If the format is not JSON and data is a string, it will be treated as HTML to send back to the client.

!!! note "Note"
    Prior to v4.5.0, due to a bug, if data is a string, it will be treated as HTML even if the format is JSON.

To define the formatter that is used, edit **app/Config/Format.php**. The `$supportedResponseFormats` property contains a list of MIME types that your application can automatically format the response for. By default, the system knows how to format both XML and JSON responses:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Format extends BaseConfig
{
    public $supportedResponseFormats = [
        'application/json',
        'application/xml',
    ];

    // ...
}
```

!!! note "Note"
    Since `v4.7.0`, you can change the default JSON encoding depth by editing the **app/Config/Format.php** file. The `$jsonEncodeDepth` value defines the maximum depth, with a default of `512`.

This is the array that is used during [Content Negotiation](../incoming/content_negotiation.md) to determine which type of response to return. If no matches are found between what the client requested and what you support, the first format in this array is what will be returned.

Next, you need to define the class that is used to format the array of data. This must be a fully qualified class name, and the class must implement `CodeIgniter\Format\FormatterInterface`. Formatters come out of the box that support both JSON and XML:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Format extends BaseConfig
{
    public $formatters = [
        'application/json' => \CodeIgniter\Format\JSONFormatter::class,
        'application/xml'  => \CodeIgniter\Format\XMLFormatter::class,
    ];

    // ...
}
```

So, if your request asks for JSON formatted data in an **Accept** header, the data array you pass to any of the `respond*` or `fail*` methods will be formatted by the `CodeIgniter\Format\JSONFormatter` class. The resulting JSON data will be sent back to the client.

## Class Reference

#### setResponseFormat($format)

- **Parameters**  
    - **$format** `string` The type of response to return, either `json` or `xml`

This defines the format to be used when formatting arrays in responses. If you provide a `null` value for `$format`, it will be automatically determined through content negotiation.

```php
<?php

return $this->setResponseFormat('json')->respond(['error' => false]);
```

#### respond($data[, $statusCode = 200[, $message = '']])

- **Parameters**  
    - **$data** `mixed` The data to return to the client. Either string or array.
    - **$statusCode** `int` The HTTP status code to return. Defaults to 200
    - **$message** `string` A custom "reason" message to return.

This is the method used by all other methods in this trait to return a response to the client.

The `$data` element can be either a string or an array. By default, a string will be returned as HTML, while an array will be run through json_encode and returned as JSON, unless [Content Negotiation](../incoming/content_negotiation.md) determines it should be returned in a different format.

If a `$message` string is passed, it will be used in place of the standard IANA reason codes for the response status. Not every client will respect the custom codes, though, and will use the IANA standards that match the status code.

!!! note "Note"
    Since it sets the status code and body on the active Response instance, this should always be the final method in the script execution.

#### fail($messages[, int $status = 400[, string $code = null[, string $message = '']]])

- **Parameters**  
    - **$messages** `mixed` A string or array of strings that contain error messages encountered.
    - **$status** `int` The HTTP status code to return. Defaults to 400.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: A multi-part response in the client's preferred format.

This is the generic method used to represent a failed response, and is used by all of the other "fail" methods.

The `$messages` element can be either a string or an array of strings.

The `$status` parameter is the HTTP status code that should be returned.

Since many APIs are better served using custom error codes, a custom error code can be passed in the third parameter. If no value is present, it will be the same as `$status`.

If a `$message` string is passed, it will be used in place of the standard IANA reason codes for the response status. Not every client will respect the custom codes, though, and will use the IANA standards that match the status code.

The response is an array with three elements: `status`, `code`, and `messages`.

- The `status` element contains the status code of the error.
- The `code` element contains a custom, API-specific error code.
- The `messages` element contains an array of error messages.

Depending on the number of error messages, the response would look something like:

```php
<?php

// Example response with a single error message
$response = [
    'status'   => 400,
    'code'     => '321',
    'messages' => [
        'error' => 'An error occurred',
    ],
];

// Example response with multiple error messages per field
$response = [
    'status'   => 400,
    'code'     => '321a',
    'messages' => [
        'foo' => 'Error message 1',
        'bar' => 'Error message 2',
    ],
];
```

#### respondCreated($data = null[, string $message = ''])

- **Parameters**  
    - **$data** `mixed` The data to return to the client. Either string or array.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when a new resource was created, typically 201:

```php
<?php

$user = $userModel->insert($data);

return $this->respondCreated($user);
```

#### respondDeleted($data = null[, string $message = ''])

- **Parameters**  
    - **$data** `mixed` The data to return to the client. Either string or array.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when a resource was deleted as the result of this API call, typically 200.

```php
<?php

$user = $userModel->delete($id);

return $this->respondDeleted(['id' => $id]);
```

#### respondNoContent(string $message = 'No Content')

- **Parameters**  
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when a command was successfully executed by the server but there is no meaningful reply to send back to the client, typically 204.

```php
<?php

sleep(1);

return $this->respondNoContent();
```

#### failUnauthorized(string $description = 'Unauthorized'[, string $code = null[, string $message = '']])

- **Parameters**  
    - **$description** `string` The error message to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when the user either has not been authorized, or has incorrect authorization. Status code is 401.

```php
<?php

return $this->failUnauthorized('Invalid Auth token');
```

#### failForbidden(string $description = 'Forbidden'[, string $code=null[, string $message = '']])

- **Parameters**  
    - **$description** `string` The error message to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Unlike `failUnauthorized()`, this method should be used when the requested API endpoint is never allowed. Unauthorized implies the client is encouraged to try again with different credentials. Forbidden means the client should not try again because it won't help. Status code is 403.

```php
<?php

return $this->failForbidden('Invalid API endpoint.');
```

#### failNotFound(string $description = 'Not Found'[, string $code=null[, string $message = '']])

- **Parameters**  
    - **$description** `string` The error message to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when the requested resource cannot be found. Status code is 404.

```php
<?php

return $this->failNotFound('User 13 cannot be found.');
```

#### failValidationErrors($errors[, string $code=null[, string $message = '']])

- **Parameters**  
    - **$errors** `mixed` The error message or array of messages to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when data the client sent did not pass validation rules. Status code is typically 400.

```php
<?php

return $this->failValidationErrors($validation->getErrors());
```

#### failResourceExists(string $description = 'Conflict'[, string $code=null[, string $message = '']])

- **Parameters**  
    - **$description** `string` The error message to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when the resource the client is trying to create already exists. Status code is typically 409.

```php
<?php

return $this->failResourceExists('A user already exists with that email.');
```

#### failResourceGone(string $description = 'Gone'[, string $code=null[, string $message = '']])

- **Parameters**  
    - **$description** `string` The error message to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when the requested resource was previously deleted and is no longer available. Status code is typically 410.

```php
<?php

return $this->failResourceGone('That user has been previously deleted.');
```

#### failTooManyRequests(string $description = 'Too Many Requests'[, string $code=null[, string $message = '']])

- **Parameters**  
    - **$description** `string` The error message to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when the client has called an API endpoint too many times. This might be due to some form of throttling or rate limiting. Status code is typically 429.

```php
<?php

return $this->failTooManyRequests('You must wait 15 seconds before making another request.');
```

#### failServerError(string $description = 'Internal Server Error'[, string $code = null[, string $message = '']])

- **Parameters**  
    - **$description** `string` The error message to show the user.
    - **$code** `string` A custom, API-specific, error code.
    - **$message** `string` A custom "reason" message to return.

- **Returns**: The value of the Response object's `send()` method.

Sets the appropriate status code to use when there is a server error.

```php
<?php

return $this->failServerError('Server error.');
```

## Pagination Responses

When returning paginated results from an API endpoint, you can use the `paginate()` method to return the results along with the pagination information. This helps to keep consistent responses across your API, while providing all of the information that clients will need to properly page through the results.

### Example Usage

```php
<?php

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $model = model(UserModel::class)
            ->where('active', 1);

        return $this->paginate($model, 20);
    }
}
```

A typical response might look like:

```json
{
    "data": [
        {
            "id": 1,
            "username": "admin",
            "email": "admin@example.com"
        },
        {
            "id": 2,
            "username": "user",
            "email": "user@example.com"
        }
    ],
    "meta": {
        "page": 1,
        "perPage": 20,
        "total": 2,
        "totalPages": 1
    },
    "links": {
        "self": "http://example.com/users?page=1",
        "first": "http://example.com/users?page=1",
        "last": "http://example.com/users?page=1",
        "next": null,
        "prev": null
    }
}
```

The `paginate()` method will always wrap the results in a `data` element, and will also include `meta` and `links` elements to help the client page through the results. If there are no results, the `data` element will be an empty array, and the `meta` and `links` elements will still be present, but with values that indicate no results.

You can also pass it a Builder instance instead of a Model, as long as the Builder is properly configured with the table name and any necessary joins or where clauses.

```php
<?php

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $builder = db_connect()
            ->table('users')
            ->where('active', 1);

        return $this->paginate(resource: $builder, perPage: 20);
    }
}
```

## Class Reference

#### paginate(Model|BaseBuilder $resource, int $perPage = 20, ?string $transformWith = null)

- **Parameters**  
    - **$resource** `Model|BaseBuilder` The resource to paginate, either a Model or a Builder instance.
    - **$perPage** `int` The number of items to return per page.
    - **$transformWith** `string|null` Optional transformer class name to transform the results.

Generates a paginated response from the given resource. The resource can be either a Model or a Builder instance. The method will automatically determine the current page from the request's query parameters. The response will include the paginated data, along with metadata about the pagination state and links to navigate through the pages.

If you provide a `$transformWith` parameter with a transformer class name, each item in the paginated results will be transformed using that transformer before being returned. This is useful for controlling the structure and content of your API responses. See [API Transformers](api_transformers.md#api-resources) for more information on creating and using transformers.

Example with transformer:

```php
<?php

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Transformers\UserTransformer;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $model = model(UserModel::class);

        return $this->paginate(resource: $model, perPage: 20, transformWith: UserTransformer::class);
    }
}
```
