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

use CodeIgniter\Exceptions\CastException;
use CodeIgniter\I18n\Time;

/**
 * Entity encapsulation, for use with CodeIgniter\Model
 */
class Entity implements \JsonSerializable
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
	 */
	protected $datamap = [];

	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

	/**
	 * Array of field names and the type of value to cast them as
	 * when they are accessed.
	 */
	protected $casts = [];

	/**
	 * Holds the current values of all class vars.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Holds original copies of all class vars so
	 * we can determine what's actually been changed
	 * and not accidentally write nulls where we shouldn't.
	 *
	 * @var array
	 */
	protected $original = [];

	/**
	 * Holds info whenever properties have to be casted
	 *
	 * @var boolean
	 **/
	private $_cast = true;

	/**
	 * Allows filling in Entity parameters during construction.
	 *
	 * @param array|null $data
	 */
	public function __construct(array $data = null)
	{
		$this->syncOriginal();

		$this->fill($data);
	}

	/**
	 * Takes an array of key/value pairs and sets them as
	 * class properties, using any `setCamelCasedProperty()` methods
	 * that may or may not exist.
	 *
	 * @param array $data
	 *
	 * @return \CodeIgniter\Entity
	 */
	public function fill(array $data = null)
	{
		if (! is_array($data))
		{
			return $this;
		}

		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * General method that will return all public and protected
	 * values of this entity as an array. All values are accessed
	 * through the __get() magic method so will have any casts, etc
	 * applied to them.
	 *
	 * @param boolean $onlyChanged If true, only return values that have changed since object creation
	 * @param boolean $cast        If true, properties will be casted.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function toArray(bool $onlyChanged = false, bool $cast = true): array
	{
		$this->_cast = $cast;
		$return      = [];

		// we need to loop over our properties so that we
		// allow our magic methods a chance to do their thing.
		foreach ($this->attributes as $key => $value)
		{
			if (strpos($key, '_') === 0)
			{
				continue;
			}

			if ($onlyChanged && ! $this->hasChanged($key))
			{
				continue;
			}

			$return[$key] = $this->__get($key);
		}

		// Loop over our mapped properties and add them to the list...
		if (is_array($this->datamap))
		{
			foreach ($this->datamap as $from => $to)
			{
				if (array_key_exists($to, $return))
				{
					$return[$from] = $this->__get($to);
				}
			}
		}

		$this->_cast = true;
		return $return;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the raw values of the current attributes.
	 *
	 * @param boolean $onlyChanged
	 *
	 * @return array
	 */
	public function toRawArray(bool $onlyChanged = false): array
	{
		$return = [];

		if (! $onlyChanged)
		{
			return $this->attributes;
		}

		foreach ($this->attributes as $key => $value)
		{
			if (! $this->hasChanged($key))
			{
				continue;
			}

			$return[$key] = $this->attributes[$key];
		}

		return $return;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures our "original" values match the current values.
	 *
	 * @return $this
	 */
	public function syncOriginal()
	{
		$this->original = $this->attributes;

		return $this;
	}

	/**
	 * Checks a property to see if it has changed since the entity was created.
	 * Or, without a parameter, checks if any properties have changed.
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function hasChanged(string $key = null): bool
	{
		// If no parameter was given then check all attributes
		if ($key === null)
		{
			return     $this->original !== $this->attributes;
		}

		// Key doesn't exist in either
		if (! array_key_exists($key, $this->original) && ! array_key_exists($key, $this->attributes))
		{
			return false;
		}

		// It's a new element
		if (! array_key_exists($key, $this->original) && array_key_exists($key, $this->attributes))
		{
			return true;
		}

		return $this->original[$key] !== $this->attributes[$key];
	}

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
	 * @throws \Exception
	 */
	public function __get(string $key)
	{
		$key    = $this->mapProperty($key);
		$result = null;

		// Convert to CamelCase for the method
		$method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

		// if a set* method exists for this key,
		// use that method to insert this value.
		if (method_exists($this, $method))
		{
			$result = $this->$method();
		}

		// Otherwise return the protected property
		// if it exists.
		else if (array_key_exists($key, $this->attributes))
		{
			$result = $this->attributes[$key];
		}

		// Do we need to mutate this into a date?
		if (in_array($key, $this->dates))
		{
			$result = $this->mutateDate($result);
		}
		// Or cast it as something?
		else if ($this->_cast && ! empty($this->casts[$key]))
		{
			$result = $this->castAs($result, $this->casts[$key]);
		}

		return $result;
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
	 * @throws \Exception
	 */
	public function __set(string $key, $value = null)
	{
		$key = $this->mapProperty($key);

		// Check if the field should be mutated into a date
		if (in_array($key, $this->dates))
		{
			$value = $this->mutateDate($value);
		}

		$isNullable = false;
		$castTo     = false;

		if (array_key_exists($key, $this->casts))
		{
			$isNullable = strpos($this->casts[$key], '?') === 0;
			$castTo     = $isNullable ? substr($this->casts[$key], 1) : $this->casts[$key];
		}

		if (! $isNullable || ! is_null($value))
		{
			// Array casting requires that we serialize the value
			// when setting it so that it can easily be stored
			// back to the database.
			if ($castTo === 'array')
			{
				$value = serialize($value);
			}

			// JSON casting requires that we JSONize the value
			// when setting it so that it can easily be stored
			// back to the database.
			if (($castTo === 'json' || $castTo === 'json-array') && function_exists('json_encode'))
			{
				$value = json_encode($value, JSON_UNESCAPED_UNICODE);

				if (json_last_error() !== JSON_ERROR_NONE)
				{
					throw CastException::forInvalidJsonFormatException(json_last_error());
				}
			}
		}

		// if a set* method exists for this key,
		// use that method to insert this value.
		// *) should be outside $isNullable check - SO maybe wants to do sth with null value automatically
		$method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));
		if (method_exists($this, $method))
		{
			$this->$method($value);

			return $this;
		}

		// Otherwise, just the value.
		// This allows for creation of new class
		// properties that are undefined, though
		// they cannot be saved. Useful for
		// grabbing values through joins,
		// assigning relationships, etc.
		$this->attributes[$key] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Unsets an attribute property.
	 *
	 * @param string $key
	 *
	 * @throws \ReflectionException
	 */
	public function __unset(string $key)
	{
		unset($this->attributes[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if a property exists names $key, or a getter method
	 * exists named like for __get().
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function __isset(string $key): bool
	{
		$key = $this->mapProperty($key);

		$method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

		if (method_exists($this, $method))
		{
			return true;
		}

		return isset($this->attributes[$key]);
	}

	/**
	 * Set raw data array without any mutations
	 *
	 * @param  array $data
	 * @return $this
	 */
	public function setAttributes(array $data)
	{
		$this->attributes = $data;
		$this->syncOriginal();
		return $this;
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
		if (empty($this->datamap))
		{
			return $key;
		}

		if (! empty($this->datamap[$key]))
		{
			return $this->datamap[$key];
		}

		return $key;
	}

	//--------------------------------------------------------------------

	/**
	 * Converts the given string|timestamp|DateTime|Time instance
	 * into a \CodeIgniter\I18n\Time object.
	 *
	 * @param $value
	 *
	 * @return \CodeIgniter\I18n\Time
	 * @throws \Exception
	 */
	protected function mutateDate($value)
	{
		if ($value instanceof Time)
		{
			return $value;
		}

		if ($value instanceof \DateTime)
		{
			return Time::instance($value);
		}

		if (is_numeric($value))
		{
			return Time::createFromTimestamp($value);
		}

		if (is_string($value))
		{
			return Time::parse($value);
		}

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides the ability to cast an item as a specific data type.
	 * Add ? at the beginning of $type  (i.e. ?string) to get NULL instead of casting $value if $value === null
	 *
	 * @param $value
	 * @param string $type
	 *
	 * @return mixed
	 * @throws \Exception
	 */

	protected function castAs($value, string $type)
	{
		if (strpos($type, '?') === 0)
		{
			if ($value === null)
			{
				return null;
			}
			$type = substr($type, 1);
		}

		switch($type)
		{
			case 'int':
			case 'integer': //alias for 'integer'
				$value = (int)$value;
				break;
			case 'float':
				$value = (float)$value;
				break;
			case 'double':
				$value = (double)$value;
				break;
			case 'string':
				$value = (string)$value;
				break;
			case 'bool':
			case 'boolean': //alias for 'boolean'
				$value = (bool)$value;
				break;
			case 'object':
				$value = (object)$value;
				break;
			case 'array':
				if (is_string($value) && (strpos($value, 'a:') === 0 || strpos($value, 's:') === 0))
				{
					$value = unserialize($value);
				}

				$value = (array)$value;
				break;
			case 'json':
				$value = $this->castAsJson($value);
				break;
			case 'json-array':
				$value = $this->castAsJson($value, true);
				break;
			case 'datetime':
				return $this->mutateDate($value);
			case 'timestamp':
				return strtotime($value);
		}

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Cast as JSON
	 *
	 * @param mixed   $value
	 * @param boolean $asArray
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Exceptions\CastException
	 */
	private function castAsJson($value, bool $asArray = false)
	{
		$tmp = ! is_null($value) ? ($asArray ? [] : new \stdClass) : null;
		if (function_exists('json_decode'))
		{
			if ((is_string($value) && strlen($value) > 1 && in_array($value[0], ['[', '{', '"'])) || is_numeric($value))
			{
				$tmp = json_decode($value, $asArray);

				if (json_last_error() !== JSON_ERROR_NONE)
				{
					throw CastException::forInvalidJsonFormatException(json_last_error());
				}
			}
		}
		return $tmp;
	}

	/**
	 * Support for json_encode()
	 *
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}
