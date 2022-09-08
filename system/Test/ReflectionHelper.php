<?php

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
     * @return Closure
     *
     * @throws ReflectionException
     */
    public static function getPrivateMethodInvoker($obj, $method)
    {
        $refMethod = new ReflectionMethod($obj, $method);
        $refMethod->setAccessible(true);
        $obj = (gettype($obj) === 'object') ? $obj : null;

        return static fn (...$args) => $refMethod->invokeArgs($obj, $args);
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

        $refProperty = $refClass->getProperty($property);
        $refProperty->setAccessible(true);

        return $refProperty;
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
    public static function setPrivateProperty($obj, $property, $value)
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);
        $refProperty->setValue($obj, $value);
    }

    /**
     * Retrieve a private property.
     *
     * @param object|string $obj      object or class name
     * @param string        $property property name
     *
     * @return mixed value
     *
     * @throws ReflectionException
     */
    public static function getPrivateProperty($obj, $property)
    {
        $refProperty = self::getAccessibleRefProperty($obj, $property);

        return is_string($obj) ? $refProperty->getValue() : $refProperty->getValue($obj);
    }
}
