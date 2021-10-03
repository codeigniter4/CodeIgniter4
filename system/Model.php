<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter;

use BadMethodCallException;
use Closure;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\Query;
use CodeIgniter\Exceptions\ModelException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Validation\ValidationInterface;
use Config\Database;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * The Model class extends BaseModel and provides additional
 * convenient features that makes working with a SQL database
 * table less painful.
 *
 * It will:
 *      - automatically connect to database
 *      - allow intermingling calls to the builder
 *      - removes the need to use Result object directly in most cases
 *
 * @mixin BaseBuilder
 *
 * @property BaseConnection $db
 */
class Model extends BaseModel
{
    /**
     * Name of database table
     *
     * @var string
     */
    protected $table;

    /**
     * The table's primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Whether primary key uses auto increment.
     *
     * @var bool
     */
    protected $useAutoIncrement = true;

    /**
     * Query Builder object
     *
     * @var BaseBuilder|null
     */
    protected $builder;

    /**
     * Holds information passed in via 'set'
     * so that we can capture it (not the builder)
     * and ensure it gets validated first.
     *
     * @var array
     */
    protected $tempData = [];

    /**
     * Escape array that maps usage of escape
     * flag for every parameter.
     *
     * @var array
     */
    protected $escape = [];

    public function __construct(?ConnectionInterface &$db = null, ?ValidationInterface $validation = null)
    {
        /**
         * @var BaseConnection $db
         */
        $db = $db ?? Database::connect($this->DBGroup);

        $this->db = &$db;

        parent::__construct($validation);
    }

