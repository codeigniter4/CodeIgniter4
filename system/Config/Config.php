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

namespace CodeIgniter\Config;

/**
 * Class Config
 *
 * @package CodeIgniter\Config
 */
class Config
{
	/**
	 * Cache for instance of any configurations that
	 * have been requested as "shared" instance.
	 *
	 * @var array
	 */
	static private $instances = [];

	//--------------------------------------------------------------------

	/**
	 * Create new configuration instances or return
	 * a shared instance
	 *
	 * @param string  $name      Configuration name
	 * @param boolean $getShared Use shared instance
	 *
	 * @return mixed|null
	 */
	public static function get(string $name, bool $getShared = true)
	{
		$class = $name;
		if (($pos = strrpos($name, '\\')) !== false)
		{
			$class = substr($name, $pos + 1);
		}

		if (! $getShared)
		{
			return self::createClass($name);
		}

		if (! isset( self::$instances[$class] ))
		{
			self::$instances[$class] = self::createClass($name);
		}
		return self::$instances[$class];
	}

	//--------------------------------------------------------------------

	/**
	 * Helper method for injecting mock instances while testing.
	 *
	 * @param string   $class
	 * @param $instance
	 */
	public static function injectMock(string $class, $instance)
	{
		self::$instances[$class] = $instance;
	}

	//--------------------------------------------------------------------

	/**
	 * Resets the instances array
	 */
	public static function reset()
	{
		static::$instances = [];
	}

	//--------------------------------------------------------------------

	/**
	 * Find configuration class and create instance
	 *
	 * @param string $name Classname
	 *
	 * @return mixed|null
	 */
	private static function createClass(string $name)
	{
		if (class_exists($name))
		{
			return new $name();
		}

		$locator = Services::locator();
		$file    = $locator->locateFile($name, 'Config');

		if (empty($file))
		{
			return null;
		}

		$name = $locator->getClassname($file);

		if (empty($name))
		{
			return null;
		}

		return new $name();
	}

	//--------------------------------------------------------------------
}
