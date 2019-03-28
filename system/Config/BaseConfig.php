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
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Config;

/**
 * Class BaseConfig
 *
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values
 * from the environment.
 *
 * These can be set within the .env file.
 */
class BaseConfig
{

	/**
	 * An optional array of classes that will act as Registrars
	 * for rapidly setting config class properties.
	 *
	 * @var array
	 */
	public static $registrars      = [];
	protected static $didDiscovery = false;
	protected static $moduleConfig;

	/**
	 * Will attempt to get environment variables with names
	 * that match the properties of the child class.
	 *
	 * The "shortPrefix" is the lowercase-only config class name.
	 */
	public function __construct()
	{
		static::$moduleConfig = config('Modules');

		$properties  = array_keys(get_object_vars($this));
		$prefix      = get_class($this);
		$slashAt     = strrpos($prefix, '\\');
		$shortPrefix = strtolower(substr($prefix, $slashAt === false ? 0 : $slashAt + 1));

		foreach ($properties as $property)
		{
			if (is_array($this->$property))
			{
				foreach ($this->$property as $key => $val)
				{
					if ($value = $this->getEnvValue("{$property}.{$key}", $prefix, $shortPrefix))
					{
						if (! is_null($value))
						{
							if ($value === 'false')
							{
								$value = false;
							}
							elseif ($value === 'true')
							{
								$value = true;
							}

							$this->$property[$key] = $value;
						}
					}
				}
			}
			else
			{
				if (($value = $this->getEnvValue($property, $prefix, $shortPrefix)) !== false)
				{
					if (! is_null($value))
					{
						if ($value === 'false')
						{
							$value = false;
						}
						elseif ($value === 'true')
						{
							$value = true;
						}

						$this->$property = is_bool($value) ? $value : trim($value, '\'"');
					}
				}
			}
		}

		if (defined('ENVIRONMENT') && ENVIRONMENT !== 'testing')
		{
			// well, this won't happen during unit testing
			// @codeCoverageIgnoreStart
			$this->registerProperties();
			// @codeCoverageIgnoreEnd
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve an environment-specific configuration setting
	 *
	 * @param string $property
	 * @param string $prefix
	 * @param string $shortPrefix
	 *
	 * @return mixed
	 */
	protected function getEnvValue(string $property, string $prefix, string $shortPrefix)
	{
		$shortPrefix = ltrim($shortPrefix, '\\');
		switch (true)
		{
			case array_key_exists("{$shortPrefix}.{$property}", $_ENV):
				return $_ENV["{$shortPrefix}.{$property}"];
				break;
			case array_key_exists("{$shortPrefix}.{$property}", $_SERVER):
				return $_SERVER["{$shortPrefix}.{$property}"];
				break;
			case array_key_exists("{$prefix}.{$property}", $_ENV):
				return $_ENV["{$prefix}.{$property}"];
				break;
			case array_key_exists("{$prefix}.{$property}", $_SERVER):
				return $_SERVER["{$prefix}.{$property}"];
				break;
			default:
				$value = getenv($property);
				return $value === false ? null : $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Provides external libraries a simple way to register one or more
	 * options into a config file.
	 *
	 * @throws \ReflectionException
	 */
	protected function registerProperties()
	{
		if (! static::$moduleConfig->shouldDiscover('registrars'))
		{
			return;
		}

		if (! static::$didDiscovery)
		{
			$locator              = \Config\Services::locator();
			static::$registrars   = $locator->search('Config/Registrar.php');
			static::$didDiscovery = true;
		}

		$shortName = (new \ReflectionClass($this))->getShortName();

		// Check the registrar class for a method named after this class' shortName
		foreach (static::$registrars as $callable)
		{
			// ignore non-applicable registrars
			if (! method_exists($callable, $shortName))
			{
				continue;
			}

			$properties = $callable::$shortName();

			if (! is_array($properties))
			{
				throw new \RuntimeException('Registrars must return an array of properties and their values.');
			}

			foreach ($properties as $property => $value)
			{
				if (isset($this->$property) && is_array($this->$property) && is_array($value))
				{
					$this->$property = array_merge($this->$property, $value);
				}
				else
				{
					$this->$property = $value;
				}
			}
		}
	}

	//--------------------------------------------------------------------
}
