<?php

declare(strict_types=1);

namespace CodeIgniter\API;

/**
 * Interface for transforming resources into arrays.
 *
 * This interface can be implemented by classes that need to transform
 * data into a standardized array format, such as for API responses.
 */
interface TransformerInterface
{
    /**
     * Converts the resource to an array representation.
     * This is overridden by child classes to define specific fields.
     *
     * @param mixed $resource The resource being transformed
     */
    public function toArray(mixed $resource): array;

    /**
     * Transforms the given resource into an array.
     */
    public function transform(object|array $resource): array;

    /**
     * Transforms a collection of resources using $this->transform() on each item.
     */
    public function transformMany(array $resources): array;
}
