<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Test;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;

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
		$refMethod = new ReflectionMethod($obj, $method);
		$refMethod->setAccessible(true);
		$obj = (gettype($obj) === 'object') ? $obj : null;

		return function () use ($obj, $refMethod) {
			$args = func_get_args();
			return $refMethod->invokeArgs($obj, $args);
		};
	}

	/**
	 * Find an accessible property.
	 *
	 * @param object|string $obj
	 * @param string        $property
	 *
	 * @return \ReflectionProperty
	 * @throws \ReflectionException
	 */
	private static function getAccessibleRefProperty($obj, $property)
	{
		if (is_object($obj))
		{
			$refClass = new ReflectionObject($obj);
		}
		else
		{
			$refClass = new ReflectionClass($obj);
		}

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
	 * @throws \ReflectionException
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
	 * @throws \ReflectionException
	 */
	public static function getPrivateProperty($obj, $property)
	{
		$refProperty = self::getAccessibleRefProperty($obj, $property);
		return $refProperty->getValue($obj);
	}

}
