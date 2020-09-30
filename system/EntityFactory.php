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

namespace CodeIgniter;

use Config\Services;

/**
 * EntityFactory creates instances of Entity classes.
 */
class EntityFactory
{
	/**
	 * Cache of instances of entities that have been
	 * requested as "shared" instances.
	 *
	 * @var array<string, Entity>
	 */
	private static $instances = [];

	/**
	 * Create a new entity instance or retrieve the shared instance.
	 *
	 * @param string  $name
	 * @param array   $data
	 * @param boolean $getShared
	 *
	 * @return Entity|null
	 */
	public static function get(string $name, array $data = [], bool $getShared = true): ?Entity
	{
		$class = $name;

		if (($pos = strrpos($name, '\\')) !== false)
		{
			$class = substr($name, $pos + 1);
		}

		if (! $getShared)
		{
			return self::createClass($name, $data);
		}

		if (! isset(self::$instances[$class]))
		{
			self::$instances[$class] = self::createClass($name, $data);
		}

		return self::$instances[$class];
	}

	/**
	 * Injects mock Entity instances for testing.
	 *
	 * @param string $class
	 * @param Entity $instance
	 *
	 * @return void
	 */
	public static function injectMock(string $class, Entity $instance): void
	{
		self::$instances[$class] = $instance;
	}

	/**
	 * Resets the instances array.
	 *
	 * @return void
	 */
	public static function reset(): void
	{
		self::$instances = [];
	}

	/**
	 * Finds the Entity class and create an instance.
	 *
	 * @param string $name
	 * @param array  $data
	 *
	 * @return Entity|null
	 */
	private static function createClass(string $name, array $data): ?Entity
	{
		if (class_exists($name))
		{
			return new $name($data);
		}

		$locator = Services::locator();

		$file = $locator->locateFile($name, 'Entities');

		if (! $file)
		{
			// Class was namespaced and cannot be found
			if (strpos($name, '\\') !== false)
			{
				return null;
			}

			$files = $locator->search('Entities/' . $name);

			if (empty($files))
			{
				return null;
			}

			$file = reset($files);
		}

		$name = $locator->getClassname($file);

		if (empty($name))
		{
			return null; // @codeCoverageIgnore
		}

		return new $name($data);
	}
}