    /**
     * Specify the table associated with a model
     *
     * @param string $table Table
     *
     * @return $this
     */
    public function setTable(string $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Fetches the row of database from $this->table with a primary key
     * matching $id. This methods works only with dbCalls
     * This methods works only with dbCalls
     *
     * @param bool                  $singleton Single or multiple results
     * @param array|int|string|null $id        One primary key or an array of primary keys
     *
     * @return array|object|null The resulting row of data, or null.
     */
    protected function doFind(bool $singleton, $id = null)
    {
        $builder = $this->builder();

        if ($this->tempUseSoftDeletes) {
            $builder->where($this->table . '.' . $this->deletedField, null);
        }

        if (is_array($id)) {
            $row = $builder->whereIn($this->table . '.' . $this->primaryKey, $id)
                ->get()
                ->getResult($this->tempReturnType);
        } elseif ($singleton) {
            $row = $builder->where($this->table . '.' . $this->primaryKey, $id)
                ->get()
                ->getFirstRow($this->tempReturnType);
        } else {
            $row = $builder->get()->getResult($this->tempReturnType);
        }

        return $row;
    }

    /**
     * Fetches the column of database from $this->table
     * This methods works only with dbCalls
     *
     * @param string $columnName Column Name
     *
     * @return array|null The resulting row of data, or null if no data found.
     */
    protected function doFindColumn(string $columnName)
    {
        return $this->select($columnName)->asArray()->find(); // @phpstan-ignore-line
    }

    /**
     * Works with the current Query Builder instance to return
     * all results, while optionally limiting them.
     * This methods works only with dbCalls
     *
     * @param int $limit  Limit
     * @param int $offset Offset
     *
     * @return array
     */
    protected function doFindAll(int $limit = 0, int $offset = 0)
    {
        $builder = $this->builder();

        if ($this->tempUseSoftDeletes) {
            $builder->where($this->table . '.' . $this->deletedField, null);
        }

        return $builder->limit($limit, $offset)
            ->get()
            ->getResult($this->tempReturnType);
    }

    /**
     * Returns the first row of the result set. Will take any previous
     * Query Builder calls into account when determining the result set.
     * This methods works only with dbCalls
     *
     * @return array|object|null
     */
    protected function doFirst()
    {
        $builder = $this->builder();

        if ($this->tempUseSoftDeletes) {
            $builder->where($this->table . '.' . $this->deletedField, null);
        } elseif ($this->useSoftDeletes && empty($builder->QBGroupBy) && $this->primaryKey) {
            $builder->groupBy($this->table . '.' . $this->primaryKey);
        }

        // Some databases, like PostgreSQL, need order
        // information to consistently return correct results.
        if ($builder->QBGroupBy && empty($builder->QBOrderBy) && $this->primaryKey) {
            $builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
        }

        return $builder->limit(1, 0)->get()->getFirstRow($this->tempReturnType);
    }

    /**
     * Inserts data into the current table.
     * This methods works only with dbCalls
     *
     * @param array $data Data
     *
     * @return bool|Query
     */
    protected function doInsert(array $data)
    {
        $escape       = $this->escape;
        $this->escape = [];

        // Require non empty primaryKey when
        // not using auto-increment feature
        if (! $this->useAutoIncrement && empty($data[$this->primaryKey])) {
            throw DataException::forEmptyPrimaryKey('insert');
        }

        $builder = $this->builder();

        // Must use the set() method to ensure to set the correct escape flag
        foreach ($data as $key => $val) {
            $builder->set($key, $val, $escape[$key] ?? null);
        }

        $result = $builder->insert();

        // If insertion succeeded then save the insert ID
        if ($result) {
            $this->insertID = ! $this->useAutoIncrement ? $data[$this->primaryKey] : $this->db->insertID();
        }

        return $result;
    }

    /**
     * Compiles batch insert strings and runs the queries, validating each row prior.
     * This methods works only with dbCalls
     *
     * @param array|null $set       An associative array of insert values
     * @param bool|null  $escape    Whether to escape values
     * @param int        $batchSize The size of the batch to run
     * @param bool       $testing   True means only number of records is returned, false will execute the query
     *
     * @return bool|int Number of rows inserted or FALSE on failure
     */
    protected function doInsertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
    {
        if (is_array($set)) {
            foreach ($set as $row) {
                // Require non empty primaryKey when
                // not using auto-increment feature
                if (! $this->useAutoIncrement && empty($row[$this->primaryKey])) {
                    throw DataException::forEmptyPrimaryKey('insertBatch');
                }
            }
        }

        return $this->builder()->testMode($testing)->insertBatch($set, $escape, $batchSize);
    }

    /**
     * Updates a single record in $this->table.
     * This methods works only with dbCalls
     *
     * @param array|int|string|null $id
     * @param array|null            $data
     */
    protected function doUpdate($id = null, $data = null): bool
    {
        $escape       = $this->escape;
        $this->escape = [];

        $builder = $this->builder();

        if ($id) {
            $builder = $builder->whereIn($this->table . '.' . $this->primaryKey, $id);
        }

        // Must use the set() method to ensure to set the correct escape flag
        foreach ($data as $key => $val) {
            $builder->set($key, $val, $escape[$key] ?? null);
        }

        return $builder->update();
    }

    /**
     * Compiles an update string and runs the query
     * This methods works only with dbCalls
     *
     * @param array|null  $set       An associative array of update values
     * @param string|null $index     The where key
     * @param int         $batchSize The size of the batch to run
     * @param bool        $returnSQL True means SQL is returned, false will execute the query
     *
     * @throws DatabaseException
     *
     * @return mixed Number of rows affected or FALSE on failure
     */
    protected function doUpdateBatch(?array $set = null, ?string $index = null, int $batchSize = 100, bool $returnSQL = false)
    {
        return $this->builder()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
    }

    /**
     * Deletes a single record from $this->table where $id matches
     * the table's primaryKey
     * This methods works only with dbCalls
     *
     * @param array|int|string|null $id    The rows primary key(s)
     * @param bool                  $purge Allows overriding the soft deletes setting.
     *
     * @throws DatabaseException
     *
     * @return bool|string
     */
    protected function doDelete($id = null, bool $purge = false)
    {
        $builder = $this->builder();

        if ($id) {
            $builder = $builder->whereIn($this->primaryKey, $id);
        }

        if ($this->useSoftDeletes && ! $purge) {
            if (empty($builder->getCompiledQBWhere())) {
                if (CI_DEBUG) {
                    throw new DatabaseException(
                        'Deletes are not allowed unless they contain a "where" or "like" clause.'
                    );
                }

                return false; // @codeCoverageIgnore
            }

            $set[$this->deletedField] = $this->setDate();

            if ($this->useTimestamps && $this->updatedField) {
                $set[$this->updatedField] = $this->setDate();
            }

            return $builder->update($set);
        }

        return $builder->delete();
    }

    /**
     * Permanently deletes all rows that have been marked as deleted
     * through soft deletes (deleted = 1)
     * This methods works only with dbCalls
     *
     * @return bool|mixed
     */
    protected function doPurgeDeleted()
    {
        return $this->builder()
            ->where($this->table . '.' . $this->deletedField . ' IS NOT NULL')
            ->delete();
    }

    /**
     * Works with the find* methods to return only the rows that
     * have been deleted.
     * This methods works only with dbCalls
     */
    protected function doOnlyDeleted()
    {
        $this->builder()->where($this->table . '.' . $this->deletedField . ' IS NOT NULL');
    }

    /**
     * Compiles a replace into string and runs the query
     * This methods works only with dbCalls
     *
     * @param array|null $data      Data
     * @param bool       $returnSQL Set to true to return Query String
     *
     * @return mixed
     */
    protected function doReplace(?array $data = null, bool $returnSQL = false)
    {
        return $this->builder()->testMode($returnSQL)->replace($data);
    }

    /**
     * Grabs the last error(s) that occurred from the Database connection.
     * The return array should be in the following format:
     *  ['source' => 'message']
     * This methods works only with dbCalls
     *
     * @return array<string,string>
     */
    protected function doErrors()
    {
        // $error is always ['code' => string|int, 'message' => string]
        $error = $this->db->error();

        if ((int) $error['code'] === 0) {
            return [];
        }

        return [get_class($this->db) => $error['message']];
    }

    /**
     * Returns the id value for the data array or object
     *
     * @param array|object $data Data
     *
     * @return array|int|string|null
     *
     * @deprecated Use getIdValue() instead. Will be removed in version 5.0.
     */
    protected function idValue($data)
    {
        return $this->getIdValue($data);
    }

    /**
     * Returns the id value for the data array or object
     *
     * @param array|object $data Data
     *
     * @return array|int|string|null
     */
    public function getIdValue($data)
    {
        if (is_object($data) && isset($data->{$this->primaryKey})) {
            return $data->{$this->primaryKey};
        }

        if (is_array($data) && ! empty($data[$this->primaryKey])) {
            return $data[$this->primaryKey];
        }

        return null;
    }

    /**
     * Loops over records in batches, allowing you to operate on them.
     * Works with $this->builder to get the Compiled select to
     * determine the rows to operate on.
     * This methods works only with dbCalls
     *
     * @throws DataException
     */
    public function chunk(int $size, Closure $userFunc)
    {
        $total  = $this->builder()->countAllResults(false);
        $offset = 0;

        while ($offset <= $total) {
            $builder = clone $this->builder();
            $rows    = $builder->get($size, $offset);

            if (! $rows) {
                throw DataException::forEmptyDataset('chunk');
            }

            $rows = $rows->getResult($this->tempReturnType);

            $offset += $size;

            if (empty($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if ($userFunc($row) === false) {
                    return;
                }
            }
        }
    }

    /**
     * Override countAllResults to account for soft deleted accounts.
     *
     * @return mixed
     */
    public function countAllResults(bool $reset = true, bool $test = false)
    {
        if ($this->tempUseSoftDeletes) {
            $this->builder()->where($this->table . '.' . $this->deletedField, null);
        }

        // When $reset === false, the $tempUseSoftDeletes will be
        // dependant on $useSoftDeletes value because we don't
        // want to add the same "where" condition for the second time
        $this->tempUseSoftDeletes = $reset
            ? $this->useSoftDeletes
            : ($this->useSoftDeletes ? false : $this->useSoftDeletes);

        return $this->builder()->testMode($test)->countAllResults($reset);
    }

    /**
     * Provides a shared instance of the Query Builder.
     *
     * @throws ModelException
     *
     * @return BaseBuilder
     */
    public function builder(?string $table = null)
    {
        // Check for an existing Builder
        if ($this->builder instanceof BaseBuilder) {
            // Make sure the requested table matches the builder
            if ($table && $this->builder->getTable() !== $table) {
                return $this->db->table($table);
            }

            return $this->builder;
        }

        // We're going to force a primary key to exist
        // so we don't have overly convoluted code,
        // and future features are likely to require them.
        if (empty($this->primaryKey)) {
            throw ModelException::forNoPrimaryKey(static::class);
        }

        $table = empty($table) ? $this->table : $table;

        // Ensure we have a good db connection
        if (! $this->db instanceof BaseConnection) {
            $this->db = Database::connect($this->DBGroup);
        }

        $builder = $this->db->table($table);

        // Only consider it "shared" if the table is correct
        if ($table === $this->table) {
            $this->builder = $builder;
        }

        return $builder;
    }

    /**
     * Captures the builder's set() method so that we can validate the
     * data here. This allows it to be used with any of the other
     * builder methods and still get validated data, like replace.
     *
     * @param mixed     $key    Field name, or an array of field/value pairs
     * @param mixed     $value  Field value, if $key is a single field
     * @param bool|null $escape Whether to escape values
     *
     * @return $this
     */
    public function set($key, $value = '', ?bool $escape = null)
    {
        $data = is_array($key) ? $key : [$key => $value];

        foreach (array_keys($data) as $k) {
            $this->tempData['escape'][$k] = $escape;
        }

        $this->tempData['data'] = array_merge($this->tempData['data'] ?? [], $data);

        return $this;
    }

    /**
     * This method is called on save to determine if entry have to be updated
     * If this method return false insert operation will be executed
     *
     * @param array|object $data Data
     */
    protected function shouldUpdate($data): bool
    {
        // When useAutoIncrement feature is disabled check
        // in the database if given record already exists
        return parent::shouldUpdate($data)
            && $this->useAutoIncrement
                ? true
                : $this->where($this->primaryKey, $this->getIdValue($data))->countAllResults() === 1;
    }

    /**
     * Inserts data into the database. If an object is provided,
     * it will attempt to convert it to an array.
     *
     * @param array|object|null $data
     * @param bool              $returnID Whether insert ID should be returned or not.
     *
     * @throws ReflectionException
     *
     * @return BaseResult|false|int|object|string
     */
    public function insert($data = null, bool $returnID = true)
    {
        if (! empty($this->tempData['data'])) {
            if (empty($data)) {
                $data = $this->tempData['data'] ?? null;
            } else {
                $data = $this->transformDataToArray($data, 'insert');
                $data = array_merge($this->tempData['data'], $data);
            }
        }

        $this->escape   = $this->tempData['escape'] ?? [];
        $this->tempData = [];

        return parent::insert($data, $returnID);
    }

    /**
     * Updates a single record in the database. If an object is provided,
     * it will attempt to convert it into an array.
     *
     * @param array|int|string|null $id
     * @param array|object|null     $data
     *
     * @throws ReflectionException
     */
    public function update($id = null, $data = null): bool
    {
        if (! empty($this->tempData['data'])) {
            if (empty($data)) {
                $data = $this->tempData['data'] ?? null;
            } else {
                $data = $this->transformDataToArray($data, 'update');
                $data = array_merge($this->tempData['data'], $data);
            }
        }

        $this->escape   = $this->tempData['escape'] ?? [];
        $this->tempData = [];

        return parent::update($id, $data);
    }

    /**
     * Takes a class an returns an array of it's public and protected
     * properties as an array with raw values.
     *
     * @param object|string $data
     * @param bool          $recursive If true, inner entities will be casted as array as well
     *
     * @throws ReflectionException
     *
     * @return array|null Array
     */
    protected function objectToRawArray($data, bool $onlyChanged = true, bool $recursive = false): ?array
    {
        $properties = parent::objectToRawArray($data, $onlyChanged);

        // Always grab the primary key otherwise updates will fail.
        if (
            method_exists($data, 'toRawArray') && (! empty($properties) && ! empty($this->primaryKey) && ! in_array($this->primaryKey, $properties, true)
            && ! empty($data->{$this->primaryKey}))
        ) {
            $properties[$this->primaryKey] = $data->{$this->primaryKey};
        }

        return $properties;
    }

    /**
     * Provides/instantiates the builder/db connection and model's table/primary key names and return type.
     *
     * @param string $name Name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (parent::__isset($name)) {
            return parent::__get($name);
        }

        if (isset($this->builder()->{$name})) {
            return $this->builder()->{$name};
        }

        return null;
    }

    /**
     * Checks for the existence of properties across this model, builder, and db connection.
     *
     * @param string $name Name
     */
    public function __isset(string $name): bool
    {
        if (parent::__isset($name)) {
            return true;
        }

        return isset($this->builder()->{$name});
    }

    /**
     * Provides direct access to method in the builder (if available)
     * and the database connection.
     *
     * @return mixed
     */
    public function __call(string $name, array $params)
    {
        $builder = $this->builder();
        $result  = null;

        if (method_exists($this->db, $name)) {
            $result = $this->db->{$name}(...$params);
        } elseif (method_exists($builder, $name)) {
            $result = $builder->{$name}(...$params);
        } else {
            throw new BadMethodCallException('Call to undefined method ' . static::class . '::' . $name);
        }

        if ($result instanceof BaseBuilder) {
            return $this;
        }

        return $result;
    }

    /**
     * Takes a class an returns an array of it's public and protected
     * properties as an array suitable for use in creates and updates.
     *
     * @param object|string $data
     * @param string|null   $primaryKey
     *
     * @throws ReflectionException
     *
     * @codeCoverageIgnore
     *
     * @deprecated since 4.1
     */
    public static function classToArray($data, $primaryKey = null, string $dateFormat = 'datetime', bool $onlyChanged = true): array
    {
        if (method_exists($data, 'toRawArray')) {
            $properties = $data->toRawArray($onlyChanged);

            // Always grab the primary key otherwise updates will fail.
            if (! empty($properties) && ! empty($primaryKey) && ! in_array($primaryKey, $properties, true) && ! empty($data->{$primaryKey})) {
                $properties[$primaryKey] = $data->{$primaryKey};
            }
        } else {
            $mirror = new ReflectionClass($data);
            $props  = $mirror->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

            $properties = [];

            // Loop over each property,
            // saving the name/value in a new array we can return.
            foreach ($props as $prop) {
                // Must make protected values accessible.
                $prop->setAccessible(true);
                $properties[$prop->getName()] = $prop->getValue($data);
            }
        }

        // Convert any Time instances to appropriate $dateFormat
        if ($properties) {
            foreach ($properties as $key => $value) {
                if ($value instanceof Time) {
                    switch ($dateFormat) {
                        case 'datetime':
                            $converted = $value->format('Y-m-d H:i:s');
                            break;

                        case 'date':
                            $converted = $value->format('Y-m-d');
                            break;

                        case 'int':
                            $converted = $value->getTimestamp();
                            break;

                        default:
                            $converted = (string) $value;
                    }

                    $properties[$key] = $converted;
                }
            }
        }

        return $properties;
    }
}
