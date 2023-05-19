<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity;

use CodeIgniter\Entity\Cast\ArrayCast;
use CodeIgniter\Entity\Cast\BooleanCast;
use CodeIgniter\Entity\Cast\CastInterface;
use CodeIgniter\Entity\Cast\CSVCast;
use CodeIgniter\Entity\Cast\DatetimeCast;
use CodeIgniter\Entity\Cast\FloatCast;
use CodeIgniter\Entity\Cast\IntBoolCast;
use CodeIgniter\Entity\Cast\IntegerCast;
use CodeIgniter\Entity\Cast\JsonCast;
use CodeIgniter\Entity\Cast\ObjectCast;
use CodeIgniter\Entity\Cast\StringCast;
use CodeIgniter\Entity\Cast\TimestampCast;
use CodeIgniter\Entity\Cast\URICast;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\I18n\Time;
use DateTime;
use Exception;
use JsonSerializable;
use ReturnTypeWillChange;

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
     *      'class_property_name' => 'db_column_name'
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
    private array $defaultCastHandlers = [
        'array'     => ArrayCast::class,
        'bool'      => BooleanCast::class,
        'boolean'   => BooleanCast::class,
        'csv'       => CSVCast::class,
        'datetime'  => DatetimeCast::class,
        'double'    => FloatCast::class,
        'float'     => FloatCast::class,
        'int'       => IntegerCast::class,
        'integer'   => IntegerCast::class,
        'int-bool'  => IntBoolCast::class,
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
     */
    private bool $_cast = true;

    /**
     * Allows filling in Entity parameters during construction.
     */
    public function __construct(?array $data = null)
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
    public function fill(?array $data = null)
    {
        if (! is_array($data)) {
            return $this;
        }

        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }

        return $this;
    }

    /**
     * General method that will return all public and protected values
     * of this entity as an array. All values are accessed through the
     * __get() magic method so will have any casts, etc applied to them.
     *
     * @param bool $onlyChanged If true, only return values that have changed since object creation
     * @param bool $cast        If true, properties will be cast.
     * @param bool $recursive   If true, inner entities will be cast as array as well.
     */
    public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
    {
        $this->_cast = $cast;

        $keys = array_filter(array_keys($this->attributes), static fn ($key) => strpos($key, '_') !== 0);

        if (is_array($this->datamap)) {
            $keys = array_unique(
                [...array_diff($keys, $this->datamap), ...array_keys($this->datamap)]
            );
        }

        $return = [];

        // Loop over the properties, to allow magic methods to do their thing.
        foreach ($keys as $key) {
            if ($onlyChanged && ! $this->hasChanged($key)) {
                continue;
            }

            $return[$key] = $this->__get($key);

            if ($recursive) {
                if ($return[$key] instanceof self) {
                    $return[$key] = $return[$key]->toArray($onlyChanged, $cast, $recursive);
                } elseif (is_callable([$return[$key], 'toArray'])) {
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
     * @param bool $onlyChanged If true, only return values that have changed since object creation
     * @param bool $recursive   If true, inner entities will be cast as array as well.
     */
    public function toRawArray(bool $onlyChanged = false, bool $recursive = false): array
    {
        $return = [];

        if (! $onlyChanged) {
            if ($recursive) {
                return array_map(static function ($value) use ($onlyChanged, $recursive) {
                    if ($value instanceof self) {
                        $value = $value->toRawArray($onlyChanged, $recursive);
                    } elseif (is_callable([$value, 'toRawArray'])) {
                        $value = $value->toRawArray();
                    }

                    return $value;
                }, $this->attributes);
            }

            return $this->attributes;
        }

        foreach ($this->attributes as $key => $value) {
            if (! $this->hasChanged($key)) {
                continue;
            }

            if ($recursive) {
                if ($value instanceof self) {
                    $value = $value->toRawArray($onlyChanged, $recursive);
                } elseif (is_callable([$value, 'toRawArray'])) {
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
     * @param string|null $key class property
     */
    public function hasChanged(?string $key = null): bool
    {
        // If no parameter was given then check all attributes
        if ($key === null) {
            return $this->original !== $this->attributes;
        }

        $dbColumn = $this->mapProperty($key);

        // Key doesn't exist in either
        if (! array_key_exists($dbColumn, $this->original) && ! array_key_exists($dbColumn, $this->attributes)) {
            return false;
        }

        // It's a new element
        if (! array_key_exists($dbColumn, $this->original) && array_key_exists($dbColumn, $this->attributes)) {
            return true;
        }

        return $this->original[$dbColumn] !== $this->attributes[$dbColumn];
    }

    /**
     * Set raw data array without any mutations
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
     * Checks the datamap to see if this property name is being mapped,
     * and returns the db column name, if any, or the original property name.
     *
     * @return string db column name
     */
    protected function mapProperty(string $key)
    {
        if (empty($this->datamap)) {
            return $key;
        }

        if (! empty($this->datamap[$key])) {
            return $this->datamap[$key];
        }

        return $key;
    }

    /**
     * Converts the given string|timestamp|DateTime|Time instance
     * into the "CodeIgniter\I18n\Time" object.
     *
     * @param DateTime|float|int|string|Time $value
     *
     * @return Time
     *
     * @throws Exception
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
     * @param bool|float|int|string|null $value     Attribute value
     * @param string                     $attribute Attribute name
     * @param string                     $method    Allowed to "get" and "set"
     *
     * @return array|bool|float|int|object|string|null
     *
     * @throws CastException
     */
    protected function castAs($value, string $attribute, string $method = 'get')
    {
        if (empty($this->casts[$attribute])) {
            return $value;
        }

        $type = $this->casts[$attribute];

        $isNullable = false;

        if (strpos($type, '?') === 0) {
            $isNullable = true;

            if ($value === null) {
                return null;
            }

            $type = substr($type, 1);
        }

        // In order not to create a separate handler for the
        // json-array type, we transform the required one.
        $type = $type === 'json-array' ? 'json[array]' : $type;

        if (! in_array($method, ['get', 'set'], true)) {
            throw CastException::forInvalidMethod($method);
        }

        $params = [];

        // Attempt to retrieve additional parameters if specified
        // type[param, param2,param3]
        if (preg_match('/^(.+)\[(.+)\]$/', $type, $matches)) {
            $type   = $matches[1];
            $params = array_map('trim', explode(',', $matches[2]));
        }

        if ($isNullable) {
            $params[] = 'nullable';
        }

        $type = trim($type, '[]');

        $handlers = array_merge($this->defaultCastHandlers, $this->castHandlers);

        if (empty($handlers[$type])) {
            return $value;
        }

        if (! is_subclass_of($handlers[$type], CastInterface::class)) {
            throw CastException::forInvalidInterface($handlers[$type]);
        }

        return $handlers[$type]::$method($value, $params);
    }

    /**
     * Support for json_encode()
     *
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Change the value of the private $_cast property
     *
     * @return bool|Entity
     */
    public function cast(?bool $cast = null)
    {
        if ($cast === null) {
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
     * @param array|bool|float|int|object|string|null $value
     *
     * @return $this
     *
     * @throws Exception
     */
    public function __set(string $key, $value = null)
    {
        $dbColumn = $this->mapProperty($key);

        // Check if the field should be mutated into a date
        if (in_array($dbColumn, $this->dates, true)) {
            $value = $this->mutateDate($value);
        }

        $value = $this->castAs($value, $dbColumn, 'set');

        // if a set* method exists for this key, use that method to
        // insert this value. should be outside $isNullable check,
        // so maybe wants to do sth with null value automatically
        $method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $dbColumn)));

        if (method_exists($this, $method)) {
            $this->{$method}($value);

            return $this;
        }

        // Otherwise, just the value. This allows for creation of new
        // class properties that are undefined, though they cannot be
        // saved. Useful for grabbing values through joins, assigning
        // relationships, etc.
        $this->attributes[$dbColumn] = $value;

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
     * @return array|bool|float|int|object|string|null
     *
     * @throws Exception
     *
     * @params string $key class property
     */
    public function __get(string $key)
    {
        $dbColumn = $this->mapProperty($key);

        $result = null;

        // Convert to CamelCase for the method
        $method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $dbColumn)));

        // if a get* method exists for this key,
        // use that method to insert this value.
        if (method_exists($this, $method)) {
            $result = $this->{$method}();
        }

        // Otherwise return the protected property
        // if it exists.
        elseif (array_key_exists($dbColumn, $this->attributes)) {
            $result = $this->attributes[$dbColumn];
        }

        // Do we need to mutate this into a date?
        if (in_array($dbColumn, $this->dates, true)) {
            $result = $this->mutateDate($result);
        }
        // Or cast it as something?
        elseif ($this->_cast) {
            $result = $this->castAs($result, $dbColumn);
        }

        return $result;
    }

    /**
     * Returns true if a property exists names $key, or a getter method
     * exists named like for __get().
     */
    public function __isset(string $key): bool
    {
        if ($this->isMappedDbColumn($key)) {
            return false;
        }

        $dbColumn = $this->mapProperty($key);

        $method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $dbColumn)));

        if (method_exists($this, $method)) {
            return true;
        }

        return isset($this->attributes[$dbColumn]);
    }

    /**
     * Unsets an attribute property.
     */
    public function __unset(string $key): void
    {
        if ($this->isMappedDbColumn($key)) {
            return;
        }

        $dbColumn = $this->mapProperty($key);

        unset($this->attributes[$dbColumn]);
    }

    /**
     * Whether this key is mapped db column name?
     */
    protected function isMappedDbColumn(string $key): bool
    {
        $dbColumn = $this->mapProperty($key);

        // The $key is a property name which has mapped db column name
        if ($key !== $dbColumn) {
            return false;
        }

        return $this->hasMappedProperty($key);
    }

    /**
     * Whether this key has mapped property?
     */
    protected function hasMappedProperty(string $key): bool
    {
        $property = array_search($key, $this->datamap, true);

        return $property !== false;
    }
}
