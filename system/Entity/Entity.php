<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity;

use BackedEnum;
use CodeIgniter\DataCaster\DataCaster;
use CodeIgniter\Entity\Cast\ArrayCast;
use CodeIgniter\Entity\Cast\BooleanCast;
use CodeIgniter\Entity\Cast\CSVCast;
use CodeIgniter\Entity\Cast\DatetimeCast;
use CodeIgniter\Entity\Cast\EnumCast;
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
use DateTimeInterface;
use Exception;
use JsonSerializable;
use Traversable;
use UnitEnum;

/**
 * Entity encapsulation, for use with CodeIgniter\Model
 *
 * @see \CodeIgniter\Entity\EntityTest
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
     *
     * @var array<string, string>
     */
    protected $datamap = [];

    /**
     * The date fields.
     *
     * @var list<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Array of field names and the type of value to cast them as when
     * they are accessed.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Custom convert handlers.
     *
     * @var array<string, string>
     */
    protected $castHandlers = [];

    /**
     * Default convert handlers.
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
        'enum'      => EnumCast::class,
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
     * @var array<string, mixed>
     */
    protected $attributes = [];

    /**
     * Holds original copies of all class vars so we can determine
     * what's actually been changed and not accidentally write
     * nulls where we shouldn't.
     *
     * @var array<string, mixed>
     */
    protected $original = [];

    /**
     * The data caster.
     */
    protected ?DataCaster $dataCaster = null;

    /**
     * Holds info whenever properties have to be casted.
     */
    private bool $_cast = true;

    /**
     * Indicates whether all attributes are scalars (for optimization).
     */
    private bool $_onlyScalars = true;

    /**
     * Allows filling in Entity parameters during construction.
     *
     * @param array<string, mixed> $data
     */
    public function __construct(?array $data = null)
    {
        $this->dataCaster = $this->dataCaster();

        $this->syncOriginal();

        $this->fill($data);
    }

    /**
     * Takes an array of key/value pairs and sets them as class
     * properties, using any `setCamelCasedProperty()` methods
     * that may or may not exist.
     *
     * @param array<string, array<int|string, mixed>|bool|float|int|object|string|null> $data
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
     * @param bool $onlyChanged If true, only return values that have changed since object creation.
     * @param bool $cast        If true, properties will be cast.
     * @param bool $recursive   If true, inner entities will be cast as array as well.
     *
     * @return array<string, mixed>
     */
    public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
    {
        $originalCast = $this->_cast;
        $this->_cast  = $cast;

        $keys = array_filter(array_keys($this->attributes), static fn ($key): bool => ! str_starts_with($key, '_'));

        if (is_array($this->datamap)) {
            $keys = array_unique(
                [...array_diff($keys, $this->datamap), ...array_keys($this->datamap)],
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

        $this->_cast = $originalCast;

        return $return;
    }

    /**
     * Returns the raw values of the current attributes.
     *
     * @param bool $onlyChanged If true, only return values that have changed since object creation.
     * @param bool $recursive   If true, inner entities will be cast as array as well.
     *
     * @return array<string, mixed>
     */
    public function toRawArray(bool $onlyChanged = false, bool $recursive = false): array
    {
        $convert = static function ($value) use (&$convert, $recursive) {
            if (! $recursive) {
                return $value;
            }

            if ($value instanceof self) {
                // Always output full array for nested entities
                return $value->toRawArray(false, true);
            }

            if (is_array($value)) {
                $result = [];

                foreach ($value as $k => $v) {
                    $result[$k] = $convert($v);
                }

                return $result;
            }

            if (is_object($value) && is_callable([$value, 'toRawArray'])) {
                return $value->toRawArray();
            }

            return $value;
        };

        // When returning everything
        if (! $onlyChanged) {
            return $recursive
                ? array_map($convert, $this->attributes)
                : $this->attributes;
        }

        // When filtering by changed values only
        $return = [];

        foreach ($this->attributes as $key => $value) {
            // Special handling for arrays of entities in recursive mode
            // Skip hasChanged() and do per-entity comparison directly
            if ($recursive && is_array($value) && $this->containsOnlyEntities($value)) {
                $originalValue = $this->original[$key] ?? null;

                if (! is_string($originalValue)) {
                    // No original or invalid format, export all entities
                    $converted = [];

                    foreach ($value as $idx => $item) {
                        $converted[$idx] = $item->toRawArray(false, true);
                    }
                    $return[$key] = $converted;

                    continue;
                }

                // Decode original array structure for per-entity comparison
                $originalArray = json_decode($originalValue, true);
                $converted     = [];

                foreach ($value as $idx => $item) {
                    // Compare current entity against its original state
                    $currentNormalized  = $this->normalizeValue($item);
                    $originalNormalized = $originalArray[$idx] ?? null;

                    // Only include if changed, new, or can't determine
                    if ($originalNormalized === null || $currentNormalized !== $originalNormalized) {
                        $converted[$idx] = $item->toRawArray(false, true);
                    }
                }

                // Only include this property if at least one entity changed
                if ($converted !== []) {
                    $return[$key] = $converted;
                }

                continue;
            }

            // For all other cases, use hasChanged()
            if (! $this->hasChanged($key)) {
                continue;
            }

            if ($recursive) {
                // Special handling for arrays (mixed or not all entities)
                if (is_array($value)) {
                    $converted = [];

                    foreach ($value as $idx => $item) {
                        $converted[$idx] = $item instanceof self ? $item->toRawArray(false, true) : $convert($item);
                    }
                    $return[$key] = $converted;

                    continue;
                }

                // default recursive conversion
                $return[$key] = $convert($value);

                continue;
            }

            // non-recursive changed value
            $return[$key] = $value;
        }

        return $return;
    }

    /**
     * Ensures our "original" values match the current values.
     *
     * Objects and arrays are normalized and JSON-encoded for reliable change detection,
     * while scalars are stored as-is for performance.
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original     = [];
        $this->_onlyScalars = true;

        foreach ($this->attributes as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $this->original[$key] = json_encode($this->normalizeValue($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $this->_onlyScalars   = false;
            } else {
                $this->original[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Checks a property to see if it has changed since the entity
     * was created. Or, without a parameter, checks if any
     * properties have changed.
     */
    public function hasChanged(?string $key = null): bool
    {
        // If no parameter was given then check all attributes
        if ($key === null) {
            if ($this->_onlyScalars) {
                return $this->original !== $this->attributes;
            }

            foreach (array_keys($this->attributes) as $attributeKey) {
                if ($this->hasChanged($attributeKey)) {
                    return true;
                }
            }

            return false;
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

        // It was removed
        if (array_key_exists($dbColumn, $this->original) && ! array_key_exists($dbColumn, $this->attributes)) {
            return true;
        }

        $originalValue = $this->original[$dbColumn];
        $currentValue  = $this->attributes[$dbColumn];

        // If original is a string, it was JSON-encoded (object or array)
        if (is_string($originalValue) && (is_object($currentValue) || is_array($currentValue))) {
            return $originalValue !== json_encode($this->normalizeValue($currentValue), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // For scalars, use direct comparison
        return $originalValue !== $currentValue;
    }

    /**
     * Checks if an array contains only Entity instances.
     * This allows optimization for per-entity change tracking.
     *
     * @param array<int|string, mixed> $data
     */
    private function containsOnlyEntities(array $data): bool
    {
        if ($data === []) {
            return false;
        }

        foreach ($data as $item) {
            if (! $item instanceof self) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recursively normalize a value for comparison.
     * Converts objects and arrays to a JSON-encodable format.
     */
    private function normalizeValue(mixed $data): mixed
    {
        if (is_array($data)) {
            $normalized = [];

            foreach ($data as $key => $value) {
                $normalized[$key] = $this->normalizeValue($value);
            }

            return $normalized;
        }

        if (is_object($data)) {
            // Check for Entity instance (use raw values, recursive)
            if ($data instanceof self) {
                $objectData = $data->toRawArray(false, true);
            } elseif ($data instanceof JsonSerializable) {
                $objectData = $data->jsonSerialize();
            } elseif (method_exists($data, 'toArray')) {
                $objectData = $data->toArray();
            } elseif ($data instanceof Traversable) {
                $objectData = iterator_to_array($data);
            } elseif ($data instanceof DateTimeInterface) {
                return [
                    '__class'    => $data::class,
                    '__datetime' => $data->format(DATE_RFC3339_EXTENDED),
                ];
            } elseif ($data instanceof UnitEnum) {
                return [
                    '__class' => $data::class,
                    '__enum'  => $data instanceof BackedEnum ? $data->value : $data->name,
                ];
            } else {
                $objectData = get_object_vars($data);

                // Fallback for value objects with __toString()
                // when properties are not accessible
                if ($objectData === [] && method_exists($data, '__toString')) {
                    return [
                        '__class'  => $data::class,
                        '__string' => (string) $data,
                    ];
                }
            }

            return [
                '__class' => $data::class,
                '__data'  => $this->normalizeValue($objectData),
            ];
        }

        // Return scalars and null as-is
        return $data;
    }

    /**
     * Set raw data array without any mutations.
     *
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function injectRawData(array $data)
    {
        $this->attributes = $data;

        $this->syncOriginal();

        return $this;
    }

    /**
     * Checks the datamap to see if this property name is being mapped,
     * and returns the DB column name, if any, or the original property name.
     *
     * @return string Database column name.
     */
    protected function mapProperty(string $key)
    {
        if ($this->datamap === []) {
            return $key;
        }

        if (array_key_exists($key, $this->datamap) && $this->datamap[$key] !== '') {
            return $this->datamap[$key];
        }

        return $key;
    }

    /**
     * Converts the given string|timestamp|DateTimeInterface instance
     * into the "CodeIgniter\I18n\Time" object.
     *
     * @param DateTimeInterface|float|int|string $value
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
     * Add ? at the beginning of the type (i.e. ?string) to get `null`
     * instead of casting $value when $value is null.
     *
     * @param bool|float|int|string|null $value     Attribute value
     * @param string                     $attribute Attribute name
     * @param string                     $method    Allowed to "get" and "set"
     *
     * @return array<int|string, mixed>|bool|float|int|object|string|null
     *
     * @throws CastException
     */
    protected function castAs($value, string $attribute, string $method = 'get')
    {
        if ($this->dataCaster() instanceof DataCaster) {
            return $this->dataCaster
                // @TODO if $casts is readonly, we don't need the setTypes() method.
                ->setTypes($this->casts)
                ->castAs($value, $attribute, $method);
        }

        return $value;
    }

    /**
     * Returns a DataCaster instance when casts are defined.
     * If no casts are configured, no DataCaster is created and null is returned.
     */
    protected function dataCaster(): ?DataCaster
    {
        if ($this->casts === []) {
            $this->dataCaster = null;

            return null;
        }

        if (! $this->dataCaster instanceof DataCaster) {
            $this->dataCaster = new DataCaster(
                array_merge($this->defaultCastHandlers, $this->castHandlers),
                null,
                null,
                false,
            );
        }

        return $this->dataCaster;
    }

    /**
     * Support for json_encode().
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Change the value of the private $_cast property.
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
     * @param array<int|string, mixed>|bool|float|int|object|string|null $value
     *
     * @return void
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

        // if a setter method exists for this key, use that method to
        // insert this value. should be outside $isNullable check,
        // so maybe wants to do sth with null value automatically
        $method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $dbColumn)));

        // If a "`_set` + $key" method exists, it is a setter.
        if (method_exists($this, '_' . $method)) {
            $this->{'_' . $method}($value);

            return;
        }

        // If a "`set` + $key" method exists, it is also a setter.
        if (method_exists($this, $method)) {
            $this->{$method}($value);

            return;
        }

        // Otherwise, just the value. This allows for creation of new
        // class properties that are undefined, though they cannot be
        // saved. Useful for grabbing values through joins, assigning
        // relationships, etc.
        $this->attributes[$dbColumn] = $value;
    }

    /**
     * Magic method to allow retrieval of protected and private class properties
     * either by their name, or through a `getCamelCasedProperty()` method.
     *
     * Examples:
     *  $p = $this->my_property
     *  $p = $this->getMyProperty()
     *
     * @return array<int|string, mixed>|bool|float|int|object|string|null
     *
     * @throws Exception
     */
    public function __get(string $key)
    {
        $dbColumn = $this->mapProperty($key);

        $result = null;

        // Convert to CamelCase for the method
        $method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $dbColumn)));

        // if a getter method exists for this key,
        // use that method to insert this value.
        if (method_exists($this, '_' . $method)) {
            // If a "`_get` + $key" method exists, it is a getter.
            $result = $this->{'_' . $method}();
        } elseif (method_exists($this, $method)) {
            // If a "`get` + $key" method exists, it is also a getter.
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
