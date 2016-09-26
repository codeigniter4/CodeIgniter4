<?php namespace CodeIgniter\Config;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT	MIT License
 * @link         http://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */

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
	 * Will attempt to get environment variables with names
	 * that match the properties of the child class.
	 */
	public function __construct()
	{
		$properties  = array_keys(get_object_vars($this));
		$prefix      = get_class($this);
		$shortPrefix = strtolower(substr($prefix, strrpos($prefix, '\\') + 1));

		foreach ($properties as $property)
		{
			if (is_array($this->$property))
			{
				foreach ($this->$property as $key => $val)
				{
					if ($value = $this->getEnvValue("{$property}.{$key}", $prefix, $shortPrefix))
					{
						if (is_null($value)) continue;

						if ($value === 'false')    $value = false;
						elseif ($value === 'true') $value = true;

						$this->$property[$key] = $value;
					}
				}
			}
			else
			{
				if (($value = $this->getEnvValue($property, $prefix, $shortPrefix)) !== false )
				{
					if (is_null($value)) continue;

					if ($value === 'false')    $value = false;
					elseif ($value === 'true') $value = true;

					$this->$property = $value;
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve an environment-specific configuration setting
	 * @param string $property
	 * @param string $prefix
	 * @param string $shortPrefix
	 * @return type
	 */
	protected function getEnvValue(string $property, string $prefix, string $shortPrefix)
	{
		if (($value = getenv("{$shortPrefix}.{$property}")) !== false)
		{
			return $value;
		}
		elseif (($value = getenv("{$prefix}.{$property}")) !== false)
		{
			return $value;
		}
		elseif (($value = getenv($property)) !== false && $property != 'path')
		{
			return $value;
		}

		return null;
	}

	//--------------------------------------------------------------------

}
