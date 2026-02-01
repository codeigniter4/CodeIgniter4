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
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $resource): array;

    /**
     * Transforms the given resource into an array.
     *
     * @param array<string, mixed>|object|null $resource
     *
     * @return array<string, mixed>
     */
    public function transform(array|object|null $resource): array;

    /**
     * Transforms a collection of resources using $this->transform() on each item.
     *
     * @param array<int|string, mixed> $resources
     *
     * @return array<int, array<string, mixed>>
     */
    public function transformMany(array $resources): array;
}
