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

use Exception;
use JsonSerializable;

/**
 * Entity encapsulation, for use with CodeIgniter\Model
 *
 * @see \CodeIgniter\Entity\EntityTest
 */
interface EntityInterface extends JsonSerializable
{
    /**
     * Allows filling in Entity parameters during construction.
     */
    public function __construct(?array $data = null);

    /**
     * Takes an array of key/value pairs and sets them as class
     * properties, using any `setCamelCasedProperty()` methods
     * that may or may not exist.
     *
     * @param array<string, array|bool|float|int|object|string|null> $data
     *
     * @return $this
     */
    public function fill(?array $data = null);

    /**
     * General method that will return all public and protected values
     * of this entity as an array. All values are accessed through the
     * __get() magic method so will have any casts, etc applied to them.
     *
     * @param bool $onlyChanged If true, only return values that have changed since object creation
     * @param bool $cast        If true, properties will be cast.
     * @param bool $recursive   If true, inner entities will be cast as array as well.
     */
    public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array;

    /**
     * Returns the raw values of the current attributes.
     *
     * @param bool $onlyChanged If true, only return values that have changed since object creation
     * @param bool $recursive   If true, inner entities will be cast as array as well.
     */
    public function toRawArray(bool $onlyChanged = false, bool $recursive = false): array;

    /**
     * Ensures our "original" values match the current values.
     *
     * @return $this
     */
    public function syncOriginal();

    /**
     * Checks a property to see if it has changed since the entity
     * was created. Or, without a parameter, checks if any
     * properties have changed.
     *
     * @param string|null $key class property
     */
    public function hasChanged(?string $key = null): bool;

    /**
     * Set raw data array without any mutations
     *
     * @return $this
     */
    public function injectRawData(array $data);

    /**
     * Set raw data array without any mutations
     *
     * @return $this
     *
     * @deprecated Use injectRawData() instead.
     */
    public function setAttributes(array $data);

    /**
     * Change the value of the private $_cast property
     *
     * @return bool|Entity
     */
    public function cast(?bool $cast = null);

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
     * @return void
     *
     * @throws Exception
     */
    public function __set(string $key, $value = null);

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
    public function __get(string $key);

    /**
     * Returns true if a property exists names $key, or a getter method
     * exists named like for __get().
     */
    public function __isset(string $key): bool;

    /**
     * Unsets an attribute property.
     */
    public function __unset(string $key): void;
}
