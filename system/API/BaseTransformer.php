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

namespace CodeIgniter\API;

use CodeIgniter\HTTP\IncomingRequest;
use InvalidArgumentException;

/**
 * Base class for transforming resources into arrays.
 * Fulfills common functionality of the TransformerInterface,
 * and provides helper methods for conditional inclusion/exclusion of values.
 *
 * Supports the following query variables from the request:
 * - fields: Comma-separated list of fields to include in the response
 *      (e.g., ?fields=id,name,email)
 *      If not provided, all fields from toArray() are included.
 * - include: Comma-separated list of related resources to include
 *      (e.g., ?include=posts,comments)
 *      This looks for methods named `include{Resource}()` on the transformer,
 *      and calls them to get the related data, which are added as a new key to the output.
 *
 * Example:
 *
 * class UserTransformer extends BaseTransformer
 * {
 *    public function toArray(mixed $resource): array
 *    {
 *      return [
 *          'id' => $resource['id'],
 *          'name' => $resource['name'],
 *          'email' => $resource['email'],
 *          'created_at' => $resource['created_at'],
 *          'updated_at' => $resource['updated_at'],
 *      ];
 *    }
 *
 *   protected function includePosts(): array
 *   {
 *       $posts = model('PostModel')->where('user_id', $this->resource['id'])->findAll();
 *       return (new PostTransformer())->transformMany($posts);
 *   }
 * }
 */
abstract class BaseTransformer implements TransformerInterface
{
    /**
     * @var list<string>|null
     */
    private ?array $fields = null;

    /**
     * @var list<string>|null
     */
    private ?array $includes = null;

    protected mixed $resource = null;

    public function __construct(
        private ?IncomingRequest $request = null,
    ) {
        $this->request = $request ?? request();

        $fields       = $this->request->getGet('fields');
        $this->fields = is_string($fields)
            ? array_map(trim(...), explode(',', $fields))
            : $fields;

        $includes       = $this->request->getGet('include');
        $this->includes = is_string($includes)
            ? array_map(trim(...), explode(',', $includes))
            : $includes;
    }

    /**
     * Converts the resource to an array representation.
     * This is overridden by child classes to define the
     * API-safe resource representation.
     *
     * @param mixed $resource The resource being transformed
     */
    abstract public function toArray(mixed $resource): array;

    /**
     * Transforms the given resource into an array using
     * the $this->toArray().
     */
    public function transform(array|object|null $resource = null): array
    {
        // Store the resource so include methods can access it
        $this->resource = $resource;

        if ($resource === null) {
            $data = $this->toArray(null);
        } elseif (is_object($resource) && method_exists($resource, 'toArray')) {
            $data = $this->toArray($resource->toArray());
        } else {
            $data = $this->toArray((array) $resource);
        }

        $data = $this->limitFields($data);

        return $this->insertIncludes($data);
    }

    /**
     * Transforms a collection of resources using $this->transform() on each item.
     *
     * If the request's 'fields' query variable is set, only those fields will be included
     * in the transformed output.
     */
    public function transformMany(array $resources): array
    {
        return array_map($this->transform(...), $resources);
    }

    /**
     * Define which fields can be requested via the 'fields' query parameter.
     * Override in child classes to restrict available fields.
     * Return null to allow all fields from toArray().
     *
     * @return list<string>|null
     */
    protected function getAllowedFields(): ?array
    {
        return null;
    }

    /**
     * Define which related resources can be included via the 'include' query parameter.
     * Override in child classes to restrict available includes.
     * Return null to allow all includes that have corresponding methods.
     * Return an empty array to disable all includes.
     *
     * @return list<string>|null
     */
    protected function getAllowedIncludes(): ?array
    {
        return null;
    }

    /**
     * Limits the given data array to only the fields specified
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    private function limitFields(array $data): array
    {
        if ($this->fields === null || $this->fields === []) {
            return $data;
        }

        $allowedFields = $this->getAllowedFields();

        // If whitelist is defined, validate against it
        if ($allowedFields !== null) {
            $invalidFields = array_diff($this->fields, $allowedFields);

            if ($invalidFields !== []) {
                throw ApiException::forInvalidFields(implode(', ', $invalidFields));
            }
        }

        return array_intersect_key($data, array_flip($this->fields));
    }

    /**
     * Checks the request for 'include' query variable, and if present,
     * calls the corresponding include{Resource} methods to add related data.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function insertIncludes(array $data): array
    {
        if ($this->includes === null) {
            return $data;
        }

        $allowedIncludes = $this->getAllowedIncludes();

        if ($allowedIncludes === []) {
            return $data; // No includes allowed
        }

        // If whitelist is defined, filter the requested includes
        if ($allowedIncludes !== null) {
            $invalidIncludes = array_diff($this->includes, $allowedIncludes);

            if ($invalidIncludes !== []) {
                throw ApiException::forInvalidIncludes(implode(', ', $invalidIncludes));
            }
        }

        foreach ($this->includes as $include) {
            $method = 'include' . ucfirst($include);
            if (method_exists($this, $method)) {
                $data[$include] = $this->{$method}();
            } else {
                throw ApiException::forMissingInclude($include);
            }
        }

        return $data;
    }
}
