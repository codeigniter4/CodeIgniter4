<?php

namespace CodeIgniter\Traits;

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
        $publicProperties = array_keys($this->getPublicProperties());

        foreach ($params as $key => $value) {
            if (in_array($key, $publicProperties, true)) {
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
        $properties = [];

        $worker = new class {
            public function getProperties($obj) {
                return get_object_vars($obj);
            }
        };

        $properties = $worker->getProperties($this);

        return $properties;
    }

    /**
     * Get the protected and private properties of the class and return as an array.
     */
    final public function getNonPublicProperties(): array
    {
        $properties = [];

        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);
            $properties[] = $property;
        }

        return $properties;
    }
}
