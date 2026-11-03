# Form Requests

!!! success "Available from version 4.8.0"

A **FormRequest** is a dedicated class that encapsulates the validation rules, custom error messages, and authorization logic for a single HTTP request. Instead of writing validation code inside every controller method, you define it once in a FormRequest class and type-hint it in the controller method signature - the framework resolves, authorizes, and validates the request automatically before your method body runs.

## Creating a Form Request

Use the `make:request` Spark command to generate a new FormRequest class:

```
php spark make:request StorePostRequest
```

This creates **app/Requests/StorePostRequest.php**. Fill in the `rules()` method with the same field/rule pairs you would normally pass to [Running Validation](../libraries/validation.md#running-validation):

```php
namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]|max_length[255]',
            'body'  => 'required',
        ];
    }
}
```

## Using a Form Request in a Controller

Type-hint the FormRequest class as a parameter of your controller method. The framework instantiates it, runs authorization and validation, and passes the resolved object to your method. If validation fails, the default behavior redirects back with errors for web requests or returns a 422 JSON response for JSON request bodies or requests that prefer `application/json` - the method body is never reached.

```php
namespace App\Controllers;

use App\Requests\StorePostRequest;

class Posts extends BaseController
{
    public function store(StorePostRequest $request): string
    {
        // $request->getValidated() returns only the fields declared in rules().
        $data = $request->getValidated();

        // save to database

        return redirect()->to('/posts');
    }
}
```

## Accessing Validated Data

`getValidated()` returns an array containing only the fields that were declared in `rules()`. Fields submitted by the client that are not covered by a rule are silently discarded, protecting against mass-assignment.

```php
$data = $request->getValidated();
// ['title' => 'My post title', 'body' => 'Body text']
```

Use `getValidatedInput()` when you want to read a single validated field or check whether a validated key exists, including keys whose value is `null`. The input object supports dot-array syntax for nested validated data:

```php
$input = $request->getValidatedInput();

$title = $input->get('title');
$slug  = $input->get('post.meta.slug', 'draft');

if ($input->has('note')) {
    // 'note' was validated, even if its value is null
}
```

### Typed Validated Input

`getValidatedInput()` returns the same validated data as a typed input object. This complements `getValidated()` by making common controller values easier to read after validation has succeeded.

After the FormRequest has been validated, read the successful values in the controller:

```php
use App\Enums\PostStatus;

$input = $request->getValidatedInput();

$page        = $input->integer('page', 1);
$rating      = $input->float('rating', 0.0);
$active      = $input->boolean('active', false);
$publishedAt = $input->date('published_at', 'Y-m-d');
$status      = $input->enum('status', PostStatus::class, PostStatus::DRAFT);
```

These typed methods do not replace validation rules. They only make accepted values easier to consume in the controller. See [Typed Validated Input](../libraries/validation.md#typed-validated-input) for the full behavior of the typed input methods.

### Accessing Other Request Data

For anything not covered by `getValidated()` - uploaded files, request headers, the client IP address, raw input, and so on - use `$this->request` as usual. It is the same [IncomingRequest](incomingrequest.md) instance that the FormRequest uses internally:

```php
namespace App\Controllers;

use App\Requests\StorePostRequest;

class Posts extends BaseController
{
    public function store(StorePostRequest $request): string
    {
        $data  = $request->getValidated();
        $files = $this->request->getFiles();

        // ...
    }
}
```

## Route Parameters Alongside a Form Request

Required scalar route parameters come first, then the FormRequest, then any optional scalar parameters. The framework matches URI segments to scalar parameters positionally and injects the FormRequest wherever the type hint appears. Parameters declared with PHP's `...` syntax capture all remaining URI segments. Optional scalar parameters after the FormRequest get their default values when no URI segment is left to fill them.

```php
namespace App\Controllers;

use App\Requests\UpdatePostRequest;

class Posts extends BaseController
{
    // Route parameters come first; FormRequest follows.
    public function update(int $id, UpdatePostRequest $request): string
    {
        $data = $request->getValidated();

        // update post $id with $data

        return redirect()->to('/posts/' . $id);
    }
}
```

## Closure Routes

FormRequest injection works identically in closure routes and follows the same signature shape: route parameters first, then the FormRequest, then optional scalar parameters. The framework resolves it the same way it does for controller methods.

```php
use App\Requests\StorePostRequest;
use App\Requests\UpdatePostRequest;

// In app/Config/Routes.php

// FormRequest only - no route parameters.
$routes->post('posts', static function (StorePostRequest $request): string {
    $data = $request->getValidated();

    // save to database

    return redirect()->to('/posts');
});

// Route parameter before a FormRequest - same convention as controller methods.
$routes->post('posts/(:num)', static function (int $id, UpdatePostRequest $request): string {
    $data = $request->getValidated();

    // update post $id with $data

    return redirect()->to('/posts/' . $id);
});
```

## `_remap()` Methods

Automatic FormRequest injection is **not** supported for controller methods that use `_remap()`. Because `_remap()` has a fixed signature `($method, ...$params)`, there is no typed position for the framework to inject a FormRequest into.

Instantiate the FormRequest manually inside `_remap()` and call `resolveRequest()` yourself. The method returns `null` on success or a `ResponseInterface` when authorization or validation fails:

```php
namespace App\Controllers;

use App\Requests\UpdatePostRequest;
use CodeIgniter\HTTP\ResponseInterface;

class Posts extends BaseController
{
    public function _remap(string $method, string ...$params): mixed
    {
        // FormRequest is not injected automatically into _remap().
        // Instantiate it manually and call resolveRequest() yourself.
        if ($method === 'update') {
            $request  = new UpdatePostRequest();
            $response = $request->resolveRequest();

            if ($response instanceof ResponseInterface) {
                // Authorization or validation failed - send the response.
                return $response;
            }

            return $this->update($request, ...$params);
        }

        return $this->{$method}(...$params);
    }

    private function update(UpdatePostRequest $request, string $id): string
    {
        $data = $request->getValidated();

        // update post $id with $data

        return redirect()->to('/posts/' . $id);
    }
}
```

## Custom Error Messages

Override `messages()` to return field-specific error messages. The format is identical to the `$errors` argument of [Saving Sets of Validation Rules to the Config File](../libraries/validation.md#saving-sets-of-validation-rules-to-the-config-file):

```php
namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]|max_length[255]',
            'body'  => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'title' => [
                'required'   => 'Post title cannot be empty.',
                'min_length' => 'Post title must be at least {param} characters long.',
            ],
            'body' => [
                'required' => 'Post body cannot be empty.',
            ],
        ];
    }
}
```

## Authorization

Override `isAuthorized()` to control whether the current user is allowed to make this request. Return `false` to reject the request with a 403 Forbidden response before validation even runs.

```php
namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'body'  => 'required',
        ];
    }

    public function isAuthorized(): bool
    {
        // Only authenticated users may submit posts.
        return auth()->loggedIn();
    }
}
```

!!! note "Note"
    The `isAuthorized()` check runs before `prepareForValidation()` and before validation itself. An unauthorized request never reaches the validation stage.

## Preparing Data Before Validation

Override `prepareForValidation(array $data): array` to normalize or derive input values before the validation rules are applied. The method receives the same data array that will be passed to the validator and must return the (possibly modified) array. This is useful for computed fields such as slugs, normalized phone numbers, or trimmed strings.

```php
namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'slug'  => 'required|is_unique[posts.slug]',
        ];
    }

    protected function prepareForValidation(array $data): array
    {
        // Derive the slug from the title so it is available for the
        // is_unique rule before validation runs.
        $data['slug'] = url_title($data['title'] ?? '', '-', true);

        return $data;
    }
}
```

!!! note "Note"
    When validation fails and the default redirect response is used, `old()` returns the prepared validation data. Use `getValidated()` to access the processed data after a successful request.

## Customizing the Data Source

By default `validationData()` selects the appropriate data source automatically based on the HTTP method and `Content-Type` header:

- **JSON request** (`Content-Type: application/json`) -> decoded JSON body
- **PUT / PATCH / DELETE / QUERY** (non-multipart) -> raw body via `getRawInput()`
- **GET / HEAD** -> query-string parameters via `getGet()`
- **Everything else** (POST, multipart) -> POST body via `getPost()`

This avoids the pitfalls of [withRequest()](../libraries/validation.md#withrequest), which mixes GET and POST data via `getVar()`.

Override `validationData()` when you need a different data source - for example, to merge GET and POST parameters:

```php
namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return ['q' => 'required|min_length[2]'];
    }

    // Merge query-string parameters and POST body for this endpoint.
    protected function validationData(): array
    {
        return array_merge(
            $this->request->getGet() ?? [],
            $this->request->getPost() ?? [],
        );
    }
}
```

## Customizing Failure Behavior

Override `failedValidation()` and `failedAuthorization()` to take full control of what happens when a request is rejected. Both methods return a `ResponseInterface` that the framework sends to the client:

```php
namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;
use CodeIgniter\HTTP\ResponseInterface;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'body'  => 'required',
        ];
    }

    // Always respond with JSON, regardless of the request type.
    protected function failedValidation(array $errors, array $preparedData): ResponseInterface
    {
        return service('response')->setStatusCode(422)->setJSON(['errors' => $errors]);
    }

    // Always respond with 403, regardless of the request type.
    protected function failedAuthorization(): ResponseInterface
    {
        return service('response')->setStatusCode(403);
    }
}
```

The default `failedValidation()` returns a 422 JSON response for JSON request bodies or requests that prefer `application/json` through the `Accept` header. Otherwise, it redirects back with input and validation errors.

!!! note "Note"
    The `X-Requested-With: XMLHttpRequest` header alone does not select a JSON response. If an AJAX client expects JSON validation errors, send an `Accept: application/json` header. If your application needs HTML fragments for AJAX form failures, override `failedValidation()`.

### Flashing Normalized Input

If your `prepareForValidation()` transforms visible form fields (for example, trimming strings or canonicalizing values), the default redirect response flashes the prepared validation data as old input.

If you override `failedValidation()` and still need to flash normalized input, use the second `$preparedData` argument. It contains the same data that was passed to validation:

```php
namespace App\Requests;

use CodeIgniter\HTTP\FormRequest;
use CodeIgniter\HTTP\ResponseInterface;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|min_length[3]',
            'slug'  => 'required|is_unique[posts.slug]',
        ];
    }

    protected function prepareForValidation(array $data): array
    {
        $data['slug'] = url_title($data['title'] ?? '', '-', true);

        return $data;
    }

    // Override while still flashing the prepared values on redirect.
    protected function failedValidation(array $errors, array $preparedData): ResponseInterface
    {
        if (
            $this->request->is('json')
            || $this->request->negotiate('media', ['text/html', 'application/json'], true) === 'application/json'
        ) {
            return service('response')->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        // withInput() flashes validation errors. Then we replace old input with
        // the same prepared values that were passed to validation.
        $redirect = redirect()->back()->withInput();

        service('session')->setFlashdata('_ci_old_input', [
            'get'  => [],
            'post' => $preparedData,
        ]);

        return $redirect;
    }
}
```

The prepared data has not passed validation. After successful validation, use `getValidated()` or `getValidatedInput()` for trusted values.

## How the Framework Resolves Form Requests

When the router dispatches a controller method or closure route, the framework inspects the callable's parameter list using reflection. For each parameter whose type extends `FormRequest`:

1.  A new instance is created with the current request injected via the constructor.
2.  `isAuthorized()` is called. If it returns `false`, `failedAuthorization()` is called, and its response is returned to the client.
3.  `validationData()` collects the data to validate.
4.  `prepareForValidation()` receives that data and may modify it before the rules are applied.
5.  `run()` executes the validation rules. If it fails, `failedValidation()` is called, and its response is returned to the client.
6.  The validated data is stored internally and available via `getValidated()` and `getValidatedInput()`.
7.  The resolved FormRequest object is injected into the controller method or closure.

The callable is never invoked if authorization or validation fails. Non-FormRequest parameters consume URI route segments in declaration order; variadic parameters receive all remaining segments.

!!! note "Note"
    Automatic injection does not apply to `_remap()` methods. See [\_remap() Methods](#\_remap-methods) above for the manual workaround.
