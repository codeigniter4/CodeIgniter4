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
     * @return $this
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
     */
    final public function getPublicProperties(): array
    {
        $worker = new class () {
            public function getProperties(object $obj): array
            {
                return get_object_vars($obj);
            }
        };

        return $worker->getProperties($this);
    }

    /**
     * Get the protected and private properties of the class and return as an array.
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

            $property->setAccessible(true);
            $properties[] = $property;
        }

        return $properties;
    }
}
