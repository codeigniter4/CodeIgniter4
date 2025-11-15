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

namespace CodeIgniter\Test;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

/**
 * Testing helper.
 */
trait ReflectionHelper
{
    /**
     * Find a private method invoker.
     *
     * @param object|string $obj    object or class name
     * @param string        $method method name
     *
     * @return Closure(mixed ...$args): mixed
     *
     * @throws ReflectionException
     */
    public static function getPrivateMethodInvoker($obj, $method)
    {
        $refMethod = new ReflectionMethod($obj, $method);
        $obj       = (gettype($obj) === 'object') ? $obj : null;

        return static fn (...$args): mixed => $refMethod->invokeArgs($obj, $args);
    }

    /**
     * Find an accessible property.
     *
     * @param object|string $obj
     * @param string        $property
     *
     * @return ReflectionProperty
     *
     * @throws ReflectionException
     */
    private static function getAccessibleRefProperty($obj, $property)
    {
        $refClass = is_object($obj) ? new ReflectionObject($obj) : new ReflectionClass($obj);

        return $refClass->getProperty($property);
    }

    /**
     * Set a private property.
     *
     * @param object|string $obj      object or class name
     * @param string        $property property name
     * @param mixed         $value    value
     *
     * @throws ReflectionException
     */
    public static function setPrivateProperty($obj, $property, $value): void
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);

        if (is_object($obj)) {
            $refProperty->setValue($obj, $value);
        } else {
            $refProperty->setValue(null, $value);
        }
    }

    /**
     * Retrieve a private property.
     *
     * @param object|string $obj      object or class name
     * @param string        $property property name
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public static function getPrivateProperty($obj, $property)
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);

        return is_string($obj) ? $refProperty->getValue() : $refProperty->getValue($obj);
    }
}
