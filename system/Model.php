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

namespace CodeIgniter;

use Closure;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Exceptions\BadMethodCallException;
use CodeIgniter\Exceptions\ModelException;
use CodeIgniter\Validation\ValidationInterface;
use Config\Database;
use Config\Feature;
use stdClass;

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
 * @property-read BaseConnection $db
 *
 * @method $this groupBy($by, ?bool $escape = null)
 * @method $this groupEnd()
 * @method $this groupStart()
 * @method $this having($key, $value = null, ?bool $escape = null)
 * @method $this havingGroupEnd()
 * @method $this havingGroupStart()
 * @method $this havingIn(?string $key = null, $values = null, ?bool $escape = null)
 * @method $this havingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this havingNotIn(?string $key = null, $values = null, ?bool $escape = null)
 * @method $this join(string $table, string $cond, string $type = '', ?bool $escape = null)
 * @method $this like($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this limit(?int $value = null, ?int $offset = 0)
 * @method $this notGroupStart()
 * @method $this notHavingGroupStart()
 * @method $this notHavingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this notLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this offset(int $offset)
 * @method $this orderBy(string $orderBy, string $direction = '', ?bool $escape = null)
 * @method $this orGroupStart()
 * @method $this orHaving($key, $value = null, ?bool $escape = null)
 * @method $this orHavingGroupStart()
 * @method $this orHavingIn(?string $key = null, $values = null, ?bool $escape = null)
 * @method $this orHavingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this orHavingNotIn(?string $key = null, $values = null, ?bool $escape = null)
 * @method $this orLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this orNotGroupStart()
 * @method $this orNotHavingGroupStart()
 * @method $this orNotHavingLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this orNotLike($field, string $match = '', string $side = 'both', ?bool $escape = null, bool $insensitiveSearch = false)
 * @method $this orWhere($key, $value = null, ?bool $escape = null)
 * @method $this orWhereIn(?string $key = null, $values = null, ?bool $escape = null)
 * @method $this orWhereNotIn(?string $key = null, $values = null, ?bool $escape = null)
 * @method $this select($select = '*', ?bool $escape = null)
 * @method $this selectAvg(string $select = '', string $alias = '')
 * @method $this selectCount(string $select = '', string $alias = '')
 * @method $this selectMax(string $select = '', string $alias = '')
 * @method $this selectMin(string $select = '', string $alias = '')
 * @method $this selectSum(string $select = '', string $alias = '')
 * @method $this when($condition, callable $callback, ?callable $defaultCallback = null)
 * @method $this whenNot($condition, callable $callback, ?callable $defaultCallback = null)
 * @method $this where($key, $value = null, ?bool $escape = null)
 * @method $this whereIn(?string $key = null, $values = null, ?bool $escape = null)
 * @method $this whereNotIn(?string $key = null, $values = null, ?bool $escape = null)
 *
 * @phpstan-import-type row_array from BaseModel
 */
class Model extends BaseModel
{
    /**
     * Name of database table.
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
     * Query Builder object.
     *
     * @var BaseBuilder|null
     */
    protected $builder;

    /**
     * Holds information passed in via 'set'
     * so that we can capture it (not the builder)
     * and ensure it gets validated first.
     *
     * @var array{escape: array<int|string, bool|null>, data: row_array}|array{}
     */
    protected $tempData = [];

    /**
     * Escape array that maps usage of escape
     * flag for every parameter.
     *
     * @var array<int|string, bool|null>
     */
    protected $escape = [];

    /**
     * Builder method names that should not be used in the Model.
     *
     * @var list<string>
     */
    private array $builderMethodsNotAvailable = [
        'getCompiledInsert',
        'getCompiledSelect',
        'getCompiledUpdate',
    ];

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        /**
         * @var BaseConnection|null $db
         */
        $db ??= Database::connect($this->DBGroup);

        $this->db = $db;

        parent::__construct($validation);
    }

    /**
     * Specify the table associated with a model.
     *
     * @return $this
     */
    public function setTable(string $table)
    {
        $this->table = $table;

        return $this;
    }

    protected function doFind(bool $singleton, $id = null)
    {
        $builder = $this->builder();
        $useCast = $this->useCasts();

        if ($useCast) {
            $returnType = $this->tempReturnType;
            $this->asArray();
        }

        if ($this->tempUseSoftDeletes) {
            $builder->where($this->table . '.' . $this->deletedField, null);
        }

        $row  = null;
        $rows = [];

        if (is_array($id)) {
            $rows = $builder->whereIn($this->table . '.' . $this->primaryKey, $id)
                ->get()
                ->getResult($this->tempReturnType);
        } elseif ($singleton) {
            $row = $builder->where($this->table . '.' . $this->primaryKey, $id)
                ->get()
                ->getFirstRow($this->tempReturnType);
        } else {
            $rows = $builder->get()->getResult($this->tempReturnType);
        }

        if ($useCast) {
            $this->tempReturnType = $returnType;

            if ($singleton) {
                if ($row === null) {
                    return null;
                }

                return $this->convertToReturnType($row, $returnType);
            }

            foreach ($rows as $i => $row) {
                $rows[$i] = $this->convertToReturnType($row, $returnType);
            }

            return $rows;
        }

        if ($singleton) {
            return $row;
        }

        return $rows;
    }

    protected function doFindColumn(string $columnName)
    {
        return $this->select($columnName)->asArray()->find();
    }

    /**
     * {@inheritDoc}
     *
     * Works with the current Query Builder instance.
     */
    protected function doFindAll(?int $limit = null, int $offset = 0)
    {
        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll) {
            $limit ??= 0;
        }

        $builder = $this->builder();

        $useCast = $this->useCasts();
        if ($useCast) {
            $returnType = $this->tempReturnType;
            $this->asArray();
        }

        if ($this->tempUseSoftDeletes) {
            $builder->where($this->table . '.' . $this->deletedField, null);
        }

        $results = $builder->limit($limit, $offset)
            ->get()
            ->getResult($this->tempReturnType);

        if ($useCast) {
            foreach ($results as $i => $row) {
                $results[$i] = $this->convertToReturnType($row, $returnType);
            }

            $this->tempReturnType = $returnType;
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     *
     * Will take any previous Query Builder calls into account
     * when determining the result set.
     */
    protected function doFirst()
    {
        $builder = $this->builder();

        $useCast = $this->useCasts();
        if ($useCast) {
            $returnType = $this->tempReturnType;
            $this->asArray();
        }

        if ($this->tempUseSoftDeletes) {
            $builder->where($this->table . '.' . $this->deletedField, null);
        } elseif ($this->useSoftDeletes && ($builder->QBGroupBy === []) && $this->primaryKey !== '') {
            $builder->groupBy($this->table . '.' . $this->primaryKey);
        }

        // Some databases, like PostgreSQL, need order
        // information to consistently return correct results.
        if ($builder->QBGroupBy !== [] && ($builder->QBOrderBy === []) && $this->primaryKey !== '') {
            $builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
        }

        $row = $builder->limit(1, 0)->get()->getFirstRow($this->tempReturnType);

        if ($useCast && $row !== null) {
            $row = $this->convertToReturnType($row, $returnType);

            $this->tempReturnType = $returnType;
        }

        return $row;
    }

    protected function doInsert(array $row)
    {
        $escape       = $this->escape;
        $this->escape = [];

        // Require non-empty primaryKey when
        // not using auto-increment feature
        if (! $this->useAutoIncrement) {
            if (! isset($row[$this->primaryKey])) {
                throw DataException::forEmptyPrimaryKey('insert');
            }

            // Validate the primary key value (arrays not allowed for insert)
            $this->validateID($row[$this->primaryKey], false);
        }

        $builder = $this->builder();

        // Must use the set() method to ensure to set the correct escape flag
        foreach ($row as $key => $val) {
            $builder->set($key, $val, $escape[$key] ?? null);
        }

        if ($this->allowEmptyInserts && $row === []) {
            $table = $this->db->protectIdentifiers($this->table, true, null, false);
            if ($this->db->getPlatform() === 'MySQLi') {
                $sql = 'INSERT INTO ' . $table . ' VALUES ()';
            } elseif ($this->db->getPlatform() === 'OCI8') {
                $allFields = $this->db->protectIdentifiers(
                    array_map(
                        static fn ($row) => $row->name,
                        $this->db->getFieldData($this->table),
                    ),
                    false,
                    true,
                );

                $sql = sprintf(
                    'INSERT INTO %s (%s) VALUES (%s)',
                    $table,
                    implode(',', $allFields),
                    substr(str_repeat(',DEFAULT', count($allFields)), 1),
                );
            } else {
                $sql = 'INSERT INTO ' . $table . ' DEFAULT VALUES';
            }

            $result = $this->db->query($sql);
        } else {
            $result = $builder->insert();
        }

        // If insertion succeeded then save the insert ID
        if ($result) {
            $this->insertID = $this->useAutoIncrement ? $this->db->insertID() : $row[$this->primaryKey];
        }

        return $result;
    }

    protected function doInsertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
    {
        if (is_array($set) && ! $this->useAutoIncrement) {
            foreach ($set as $row) {
                // Require non-empty $primaryKey when
                // not using auto-increment feature
                if (! isset($row[$this->primaryKey])) {
                    throw DataException::forEmptyPrimaryKey('insertBatch');
                }

                // Validate the primary key value
                $this->validateID($row[$this->primaryKey], false);
            }
        }

        return $this->builder()->testMode($testing)->insertBatch($set, $escape, $batchSize);
    }

    protected function doUpdate($id = null, $row = null): bool
    {
        $escape       = $this->escape;
        $this->escape = [];

        $builder = $this->builder();

        if (is_array($id) && $id !== []) {
            $builder = $builder->whereIn($this->table . '.' . $this->primaryKey, $id);
        }

        // Must use the set() method to ensure to set the correct escape flag
        foreach ($row as $key => $val) {
            $builder->set($key, $val, $escape[$key] ?? null);
        }

        if ($builder->getCompiledQBWhere() === []) {
            throw new DatabaseException(
                'Updates are not allowed unless they contain a "where" or "like" clause.',
            );
        }

        return $builder->update();
    }

    protected function doUpdateBatch(?array $set = null, ?string $index = null, int $batchSize = 100, bool $returnSQL = false)
    {
        return $this->builder()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
    }

    protected function doDelete($id = null, bool $purge = false)
    {
        $set     = [];
        $builder = $this->builder();

        if (is_array($id) && $id !== []) {
            $builder = $builder->whereIn($this->primaryKey, $id);
        }

        if ($this->useSoftDeletes && ! $purge) {
            if ($builder->getCompiledQBWhere() === []) {
                throw new DatabaseException(
                    'Deletes are not allowed unless they contain a "where" or "like" clause.',
                );
            }

            $builder->where($this->deletedField);

            $set[$this->deletedField] = $this->setDate();

            if ($this->useTimestamps && $this->updatedField !== '') {
                $set[$this->updatedField] = $this->setDate();
            }

            return $builder->update($set);
        }

        return $builder->delete();
    }

    protected function doPurgeDeleted()
    {
        return $this->builder()
            ->where($this->table . '.' . $this->deletedField . ' IS NOT NULL')
            ->delete();
    }

    protected function doOnlyDeleted()
    {
        $this->builder()->where($this->table . '.' . $this->deletedField . ' IS NOT NULL');
    }

    protected function doReplace(?array $row = null, bool $returnSQL = false)
    {
        return $this->builder()->testMode($returnSQL)->replace($row);
    }

    /**
     * {@inheritDoc}
     *
     * The return array should be in the following format:
     *  `['source' => 'message']`.
     * This method works only with dbCalls.
     */
    protected function doErrors()
    {
        // $error is always ['code' => string|int, 'message' => string]
        $error = $this->db->error();

        if ((int) $error['code'] === 0) {
            return [];
        }

        return [$this->db::class => $error['message']];
    }

    public function getIdValue($row)
    {
        if (is_object($row)) {
            // Get the raw or mapped primary key value of the Entity.
            if ($row instanceof Entity && $row->{$this->primaryKey} !== null) {
                $cast = $row->cast();

                // Disable Entity casting, because raw primary key value is needed for database.
                $row->cast(false);

                $primaryKey = $row->{$this->primaryKey};

                // Restore Entity casting setting.
                $row->cast($cast);

                return $primaryKey;
            }

            if (! $row instanceof Entity && isset($row->{$this->primaryKey})) {
                return $row->{$this->primaryKey};
            }
        }

        if (is_array($row) && isset($row[$this->primaryKey])) {
            return $row[$this->primaryKey];
        }

        return null;
    }

    public function countAllResults(bool $reset = true, bool $test = false)
    {
        if ($this->tempUseSoftDeletes) {
            $this->builder()->where($this->table . '.' . $this->deletedField, null);
        }

        // When $reset === false, the $tempUseSoftDeletes will be
        // dependent on $useSoftDeletes value because we don't
        // want to add the same "where" condition for the second time.
        $this->tempUseSoftDeletes = $reset
            ? $this->useSoftDeletes
            : ($this->useSoftDeletes ? false : $this->useSoftDeletes);

        return $this->builder()->testMode($test)->countAllResults($reset);
    }

    /**
     * {@inheritDoc}
     *
     * Works with `$this->builder` to get the Compiled select to
     * determine the rows to operate on.
     * This method works only with dbCalls.
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

            if ($rows === []) {
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
     * Provides a shared instance of the Query Builder.
     *
     * @param non-empty-string|null $table
     *
     * @return BaseBuilder
     *
     * @throws ModelException
     */
    public function builder(?string $table = null)
    {
        // Check for an existing Builder
        if ($this->builder instanceof BaseBuilder) {
            // Make sure the requested table matches the builder
            if ((string) $table !== '' && $this->builder->getTable() !== $table) {
                return $this->db->table($table);
            }

            return $this->builder;
        }

        // We're going to force a primary key to exist
        // so we don't have overly convoluted code,
        // and future features are likely to require them.
        if ($this->primaryKey === '') {
            throw ModelException::forNoPrimaryKey(static::class);
        }

        $table = ((string) $table === '') ? $this->table : $table;

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
     * @param object|row_array|string           $key    Field name, or an array of field/value pairs, or an object
     * @param bool|float|int|object|string|null $value  Field value, if $key is a single field
     * @param bool|null                         $escape Whether to escape values
     *
     * @return $this
     */
    public function set($key, $value = '', ?bool $escape = null)
    {
        if (is_object($key)) {
            $key = $key instanceof stdClass ? (array) $key : $this->objectToArray($key);
        }

        $data = is_array($key) ? $key : [$key => $value];

        foreach (array_keys($data) as $k) {
            $this->tempData['escape'][$k] = $escape;
        }

        $this->tempData['data'] = array_merge($this->tempData['data'] ?? [], $data);

        return $this;
    }

    protected function shouldUpdate($row): bool
    {
        if (parent::shouldUpdate($row) === false) {
            return false;
        }

        if ($this->useAutoIncrement === true) {
            return true;
        }

        // When useAutoIncrement feature is disabled, check
        // in the database if given record already exists
        return $this->where($this->primaryKey, $this->getIdValue($row))->countAllResults() === 1;
    }

    public function insert($row = null, bool $returnID = true)
    {
        if (isset($this->tempData['data'])) {
            if ($row === null) {
                $row = $this->tempData['data'];
            } else {
                $row = $this->transformDataToArray($row, 'insert');
                $row = array_merge($this->tempData['data'], $row);
            }
        }

        $this->escape   = $this->tempData['escape'] ?? [];
        $this->tempData = [];

        return parent::insert($row, $returnID);
    }

    protected function doProtectFieldsForInsert(array $row): array
    {
        if (! $this->protectFields) {
            return $row;
        }

        if ($this->allowedFields === []) {
            throw DataException::forInvalidAllowedFields(static::class);
        }

        foreach (array_keys($row) as $key) {
            // Do not remove the non-auto-incrementing primary key data.
            if ($this->useAutoIncrement === false && $key === $this->primaryKey) {
                continue;
            }

            if (! in_array($key, $this->allowedFields, true)) {
                unset($row[$key]);
            }
        }

        return $row;
    }

    public function update($id = null, $row = null): bool
    {
        if (isset($this->tempData['data'])) {
            if ($row === null) {
                $row = $this->tempData['data'];
            } else {
                $row = $this->transformDataToArray($row, 'update');
                $row = array_merge($this->tempData['data'], $row);
            }
        }

        $this->escape   = $this->tempData['escape'] ?? [];
        $this->tempData = [];

        return parent::update($id, $row);
    }

    protected function objectToRawArray($object, bool $onlyChanged = true, bool $recursive = false): array
    {
        return parent::objectToRawArray($object, $onlyChanged);
    }

    /**
     * Provides/instantiates the builder/db connection and model's table/primary key names and return type.
     *
     * @return array<int|string, mixed>|BaseBuilder|bool|float|int|object|string|null
     */
    public function __get(string $name)
    {
        if (parent::__isset($name)) {
            return parent::__get($name);
        }

        return $this->builder()->{$name} ?? null;
    }

    /**
     * Checks for the existence of properties across this model, builder, and db connection.
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
     * @return $this|array<int|string, mixed>|BaseBuilder|bool|float|int|object|string|null
     */
    public function __call(string $name, array $params)
    {
        $builder = $this->builder();
        $result  = null;

        if (method_exists($this->db, $name)) {
            $result = $this->db->{$name}(...$params);
        } elseif (method_exists($builder, $name)) {
            $this->checkBuilderMethod($name);

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
     * Checks the Builder method name that should not be used in the Model.
     */
    private function checkBuilderMethod(string $name): void
    {
        if (in_array($name, $this->builderMethodsNotAvailable, true)) {
            throw ModelException::forMethodNotAvailable(static::class, $name . '()');
        }
    }
}
