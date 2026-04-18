<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Exceptions\RuntimeException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @see \CodeIgniter\HTTP\FormRequestTest
 */
abstract class FormRequest
{
    /**
     * The underlying HTTP request instance.
     */
    protected IncomingRequest $request;

    /**
     * Data that passed validation (only the fields covered by rules()).
     *
     * @var array<string, mixed>
     */
    private array $validatedData = [];

    /**
     * When called by the framework, the current IncomingRequest is injected
     * explicitly. When instantiated manually (e.g. in tests), the constructor
     * falls back to service('request').
     */
    final public function __construct(?Request $request = null)
    {
        $request ??= service('request');

        if (! $request instanceof IncomingRequest) {
            throw new RuntimeException(
                sprintf('%s requires an IncomingRequest instance, got %s.', static::class, $request::class),
            );
        }

        $this->request = $request;
    }

    /**
     * Validation rules for this request.
     *
     * Return an array of field => rules pairs, identical to what you would
     * pass to $this->validate() in a controller:
     *
     *  return [
     *      'title' => 'required|min_length[5]',
     *      'body'  => ['required', 'max_length[10000]'],
     *  ];
     *
     * @return array<string, list<string>|string>
     */
    abstract public function rules(): array;

    /**
     * Custom error messages keyed by field.rule.
     *
     *  return [
     *      'title' => ['required' => 'Post title cannot be empty.'],
     *  ];
     *
     * @return array<string, array<string, string>>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Determine if the current user is authorized to make this request.
     *
     * Override in subclasses to add authorization logic:
     *
     *  public function isAuthorized(): bool
     *  {
     *      return auth()->user()->can('create-posts');
     *  }
     */
    public function isAuthorized(): bool
    {
        return true;
    }

    /**
     * Returns the class name when the given reflection parameter is typed as a
     * FormRequest subclass, or null otherwise. Used by the dispatcher and
     * auto-router to distinguish injectable parameters from URI-segment parameters.
     *
     * @internal
     *
     * @return class-string<self>|null
     */
    final public static function getFormRequestClass(ReflectionParameter $param): ?string
    {
        $type = $param->getType();

        if (
            $type instanceof ReflectionNamedType
            && ! $type->isBuiltin()
            && is_subclass_of($type->getName(), self::class)
        ) {
            return $type->getName();
        }

        return null;
    }

    /**
     * Modify the request data before validation rules are applied.
     * Override to normalize or cast input values:
     *
     *  protected function prepareForValidation(array $data): array
     *  {
     *      $data['slug'] = url_title($data['title'] ?? '', '-', true);
     *      return $data;
     *  }
     *
     * The $data array is the same payload that will be passed to the
     * validator. Return the (possibly modified) array.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function prepareForValidation(array $data): array
    {
        return $data;
    }

    /**
     * Called when validation fails. Override to customize the failure response.
     *
     * The default implementation redirects back with input and flashes validation
     * errors via the standard ``_ci_validation_errors`` channel (the same channel
     * used by controller-level validation and readable by ``validation_errors()``
     * helpers). For JSON/AJAX requests it returns a 422 JSON response instead.
     *
     * @param array<string, string> $errors
     */
    protected function failedValidation(array $errors): ResponseInterface
    {
        if ($this->request->is('json') || $this->request->isAJAX()) {
            return service('response')->setStatusCode(422)->setJSON(['errors' => $errors]);
        }

        return redirect()->back()->withInput();
    }

    /**
     * Called when the isAuthorized() check returns false. Override to customize.
     */
    protected function failedAuthorization(): ResponseInterface
    {
        return service('response')->setStatusCode(403);
    }

    /**
     * Returns only the fields that passed validation (those covered by rules()).
     *
     * Prefer this over $this->request->getPost() in controllers to avoid
     * processing fields that were not declared in the rules.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        return $this->validatedData;
    }

    /**
     * Returns a single validated field value by name, or the default value
     * if the field is not present in the validated data.
     *
     * Supports dot-array syntax for nested validated data.
     */
    public function getValidated(string $key, mixed $default = null): mixed
    {
        helper('array');

        if (! dot_array_has($key, $this->validatedData)) {
            return $default;
        }

        return dot_array_search($key, $this->validatedData);
    }

    /**
     * Returns true when the named field exists in the validated data, even if
     * its value is null.
     *
     * Supports dot-array syntax for nested validated data.
     */
    public function hasValidated(string $key): bool
    {
        helper('array');

        return dot_array_has($key, $this->validatedData);
    }

    /**
     * Returns the data to be validated.
     *
     * Override this method to provide custom data or to merge data from
     * multiple sources. By default, data is sourced from the appropriate
     * part of the request based on HTTP method and Content-Type:
     *
     * - JSON (any method)      - decoded JSON body
     * - PUT / PATCH / DELETE   - raw body (unless multipart/form-data)
     * - GET / HEAD             - query-string parameters
     * - Everything else (POST) - POST body
     *
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            return $this->request->getJSON(true) ?? [];
        }

        if (
            in_array($this->request->getMethod(), [Method::PUT, Method::PATCH, Method::DELETE], true)
            && ! str_contains($contentType, 'multipart/form-data')
        ) {
            return $this->request->getRawInput() ?? [];
        }

        if (in_array($this->request->getMethod(), [Method::GET, Method::HEAD], true)) {
            return $this->request->getGet() ?? [];
        }

        return $this->request->getPost() ?? [];
    }

    /**
     * Runs authorization and validation. Called by the framework before
     * injecting the FormRequest into the controller method.
     *
     * Returns null on success, or a ResponseInterface to short-circuit the
     * request when authorization or validation fails.
     *
     * Do not call this method directly unless you are inside a ``_remap()``
     * method, where automatic injection is not available.
     */
    final public function resolveRequest(): ?ResponseInterface
    {
        if (! $this->isAuthorized()) {
            return $this->failedAuthorization();
        }

        $data = $this->prepareForValidation($this->validationData());

        $validation = service('validation')
            ->setRules($this->rules(), $this->messages());

        if (! $validation->run($data)) {
            return $this->failedValidation($validation->getErrors());
        }

        $this->validatedData = $validation->getValidated();

        return null;
    }
}
