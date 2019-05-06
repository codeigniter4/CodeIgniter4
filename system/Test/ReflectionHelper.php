<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Test;

use ReflectionMethod;
use ReflectionObject;
use ReflectionClass;

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
	 * @return \Closure
	 * @throws \ReflectionException
	 */
	public static function getPrivateMethodInvoker($obj, $method)
	{
		$ref_method = new ReflectionMethod($obj, $method);
		$ref_method->setAccessible(true);
		$obj = (gettype($obj) === 'object') ? $obj : null;

		return function () use ($obj, $ref_method) {
			$args = func_get_args();
			return $ref_method->invokeArgs($obj, $args);
		};
	}

	/**
	 * Find an accessible property.
	 *
	 * @param object $obj
	 * @param string $property
	 *
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	private static function getAccessibleRefProperty($obj, $property)
	{
		if (is_object($obj))
		{
			$ref_class = new ReflectionObject($obj);
		}
		else
		{
			$ref_class = new ReflectionClass($obj);
		}

		$ref_property = $ref_class->getProperty($property);
		$ref_property->setAccessible(true);

		return $ref_property;
	}

	/**
	 * Set a private property.
	 *
	 * @param object|string $obj      object or class name
	 * @param string        $property property name
	 * @param mixed         $value    value
	 *
	 * @throws \ReflectionException
	 */
	public static function setPrivateProperty($obj, $property, $value)
	{
		$ref_property = self::getAccessibleRefProperty($obj, $property);
		$ref_property->setValue($obj, $value);
	}

	/**
	 * Retrieve a private property.
	 *
	 * @param object|string $obj      object or class name
	 * @param string        $property property name
	 *
	 * @return mixed value
	 * @throws \ReflectionException
	 */
	public static function getPrivateProperty($obj, $property)
	{
		$ref_property = self::getAccessibleRefProperty($obj, $property);
		return $ref_property->getValue($obj);
	}

}
