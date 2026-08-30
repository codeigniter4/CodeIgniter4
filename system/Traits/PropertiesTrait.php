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

namespace CodeIgniter\Traits;

use Closure;
use ReflectionClass;
use ReflectionProperty;

/**
 * Trait PropertiesTrait
 *
 * Provides utilities for reading and writing
 * class properties, primarily for limiting access
 * to public properties.
 */
trait PropertiesTrait
{
    /**
     * Attempts to set the values of public class properties.
     *
     * @param array<string, mixed> $params
     */
    final public function fill(array $params): self
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Get the public properties of the class and return as an array.
     *
     * @return array<string, mixed>
     */
    final public function getPublicProperties(): array
    {
        $fn = fn (): array => get_object_vars($this);

        $bound = Closure::bind($fn, $this, null);

        return $bound();
    }

    /**
     * Get the protected and private properties of the class and return as an array.
     *
     * @return list<ReflectionProperty>
     */
    final public function getNonPublicProperties(): array
    {
        $exclude    = ['view'];
        $properties = [];

        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED) as $property) {
            if ($property->isStatic() || in_array($property->getName(), $exclude, true)) {
                continue;
            }

            $properties[] = $property;
        }

        return $properties;
    }
}
