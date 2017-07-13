<?php namespace CodeIgniter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
class Entity
{

	/**
	 * Maps names used in sets and gets against unique
	 * names within the class, allowing independence from
	 * database column names.
	 *
	 * Example:
	 *  $datamap = [
	 *      'db_name' => 'class_name'
	 *  ];
	 *
	 * @var array
	 */
	protected $datamap = [];

	/**
	 * Allows filling in Entity parameters during construction.
	 *
	 * @param array|null $data
	 */
	public function __construct(array $data = null)
	{
		if (is_array($data))
		{
			$this->fill($data);
		}
	}

	/**
	 * Takes an array of key/value pairs and sets them as
	 * class properties, using any `setCamelCasedProperty()` methods
	 * that may or may not exist.
	 *
	 * @param array $data
	 */
	public function fill(array $data)
	{
		foreach ($data as $key => $value)
		{
			$method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

			if (method_exists($this, $method))
			{
				$this->$method($value);
			}
			elseif (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Magic method to allow retrieval of protected and private
	 * class properties either by their name, or through a `getCamelCasedProperty()`
	 * method.
	 *
	 * Examples:
	 *
	 *      $p = $this->my_property
	 *      $p = $this->getMyProperty()
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		$key = $this->mapProperty($key);

		// Convert to CamelCase for the method
		$method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

		// if a set* method exists for this key, 
		// use that method to insert this value. 
		if (method_exists($this, $method))
		{
			return $this->$method();
		}

		// Otherwise return the protected property
		// if it exists.
		if (property_exists($this, $key))
		{
			return $this->$key;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Magic method to all protected/private class properties to be easily set,
	 * either through a direct access or a `setCamelCasedProperty()` method.
	 *
	 * Examples:
	 *
	 *      $this->my_property = $p;
	 *      $this->setMyProperty() = $p;
	 *
	 * @param string $key
	 * @param null   $value
	 *
	 * @return $this
	 */
	public function __set(string $key, $value = null)
	{
		$key = $this->mapProperty($key);

		// if a set* method exists for this key, 
		// use that method to insert this value. 
		$method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));
		if (method_exists($this, $method))
		{
			$this->$method($value);

			return $this;
		}
		elseif (property_exists($this, $key))
		{
			$this->$key = $value;

			return $this;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Unsets a protected/private class property. Sets the value to null.
	 * However, if there was a default value for the parent class, this
	 * attribute will be reset to that default value.
	 *
	 * @param string $key
	 */
	public function __unset(string $key)
	{
		// If not actual property exists, get out
		// before we confuse our data mapping.
		if ( ! property_exists($this, $key))
			return;

		$this->$key = null;

		// Get the class' original default value for this property
		// so we can reset it to the original value.
		$reflectionClass = new \ReflectionClass($this);
		$defaultProperties = $reflectionClass->getDefaultProperties();

		if (isset($defaultProperties[$key]))
		{
			$this->$key = $defaultProperties[$key];
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if a property exists names $key, or a getter method
	 * exists named like for __get().
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset(string $key): bool
	{
		// Ensure an actual property exists, otherwise
		// we confuse the data mapping.
		$value = property_exists($this, $key) ? $this->$key : null;

		return ! is_null($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the datamap to see if this column name is being mapped,
	 * and returns the mapped name, if any, or the original name.
	 *
	 * @param string $key
	 *
	 * @return mixed|string
	 */
	protected function mapProperty(string $key)
	{
		if (array_key_exists($key, $this->datamap))
		{
			return $this->datamap[$key];
		}

		return $key;
	}

	//--------------------------------------------------------------------
}
