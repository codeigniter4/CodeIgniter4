<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Entity;

use CodeIgniter\Entity\Cast\ArrayCast;
use CodeIgniter\Entity\Cast\BooleanCast;
use CodeIgniter\Entity\Cast\CastInterface;
use CodeIgniter\Entity\Cast\CSVCast;
use CodeIgniter\Entity\Cast\DatetimeCast;
use CodeIgniter\Entity\Cast\FloatCast;
use CodeIgniter\Entity\Cast\IntegerCast;
use CodeIgniter\Entity\Cast\JsonCast;
use CodeIgniter\Entity\Cast\ObjectCast;
use CodeIgniter\Entity\Cast\StringCast;
use CodeIgniter\Entity\Cast\TimestampCast;
use CodeIgniter\Entity\Cast\URICast;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\I18n\Time;
use Exception;
use JsonSerializable;

/**
 * Entity encapsulation, for use with CodeIgniter\Model
 */
class Entity implements JsonSerializable
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
	 * Array of field names and the type of value to cast them as when
	 * they are accessed.
	 */
	protected $casts = [];

	/**
	 * Custom convert handlers
	 *
	 * @var array<string, string>
	 */
	protected $castHandlers = [];

	/**
	 * Default convert handlers
	 *
	 * @var array<string, string>
	 */
	private $defaultCastHandlers = [
		'array'     => ArrayCast::class,
		'bool'      => BooleanCast::class,
		'boolean'   => BooleanCast::class,
		'csv'       => CSVCast::class,
		'datetime'  => DatetimeCast::class,
		'double'    => FloatCast::class,
		'float'     => FloatCast::class,
		'int'       => IntegerCast::class,
		'integer'   => IntegerCast::class,
		'json'      => JsonCast::class,
		'object'    => ObjectCast::class,
		'string'    => StringCast::class,
		'timestamp' => TimestampCast::class,
		'uri'       => URICast::class,
	];

	/**
	 * Holds the current values of all class vars.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Holds original copies of all class vars so we can determine
	 * what's actually been changed and not accidentally write
	 * nulls where we shouldn't.
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
	 * Takes an array of key/value pairs and sets them as class
	 * properties, using any `setCamelCasedProperty()` methods
	 * that may or may not exist.
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function fill(array $data = null)
	{
		if (! is_array($data))
		{
			return $this;
		}

		foreach ($data as $key => $value)
		{
			$this->__set($key, $value);
		}

		return $this;
	}

	/**
	 * General method that will return all public and protected values
	 * of this entity as an array. All values are accessed through the
	 * __get() magic method so will have any casts, etc applied to them.
	 *
	 * @param boolean $onlyChanged If true, only return values that have changed since object creation
	 * @param boolean $cast        If true, properties will be casted.
	 * @param boolean $recursive   If true, inner entities will be casted as array as well.
	 *
	 * @return array
	 */
	public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
	{
		$this->_cast = $cast;

		$keys = array_filter(array_keys($this->attributes), function ($key) {
			return strpos($key, '_') !== 0;
		});

		if (is_array($this->datamap))
		{
			$keys = array_unique(
				array_merge(array_diff($keys, $this->datamap), array_keys($this->datamap))
			);
		}

		$return = [];

		// Loop over the properties, to allow magic methods to do their thing.
		foreach ($keys as $key)
		{
			if ($onlyChanged && ! $this->hasChanged($key))
			{
				continue;
			}

			$return[$key] = $this->__get($key);

			if ($recursive)
			{
				if ($return[$key] instanceof Entity)
				{
					$return[$key] = $return[$key]->toArray($onlyChanged, $cast, $recursive);
				}
				elseif (is_callable([$return[$key], 'toArray']))
				{
					$return[$key] = $return[$key]->toArray();
				}
			}
		}

		$this->_cast = true;

		return $return;
	}

	/**
	 * Returns the raw values of the current attributes.
	 *
	 * @param boolean $onlyChanged If true, only return values that have changed since object creation
	 * @param boolean $recursive   If true, inner entities will be casted as array as well.
	 *
	 * @return array
	 */
	public function toRawArray(bool $onlyChanged = false, bool $recursive = false): array
	{
		$return = [];

		if (! $onlyChanged)
		{
			if ($recursive)
			{
				return array_map(function ($value) use ($onlyChanged, $recursive) {
					if ($value instanceof Entity)
					{
						$value = $value->toRawArray($onlyChanged, $recursive);
					}
					elseif (is_callable([$value, 'toRawArray']))
					{
						$value = $value->toRawArray();
					}

					return $value;
				}, $this->attributes);
			}

			return $this->attributes;
		}

		foreach ($this->attributes as $key => $value)
		{
			if (! $this->hasChanged($key))
			{
				continue;
			}

			if ($recursive)
			{
				if ($value instanceof Entity)
				{
					$value = $value->toRawArray($onlyChanged, $recursive);
				}
				elseif (is_callable([$value, 'toRawArray']))
				{
					$value = $value->toRawArray();
				}
			}

			$return[$key] = $value;
		}

		return $return;
	}

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
	 * Checks a property to see if it has changed since the entity
	 * was created. Or, without a parameter, checks if any
	 * properties have changed.
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function hasChanged(string $key = null): bool
	{
		// If no parameter was given then check all attributes
		if (is_null($key))
		{
			return $this->original !== $this->attributes;
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
	 * Set raw data array without any mutations
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setAttributes(array $data)
	{
		$this->attributes = $data;

		$this->syncOriginal();

		return $this;
	}

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

	/**
	 * Converts the given string|timestamp|DateTime|Time instance
	 * into the "CodeIgniter\I18n\Time" object.
	 *
	 * @param mixed $value
	 *
	 * @throws Exception
	 *
	 * @return Time|mixed
	 */
	protected function mutateDate($value)
	{
		return DatetimeCast::get($value);
	}

	/**
	 * Provides the ability to cast an item as a specific data type.
	 * Add ? at the beginning of $type  (i.e. ?string) to get NULL
	 * instead of casting $value if $value === null
	 *
	 * @param mixed  $value     Attribute value
	 * @param string $attribute Attribute name
	 * @param string $method    Allowed to "get" and "set"
	 *
	 * @throws CastException
	 *
	 * @return mixed
	 */
	protected function castAs($value, string $attribute, string $method = 'get')
	{
		if (empty($this->casts[$attribute]))
		{
			return $value;
		}

		$type = $this->casts[$attribute];

		$isNullable = false;

		if (strpos($type, '?') === 0)
		{
			$isNullable = true;

			if (is_null($value))
			{
				return null;
			}

			$type = substr($type, 1);
		}

		//In order not to create a separate handler for the
		// json-array type, we transform the required one.
		$type = $type === 'json-array' ? 'json[array]' : $type;

		if (! in_array($method, ['get', 'set'], true))
		{
			throw CastException::forInvalidMethod($method);
		}

		$params = [];

		//Attempt to retrieve additional parameters if specified
		// type[param, param2,param3]
		if (preg_match('/^(.+)\[(.+)\]$/', $type, $matches))
		{
			$type   = $matches[1];
			$params = array_map('trim', explode(',', $matches[2]));
		}

		if ($isNullable)
		{
			$params[] = 'nullable';
		}

		$type = trim($type, '[]');

		$handlers = array_merge($this->defaultCastHandlers, $this->castHandlers);

		if (empty($handlers[$type]))
		{
			return $value;
		}

		if (! is_subclass_of($handlers[$type], CastInterface::class))
		{
			throw CastException::forInvalidInterface($handlers[$type]);
		}

		return $handlers[$type]::$method($value, $params);
	}

	/**
	 * Cast as JSON
	 *
	 * @param mixed   $value
	 * @param boolean $asArray
	 *
	 * @throws CastException
	 *
	 * @return mixed
	 */
	private function castAsJson($value, bool $asArray = false)
	{
		return JsonCast::get($value, $asArray ? ['array'] : []);
	}

	/**
	 * Support for json_encode()
	 *
	 * @return array|mixed
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Change the value of the private $_cast property
	 *
	 * @param boolean|null $cast
	 *
	 * @return boolean|Entity
	 */
	public function cast(bool $cast = null)
	{
		if (is_null($cast))
		{
			return $this->_cast;
		}

		$this->_cast = $cast;

		return $this;
	}

	/**
	 * Magic method to all protected/private class properties to be
	 * easily set, either through a direct access or a
	 * `setCamelCasedProperty()` method.
	 *
	 * Examples:
	 *  $this->my_property = $p;
	 *  $this->setMyProperty() = $p;
	 *
	 * @param string     $key
	 * @param mixed|null $value
	 *
	 * @throws Exception
	 *
	 * @return $this
	 */
	public function __set(string $key, $value = null)
	{
		$key = $this->mapProperty($key);

		// Check if the field should be mutated into a date
		if (in_array($key, $this->dates, true))
		{
			$value = $this->mutateDate($value);
		}

		$value = $this->castAs($value, $key, 'set');

		// if a set* method exists for this key, use that method to
		// insert this value. should be outside $isNullable check,
		// so maybe wants to do sth with null value automatically
		$method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

		if (method_exists($this, $method))
		{
			$this->$method($value);

			return $this;
		}

		// Otherwise, just the value. This allows for creation of new
		// class properties that are undefined, though they cannot be
		// saved. Useful for grabbing values through joins, assigning
		// relationships, etc.
		$this->attributes[$key] = $value;

		return $this;
	}

	/**
	 * Magic method to allow retrieval of protected and private class properties
	 * either by their name, or through a `getCamelCasedProperty()` method.
	 *
	 * Examples:
	 *  $p = $this->my_property
	 *  $p = $this->getMyProperty()
	 *
	 * @param string $key
	 *
	 * @throws Exception
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		$key = $this->mapProperty($key);

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
		elseif (array_key_exists($key, $this->attributes))
		{
			$result = $this->attributes[$key];
		}

		// Do we need to mutate this into a date?
		if (in_array($key, $this->dates, true))
		{
			$result = $this->mutateDate($result);
		}
		// Or cast it as something?
		elseif ($this->_cast)
		{
			$result = $this->castAs($result, $key);
		}

		return $result;
	}

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
	 * Unsets an attribute property.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function __unset(string $key): void
	{
		unset($this->attributes[$key]);
	}
}
