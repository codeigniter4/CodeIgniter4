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
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\Query;
use CodeIgniter\Database\RawSql;
use CodeIgniter\DataCaster\Cast\CastInterface;
use CodeIgniter\DataConverter\DataConverter;
use CodeIgniter\Entity\Cast\CastInterface as EntityCastInterface;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\ModelException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Validation\ValidationInterface;
use Config\Feature;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

/**
 * The BaseModel class provides a number of convenient features that
 * makes working with a databases less painful. Extending this class
 * provide means of implementing various database systems.
 *
 * It will:
 *      - simplifies pagination
 *      - allow specifying the return type (array, object, etc) with each call
 *      - automatically set and update timestamps
 *      - handle soft deletes
 *      - ensure validation is run against objects when saving items
 *      - process various callbacks
 *      - allow intermingling calls to the db connection
 *
 * @phpstan-type row_array               array<int|string, float|int|null|object|string|bool>
 * @phpstan-type event_data_beforeinsert array{data: row_array}
 * @phpstan-type event_data_afterinsert  array{id: int|string, data: row_array, result: bool}
 * @phpstan-type event_data_beforefind   array{id?: int|string, method: string, singleton: bool, limit?: int, offset?: int}
 * @phpstan-type event_data_afterfind    array{id: int|string|null|list<int|string>, data: row_array|list<row_array>|object|null, method: string, singleton: bool}
 * @phpstan-type event_data_beforeupdate array{id: null|list<int|string>, data: row_array}
 * @phpstan-type event_data_afterupdate  array{id: null|list<int|string>, data: row_array|object, result: bool}
 * @phpstan-type event_data_beforedelete array{id: null|list<int|string>, purge: bool}
 * @phpstan-type event_data_afterdelete  array{id: null|list<int|string>, data: null, purge: bool, result: bool}
 */
abstract class BaseModel
{
    /**
     * Pager instance.
     *
     * Populated after calling `$this->paginate()`.
     *
     * @var Pager
     */
    public $pager;

    /**
     * Database Connection.
     *
     * @var BaseConnection
     */
    protected $db;

    /**
     * Last insert ID.
     *
     * @var int|string
     */
    protected $insertID = 0;

    /**
     * The Database connection group that
     * should be instantiated.
     *
     * @var non-empty-string|null
     */
    protected $DBGroup;

    /**
     * The format that the results should be returned as.
     *
     * Will be overridden if the `$this->asArray()`, `$this->asObject()` methods are used.
     *
     * @var 'array'|'object'|class-string
     */
    protected $returnType = 'array';

    /**
     * The temporary format of the result.
     *
     * Used by `$this->asArray()` and `$this->asObject()` to provide
     * temporary overrides of model default.
     *
     * @var 'array'|'object'|class-string
     */
    protected $tempReturnType;

    /**
     * Array of column names and the type of value to cast.
     *
     * @var array<string, string> Array order `['column' => 'type']`.
     */
    protected array $casts = [];

    /**
     * Custom convert handlers.
     *
     * @var array<string, class-string<CastInterface|EntityCastInterface>> Array order `['type' => 'classname']`.
     */
    protected array $castHandlers = [];

    protected ?DataConverter $converter = null;

    /**
     * Determines whether the model should protect field names during
     * mass assignment operations such as $this->insert(), $this->update().
     *
     * When set to `true`, only the fields explicitly defined in the `$allowedFields`
     * property will be allowed for mass assignment. This helps prevent
     * unintended modification of database fields and improves security
     * by avoiding mass assignment vulnerabilities.
     *
     * @var bool
     */
    protected $protectFields = true;

    /**
     * An array of field names that are allowed
     * to be set by the user in inserts/updates.
     *
     * @var list<string>
     */
    protected $allowedFields = [];

    /**
     * If true, will set created_at, and updated_at
     * values during insert and update routines.
     *
     * @var bool
     */
    protected $useTimestamps = false;

    /**
     * The type of column that created_at and updated_at
     * are expected to.
     *
     * @var 'date'|'datetime'|'int'
     */
    protected $dateFormat = 'datetime';

    /**
     * The column used for insert timestamps.
     *
     * @var string
     */
    protected $createdField = 'created_at';

    /**
     * The column used for update timestamps.
     *
     * @var string
     */
    protected $updatedField = 'updated_at';

    /**
     * If this model should use "softDeletes" and
     * simply set a date when rows are deleted, or
     * do hard deletes.
     *
     * @var bool
     */
    protected $useSoftDeletes = false;

    /**
     * Used by $this->withDeleted() to override the
     * model's "softDelete" setting.
     *
     * @var bool
     */
    protected $tempUseSoftDeletes;

    /**
     * The column used to save soft delete state.
     *
     * @var string
     */
    protected $deletedField = 'deleted_at';

    /**
     * Whether to allow inserting empty data.
     */
    protected bool $allowEmptyInserts = false;

    /**
     * Whether to update Entity's only changed data.
     */
    protected bool $updateOnlyChanged = true;

    /**
     * Rules used to validate data in insert(), update(), save(),
     * insertBatch(), and updateBatch() methods.
     *
     * The array must match the format of data passed to the `Validation`
     * library.
     *
     * @see https://codeigniter4.github.io/userguide/models/model.html#setting-validation-rules
     *
     * @var array<string, array<string, array<string, string>|string>|string>|string
     */
    protected $validationRules = [];

    /**
     * Contains any custom error messages to be
     * used during data validation.
     *
     * @var array<string, array<string, string>> The column is used as the keys.
     */
    protected $validationMessages = [];

    /**
     * Skip the model's validation.
     *
     * Used in conjunction with `$this->skipValidation()`
     * to skip data validation for any future calls.
     *
     * @var bool
     */
    protected $skipValidation = false;

    /**
     * Whether rules should be removed that do not exist
     * in the passed data. Used in updates.
     *
     * @var bool
     */
    protected $cleanValidationRules = true;

    /**
     * Our validator instance.
     *
     * @var ValidationInterface|null
     */
    protected $validation;

    /*
     * Callbacks.
     *
     * Each array should contain the method names (within the model)
     * that should be called when those events are triggered.
     *
     * "Update" and "delete" methods are passed the same items that
     * are given to their respective method.
     *
     * "Find" methods receive the ID searched for (if present), and
     * 'afterFind' additionally receives the results that were found.
     */

    /**
     * Whether to trigger the defined callbacks.
     *
     * @var bool
     */
    protected $allowCallbacks = true;

    /**
     * Used by $this->allowCallbacks() to override the
     * model's $allowCallbacks setting.
     *
     * @var bool
     */
    protected $tempAllowCallbacks;

    /**
     * Callbacks for "beforeInsert" event.
     *
     * @var list<string>
     */
    protected $beforeInsert = [];

    /**
     * Callbacks for "afterInsert" event.
     *
     * @var list<string>
     */
    protected $afterInsert = [];

    /**
     * Callbacks for "beforeUpdate" event.
     *
     * @var list<string>
     */
    protected $beforeUpdate = [];

    /**
     * Callbacks for "afterUpdate" event.
     *
     * @var list<string>
     */
    protected $afterUpdate = [];

    /**
     * Callbacks for "beforeInsertBatch" event.
     *
     * @var list<string>
     */
    protected $beforeInsertBatch = [];

    /**
     * Callbacks for "afterInsertBatch" event.
     *
     * @var list<string>
     */
    protected $afterInsertBatch = [];

    /**
     * Callbacks for "beforeUpdateBatch" event.
     *
     * @var list<string>
     */
    protected $beforeUpdateBatch = [];

    /**
     * Callbacks for "afterUpdateBatch" event.
     *
     * @var list<string>
     */
    protected $afterUpdateBatch = [];

    /**
     * Callbacks for "beforeFind" event.
     *
     * @var list<string>
     */
    protected $beforeFind = [];

    /**
     * Callbacks for "afterFind" event.
     *
     * @var list<string>
     */
    protected $afterFind = [];

    /**
     * Callbacks for "beforeDelete" event.
     *
     * @var list<string>
     */
    protected $beforeDelete = [];

    /**
     * Callbacks for "afterDelete" event.
     *
     * @var list<string>
     */
    protected $afterDelete = [];

    public function __construct(?ValidationInterface $validation = null)
    {
        $this->tempReturnType     = $this->returnType;
        $this->tempUseSoftDeletes = $this->useSoftDeletes;
        $this->tempAllowCallbacks = $this->allowCallbacks;

        $this->validation = $validation;

        $this->initialize();
        $this->createDataConverter();
    }

    /**
     * Creates DataConverter instance.
     */
    protected function createDataConverter(): void
    {
        if ($this->useCasts()) {
            $this->converter = new DataConverter(
                $this->casts,
                $this->castHandlers,
                $this->db,
            );
        }
    }

    /**
     * Are casts used?
     */
    protected function useCasts(): bool
    {
        return $this->casts !== [];
    }

    /**
     * Initializes the instance with any additional steps.
     * Optionally implemented by child classes.
     *
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * Fetches the row(s) of database with a primary key
     * matching $id.
     * This method works only with DB calls.
     *
     * @param bool                             $singleton Single or multiple results.
     * @param int|list<int|string>|string|null $id        One primary key or an array of primary keys.
     *
     * @return ($singleton is true ? object|row_array|null : list<object|row_array>) The resulting row of data or `null`.
     */
    abstract protected function doFind(bool $singleton, $id = null);

    /**
     * Fetches the column of database.
     * This method works only with DB calls.
     *
     * @return list<row_array>|null The resulting row of data or `null` if no data found.
     *
     * @throws DataException
     */
    abstract protected function doFindColumn(string $columnName);

    /**
     * Fetches all results, while optionally limiting them.
     * This method works only with DB calls.
     *
     * @return list<object|row_array>
     */
    abstract protected function doFindAll(?int $limit = null, int $offset = 0);

    /**
     * Returns the first row of the result set.
     * This method works only with DB calls.
     *
     * @return object|row_array|null
     */
    abstract protected function doFirst();

    /**
     * Inserts data into the current database.
     * This method works only with DB calls.
     *
     * @param row_array $row
     *
     * @return bool
     */
    abstract protected function doInsert(array $row);

    /**
     * Compiles batch insert and runs the queries, validating each row prior.
     * This method works only with DB calls.
     *
     * @param list<object|row_array>|null $set       An associative array of insert values.
     * @param bool|null                   $escape    Whether to escape values.
     * @param int                         $batchSize The size of the batch to run.
     * @param bool                        $testing   `true` means only number of records is returned, `false` will execute the query.
     *
     * @return false|int|list<string> Number of rows affected or `false` on failure, SQL array when test mode
     */
    abstract protected function doInsertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false);

    /**
     * Updates a single record in the database.
     * This method works only with DB calls.
     *
     * @param int|list<int|string>|string|null $id
     * @param row_array|null                   $row
     */
    abstract protected function doUpdate($id = null, $row = null): bool;

    /**
     * Compiles an update and runs the query.
     * This method works only with DB calls.
     *
     * @param list<object|row_array>|null $set       An associative array of update values.
     * @param string|null                 $index     The where key.
     * @param int                         $batchSize The size of the batch to run.
     * @param bool                        $returnSQL `true` means SQL is returned, `false` will execute the query.
     *
     * @return false|int|list<string> Number of rows affected or `false` on failure, SQL array when test mode
     *
     * @throws DatabaseException
     */
    abstract protected function doUpdateBatch(?array $set = null, ?string $index = null, int $batchSize = 100, bool $returnSQL = false);

    /**
     * Deletes a single record from the database where $id matches
     * the table's primary key.
     * This method works only with DB calls.
     *
     * @param int|list<int|string>|string|null $id    The rows primary key(s).
     * @param bool                             $purge Allows overriding the soft deletes setting.
     *
     * @return bool|string Returns a SQL string if in test mode.
     *
     * @throws DatabaseException
     */
    abstract protected function doDelete($id = null, bool $purge = false);

    /**
     * Permanently deletes all rows that have been marked as deleted
     * through soft deletes (value of column $deletedField is not null).
     * This method works only with DB calls.
     *
     * @return bool|string Returns a SQL string if in test mode.
     */
    abstract protected function doPurgeDeleted();

    /**
     * Works with the $this->find* methods to return only the rows that
     * have been deleted (value of column $deletedField is not null).
     * This method works only with DB calls.
     *
     * @return void
     */
    abstract protected function doOnlyDeleted();

    /**
     * Compiles a replace and runs the query.
     * This method works only with DB calls.
     *
     * @param row_array|null $row
     * @param bool           $returnSQL `true` means SQL is returned, `false` will execute the query.
     *
     * @return BaseResult|false|Query|string
     */
    abstract protected function doReplace(?array $row = null, bool $returnSQL = false);

    /**
     * Grabs the last error(s) that occurred from the Database connection.
     * This method works only with DB calls.
     *
     * @return array<string, string>
     */
    abstract protected function doErrors();

    /**
     * Public getter to return the ID value for the data array or object.
     * For example with SQL this will return `$data->{$this->primaryKey}`.
     *
     * @param object|row_array $row
     *
     * @return int|string|null
     */
    abstract public function getIdValue($row);

    /**
     * Override countAllResults to account for soft deleted accounts.
     * This method works only with DB calls.
     *
     * @param bool $reset When `false`, the `$tempUseSoftDeletes` will be
     *                    dependent on `$useSoftDeletes` value because we don't
     *                    want to add the same "where" condition for the second time.
     * @param bool $test  `true` returns the number of all records, `false` will execute the query.
     *
     * @return int|string Returns a SQL string if in test mode.
     */
    abstract public function countAllResults(bool $reset = true, bool $test = false);

    /**
     * Loops over records in batches, allowing you to operate on them.
     * This method works only with DB calls.
     *
     * @param Closure(array<string, string>|object): mixed $userFunc
     *
     * @return void
     *
     * @throws DataException
     */
    abstract public function chunk(int $size, Closure $userFunc);

    /**
     * Fetches the row of database.
     *
     * @param int|list<int|string>|string|null $id One primary key or an array of primary keys.
     *
     * @return ($id is int|string ? object|row_array|null :  list<object|row_array>)
     */
    public function find($id = null)
    {
        $singleton = is_numeric($id) || is_string($id);

        if ($this->tempAllowCallbacks) {
            // Call the before event and check for a return
            $eventData = $this->trigger('beforeFind', [
                'id'        => $id,
                'method'    => 'find',
                'singleton' => $singleton,
            ]);

            if (isset($eventData['returnData']) && $eventData['returnData'] === true) {
                return $eventData['data'];
            }
        }

        $eventData = [
            'id'        => $id,
            'data'      => $this->doFind($singleton, $id),
            'method'    => 'find',
            'singleton' => $singleton,
        ];

        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('afterFind', $eventData);
        }

        $this->tempReturnType     = $this->returnType;
        $this->tempUseSoftDeletes = $this->useSoftDeletes;
        $this->tempAllowCallbacks = $this->allowCallbacks;

        return $eventData['data'];
    }

    /**
     * Fetches the column of database.
     *
     * @return list<bool|float|int|list<mixed>|object|string|null>|null The resulting row of data, or `null` if no data found.
     *
     * @throws DataException
     */
    public function findColumn(string $columnName)
    {
        if (str_contains($columnName, ',')) {
            throw DataException::forFindColumnHaveMultipleColumns();
        }

        $resultSet = $this->doFindColumn($columnName);

        return $resultSet !== null ? array_column($resultSet, $columnName) : null;
    }

    /**
     * Fetches all results, while optionally limiting them.
     *
     * @return list<object|row_array>
     */
    public function findAll(?int $limit = null, int $offset = 0)
    {
        $limitZeroAsAll = config(Feature::class)->limitZeroAsAll ?? true;
        if ($limitZeroAsAll) {
            $limit ??= 0;
        }

        if ($this->tempAllowCallbacks) {
            // Call the before event and check for a return
            $eventData = $this->trigger('beforeFind', [
                'method'    => 'findAll',
                'limit'     => $limit,
                'offset'    => $offset,
                'singleton' => false,
            ]);

            if (isset($eventData['returnData']) && $eventData['returnData'] === true) {
                return $eventData['data'];
            }
        }

        $eventData = [
            'data'      => $this->doFindAll($limit, $offset),
            'limit'     => $limit,
            'offset'    => $offset,
            'method'    => 'findAll',
            'singleton' => false,
        ];

        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('afterFind', $eventData);
        }

        $this->tempReturnType     = $this->returnType;
        $this->tempUseSoftDeletes = $this->useSoftDeletes;
        $this->tempAllowCallbacks = $this->allowCallbacks;

        return $eventData['data'];
    }

    /**
     * Returns the first row of the result set.
     *
     * @return object|row_array|null
     */
    public function first()
    {
        if ($this->tempAllowCallbacks) {
            // Call the before event and check for a return
            $eventData = $this->trigger('beforeFind', [
                'method'    => 'first',
                'singleton' => true,
            ]);

            if (isset($eventData['returnData']) && $eventData['returnData'] === true) {
                return $eventData['data'];
            }
        }

        $eventData = [
            'data'      => $this->doFirst(),
            'method'    => 'first',
            'singleton' => true,
        ];

        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('afterFind', $eventData);
        }

        $this->tempReturnType     = $this->returnType;
        $this->tempUseSoftDeletes = $this->useSoftDeletes;
        $this->tempAllowCallbacks = $this->allowCallbacks;

        return $eventData['data'];
    }

    /**
     * A convenience method that will attempt to determine whether the
     * data should be inserted or updated.
     *
     * Will work with either an array or object.
     * When using with custom class objects,
     * you must ensure that the class will provide access to the class
     * variables, even if through a magic method.
     *
     * @param object|row_array $row
     *
     * @throws ReflectionException
     */
    public function save($row): bool
    {
        if ((array) $row === []) {
            return true;
        }

        if ($this->shouldUpdate($row)) {
            $response = $this->update($this->getIdValue($row), $row);
        } else {
            $response = $this->insert($row, false);

            if ($response !== false) {
                $response = true;
            }
        }

        return $response;
    }

    /**
     * This method is called on save to determine if entry have to be updated.
     * If this method returns `false` insert operation will be executed.
     *
     * @param object|row_array $row
     */
    protected function shouldUpdate($row): bool
    {
        $id = $this->getIdValue($row);

        return ! in_array($id, [null, [], ''], true);
    }

    /**
     * Returns last insert ID or 0.
     *
     * @return int|string
     */
    public function getInsertID()
    {
        return is_numeric($this->insertID) ? (int) $this->insertID : $this->insertID;
    }

    /**
     * Validates that the primary key values are valid for update/delete/insert operations.
     * Throws exception if invalid.
     *
     * @param bool $allowArray Whether to allow array of IDs (true for update/delete, false for insert)
     *
     * @phpstan-assert non-zero-int|non-empty-list<int|string>|RawSql|non-falsy-string $id
     * @throws         InvalidArgumentException
     */
    protected function validateID(mixed $id, bool $allowArray = true): void
    {
        if (is_array($id)) {
            // Check if arrays are allowed
            if (! $allowArray) {
                throw new InvalidArgumentException(
                    'Invalid primary key: only a single value is allowed, not an array.',
                );
            }

            // Check for empty array
            if ($id === []) {
                throw new InvalidArgumentException('Invalid primary key: cannot be an empty array.');
            }

            // Validate each ID in the array recursively
            foreach ($id as $key => $valueId) {
                if (is_array($valueId)) {
                    throw new InvalidArgumentException(
                        sprintf('Invalid primary key at index %s: nested arrays are not allowed.', $key),
                    );
                }

                // Recursive call for each value (single values only in recursion)
                $this->validateID($valueId, false);
            }

            return;
        }

        // Allow RawSql objects for complex scenarios
        if ($id instanceof RawSql) {
            return;
        }

        // Check for invalid single values
        if (in_array($id, [null, 0, '0', '', true, false], true)) {
            $type = is_bool($id) ? 'boolean ' . var_export($id, true) : var_export($id, true);

            throw new InvalidArgumentException(
                sprintf('Invalid primary key: %s is not allowed.', $type),
            );
        }

        // Only allow int and string at this point
        if (! is_int($id) && ! is_string($id)) {
            throw new InvalidArgumentException(
                sprintf('Invalid primary key: must be int or string, %s given.', get_debug_type($id)),
            );
        }
    }

    /**
     * Inserts data into the database. If an object is provided,
     * it will attempt to convert it to an array.
     *
     * @param object|row_array|null $row
     * @param bool                  $returnID Whether insert ID should be returned or not.
     *
     * @return ($returnID is true ? false|int|string : bool)
     *
     * @throws ReflectionException
     */
    public function insert($row = null, bool $returnID = true)
    {
        $this->insertID = 0;

        // Set $cleanValidationRules to false temporary.
        $cleanValidationRules       = $this->cleanValidationRules;
        $this->cleanValidationRules = false;

        $row = $this->transformDataToArray($row, 'insert');

        // Validate data before saving.
        if (! $this->skipValidation && ! $this->validate($row)) {
            // Restore $cleanValidationRules
            $this->cleanValidationRules = $cleanValidationRules;

            return false;
        }

        // Restore $cleanValidationRules
        $this->cleanValidationRules = $cleanValidationRules;

        // Must be called first, so we don't
        // strip out created_at values.
        $row = $this->doProtectFieldsForInsert($row);

        // doProtectFields() can further remove elements from
        // $row, so we need to check for empty dataset again
        if (! $this->allowEmptyInserts && $row === []) {
            throw DataException::forEmptyDataset('insert');
        }

        // Set created_at and updated_at with same time
        $date = $this->setDate();
        $row  = $this->setCreatedField($row, $date);
        $row  = $this->setUpdatedField($row, $date);

        $eventData = ['data' => $row];

        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('beforeInsert', $eventData);
        }

        $result = $this->doInsert($eventData['data']);

        $eventData = [
            'id'     => $this->insertID,
            'data'   => $eventData['data'],
            'result' => $result,
        ];

        if ($this->tempAllowCallbacks) {
            // Trigger afterInsert events with the inserted data and new ID
            $this->trigger('afterInsert', $eventData);
        }

        $this->tempAllowCallbacks = $this->allowCallbacks;

        // If insertion failed, get out of here
        if (! $result) {
            return $result;
        }

        // otherwise return the insertID, if requested.
        return $returnID ? $this->insertID : $result;
    }

    /**
     * Set datetime to created field.
     *
     * @param row_array  $row
     * @param int|string $date Timestamp or datetime string.
     *
     * @return row_array
     */
    protected function setCreatedField(array $row, $date): array
    {
        if ($this->useTimestamps && $this->createdField !== '' && ! array_key_exists($this->createdField, $row)) {
            $row[$this->createdField] = $date;
        }

        return $row;
    }

    /**
     * Set datetime to updated field.
     *
     * @param row_array  $row
     * @param int|string $date Timestamp or datetime string
     *
     * @return row_array
     */
    protected function setUpdatedField(array $row, $date): array
    {
        if ($this->useTimestamps && $this->updatedField !== '' && ! array_key_exists($this->updatedField, $row)) {
            $row[$this->updatedField] = $date;
        }

        return $row;
    }

    /**
     * Compiles batch insert runs the queries, validating each row prior.
     *
     * @param list<object|row_array>|null $set       An associative array of insert values.
     * @param bool|null                   $escape    Whether to escape values.
     * @param int                         $batchSize The size of the batch to run.
     * @param bool                        $testing   `true` means only number of records is returned, `false` will execute the query.
     *
     * @return false|int|list<string> Number of rows inserted or `false` on failure.
     *
     * @throws ReflectionException
     */
    public function insertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
    {
        // Set $cleanValidationRules to false temporary.
        $cleanValidationRules       = $this->cleanValidationRules;
        $this->cleanValidationRules = false;

        if (is_array($set)) {
            foreach ($set as &$row) {
                $row = $this->transformDataToArray($row, 'insert');

                // Validate every row.
                if (! $this->skipValidation && ! $this->validate($row)) {
                    // Restore $cleanValidationRules
                    $this->cleanValidationRules = $cleanValidationRules;

                    return false;
                }

                // Must be called first so we don't
                // strip out created_at values.
                $row = $this->doProtectFieldsForInsert($row);

                // Set created_at and updated_at with same time
                $date = $this->setDate();
                $row  = $this->setCreatedField($row, $date);
                $row  = $this->setUpdatedField($row, $date);
            }
        }

        // Restore $cleanValidationRules
        $this->cleanValidationRules = $cleanValidationRules;

        $eventData = ['data' => $set];

        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('beforeInsertBatch', $eventData);
        }

        $result = $this->doInsertBatch($eventData['data'], $escape, $batchSize, $testing);

        $eventData = [
            'data'   => $eventData['data'],
            'result' => $result,
        ];

        if ($this->tempAllowCallbacks) {
            // Trigger afterInsert events with the inserted data and new ID
            $this->trigger('afterInsertBatch', $eventData);
        }

        $this->tempAllowCallbacks = $this->allowCallbacks;

        return $result;
    }

    /**
     * Updates a single record in the database. If an object is provided,
     * it will attempt to convert it into an array.
     *
     * @param int|list<int|string>|RawSql|string|null $id
     * @param object|row_array|null                   $row
     *
     * @throws ReflectionException
     */
    public function update($id = null, $row = null): bool
    {
        if ($id !== null) {
            if (! is_array($id)) {
                $id = [$id];
            }

            $this->validateID($id);
        }

        $row = $this->transformDataToArray($row, 'update');

        // Validate data before saving.
        if (! $this->skipValidation && ! $this->validate($row)) {
            return false;
        }

        // Must be called first, so we don't
        // strip out updated_at values.
        $row = $this->doProtectFields($row);

        // doProtectFields() can further remove elements from
        // $row, so we need to check for empty dataset again
        if ($row === []) {
            throw DataException::forEmptyDataset('update');
        }

        $row = $this->setUpdatedField($row, $this->setDate());

        $eventData = [
            'id'   => $id,
            'data' => $row,
        ];

        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('beforeUpdate', $eventData);
        }

        $eventData = [
            'id'     => $id,
            'data'   => $eventData['data'],
            'result' => $this->doUpdate($id, $eventData['data']),
        ];

        if ($this->tempAllowCallbacks) {
            $this->trigger('afterUpdate', $eventData);
        }

        $this->tempAllowCallbacks = $this->allowCallbacks;

        return $eventData['result'];
    }

    /**
     * Compiles an update and runs the query.
     *
     * @param list<object|row_array>|null $set       An associative array of insert values.
     * @param string|null                 $index     The where key.
     * @param int                         $batchSize The size of the batch to run.
     * @param bool                        $returnSQL `true` means SQL is returned, `false` will execute the query.
     *
     * @return false|int|list<string> Number of rows affected or `false` on failure, SQL array when test mode.
     *
     * @throws DatabaseException
     * @throws ReflectionException
     */
    public function updateBatch(?array $set = null, ?string $index = null, int $batchSize = 100, bool $returnSQL = false)
    {
        if (is_array($set)) {
            foreach ($set as &$row) {
                $row = $this->transformDataToArray($row, 'update');

                // Validate data before saving.
                if (! $this->skipValidation && ! $this->validate($row)) {
                    return false;
                }

                // Save updateIndex for later
                $updateIndex = $row[$index] ?? null;

                if ($updateIndex === null) {
                    throw new InvalidArgumentException(
                        'The index ("' . $index . '") for updateBatch() is missing in the data: '
                        . json_encode($row),
                    );
                }

                // Must be called first so we don't
                // strip out updated_at values.
                $row = $this->doProtectFields($row);

                // Restore updateIndex value in case it was wiped out
                $row[$index] = $updateIndex;

                $row = $this->setUpdatedField($row, $this->setDate());
            }
        }

        $eventData = ['data' => $set];

        if ($this->tempAllowCallbacks) {
            $eventData = $this->trigger('beforeUpdateBatch', $eventData);
        }

        $result = $this->doUpdateBatch($eventData['data'], $index, $batchSize, $returnSQL);

        $eventData = [
            'data'   => $eventData['data'],
            'result' => $result,
        ];

        if ($this->tempAllowCallbacks) {
            // Trigger afterInsert events with the inserted data and new ID
            $this->trigger('afterUpdateBatch', $eventData);
        }

        $this->tempAllowCallbacks = $this->allowCallbacks;

        return $result;
    }

    /**
     * Deletes a single record from the database where $id matches.
     *
     * @param int|list<int|string>|RawSql|string|null $id    The rows primary key(s).
     * @param bool                                    $purge Allows overriding the soft deletes setting.
     *
     * @return bool|string Returns a SQL string if in test mode.
     *
     * @throws DatabaseException
     */
    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            if (! is_array($id)) {
                $id = [$id];
            }

            $this->validateID($id);
        }

        $eventData = [
            'id'    => $id,
            'purge' => $purge,
        ];

        if ($this->tempAllowCallbacks) {
            $this->trigger('beforeDelete', $eventData);
        }

        $eventData = [
            'id'     => $id,
            'data'   => null,
            'purge'  => $purge,
            'result' => $this->doDelete($id, $purge),
        ];

        if ($this->tempAllowCallbacks) {
            $this->trigger('afterDelete', $eventData);
        }

        $this->tempAllowCallbacks = $this->allowCallbacks;

        return $eventData['result'];
    }

    /**
     * Permanently deletes all rows that have been marked as deleted
     * through soft deletes (value of column $deletedField is not null).
     *
     * @return bool|string Returns a SQL string if in test mode.
     */
    public function purgeDeleted()
    {
        if (! $this->useSoftDeletes) {
            return true;
        }

        return $this->doPurgeDeleted();
    }

    /**
     * Sets $useSoftDeletes value so that we can temporarily override
     * the soft deletes settings. Can be used for all find* methods.
     *
     * @return $this
     */
    public function withDeleted(bool $val = true)
    {
        $this->tempUseSoftDeletes = ! $val;

        return $this;
    }

    /**
     * Works with the $this->find* methods to return only the rows that
     * have been deleted.
     *
     * @return $this
     */
    public function onlyDeleted()
    {
        $this->tempUseSoftDeletes = false;
        $this->doOnlyDeleted();

        return $this;
    }

    /**
     * Compiles a replace and runs the query.
     *
     * @param row_array|null $row
     * @param bool           $returnSQL `true` means SQL is returned, `false` will execute the query.
     *
     * @return BaseResult|false|Query|string
     */
    public function replace(?array $row = null, bool $returnSQL = false)
    {
        // Validate data before saving.
        if (($row !== null) && ! $this->skipValidation && ! $this->validate($row)) {
            return false;
        }

        $row = (array) $row;
        $row = $this->setCreatedField($row, $this->setDate());
        $row = $this->setUpdatedField($row, $this->setDate());

        return $this->doReplace($row, $returnSQL);
    }

    /**
     * Grabs the last error(s) that occurred.
     *
     * If data was validated, it will first check for errors there,
     *  otherwise will try to grab the last error from the Database connection.
     *
     * The return array should be in the following format:
     *  `['source' => 'message']`.
     *
     * @param bool $forceDB Always grab the DB error, not validation.
     *
     * @return array<string, string>
     */
    public function errors(bool $forceDB = false)
    {
        if ($this->validation === null) {
            return $this->doErrors();
        }

        // Do we have validation errors?
        if (! $forceDB && ! $this->skipValidation && ($errors = $this->validation->getErrors()) !== []) {
            return $errors;
        }

        return $this->doErrors();
    }

    /**
     * Works with Pager to get the size and offset parameters.
     * Expects a GET variable (?page=2) that specifies the page of results
     * to display.
     *
     * @param int|null $perPage Items per page.
     * @param string   $group   Will be used by the pagination library to identify a unique pagination set.
     * @param int|null $page    Optional page number (useful when the page number is provided in different way).
     * @param int      $segment Optional URI segment number (if page number is provided by URI segment).
     *
     * @return list<object|row_array>
     */
    public function paginate(?int $perPage = null, string $group = 'default', ?int $page = null, int $segment = 0)
    {
        // Since multiple models may use the Pager, the Pager must be shared.
        $pager = service('pager');

        if ($segment !== 0) {
            $pager->setSegment($segment, $group);
        }

        $page = $page >= 1 ? $page : $pager->getCurrentPage($group);
        // Store it in the Pager library, so it can be paginated in the views.
        $this->pager = $pager->store($group, $page, $perPage, $this->countAllResults(false), $segment);
        $perPage     = $this->pager->getPerPage($group);
        $offset      = ($pager->getCurrentPage($group) - 1) * $perPage;

        return $this->findAll($perPage, $offset);
    }

    /**
     * It could be used when you have to change default or override current allowed fields.
     *
     * @param list<string> $allowedFields Array with names of fields.
     *
     * @return $this
     */
    public function setAllowedFields(array $allowedFields)
    {
        $this->allowedFields = $allowedFields;

        return $this;
    }

    /**
     * Sets whether or not we should whitelist data set during
     * updates or inserts against $this->availableFields.
     *
     * @return $this
     */
    public function protect(bool $protect = true)
    {
        $this->protectFields = $protect;

        return $this;
    }

    /**
     * Ensures that only the fields that are allowed to be updated are
     * in the data array.
     *
     * @used-by update() to protect against mass assignment vulnerabilities.
     * @used-by updateBatch() to protect against mass assignment vulnerabilities.
     *
     * @param row_array $row
     *
     * @return row_array
     *
     * @throws DataException
     */
    protected function doProtectFields(array $row): array
    {
        if (! $this->protectFields) {
            return $row;
        }

        if ($this->allowedFields === []) {
            throw DataException::forInvalidAllowedFields(static::class);
        }

        foreach (array_keys($row) as $key) {
            if (! in_array($key, $this->allowedFields, true)) {
                unset($row[$key]);
            }
        }

        return $row;
    }

    /**
     * Ensures that only the fields that are allowed to be inserted are in
     * the data array.
     *
     * @used-by insert() to protect against mass assignment vulnerabilities.
     * @used-by insertBatch() to protect against mass assignment vulnerabilities.
     *
     * @param row_array $row
     *
     * @return row_array
     *
     * @throws DataException
     */
    protected function doProtectFieldsForInsert(array $row): array
    {
        return $this->doProtectFields($row);
    }

    /**
     * Sets the timestamp or current timestamp if null value is passed.
     *
     * @param int|null $userDate An optional PHP timestamp to be converted
     *
     * @return int|string
     *
     * @throws ModelException
     */
    protected function setDate(?int $userDate = null)
    {
        $currentDate = $userDate ?? Time::now()->getTimestamp();

        return $this->intToDate($currentDate);
    }

    /**
     * A utility function to allow child models to use the type of
     * date/time format that they prefer. This is primarily used for
     * setting created_at, updated_at and deleted_at values, but can be
     * used by inheriting classes.
     *
     * The available time formats are:
     *  - 'int'      - Stores the date as an integer timestamp.
     *  - 'datetime' - Stores the data in the SQL datetime format.
     *  - 'date'     - Stores the date (only) in the SQL date format.
     *
     * @return int|string
     *
     * @throws ModelException
     */
    protected function intToDate(int $value)
    {
        return match ($this->dateFormat) {
            'int'      => $value,
            'datetime' => date($this->db->dateFormat['datetime'], $value),
            'date'     => date($this->db->dateFormat['date'], $value),
            default    => throw ModelException::forNoDateFormat(static::class),
        };
    }

    /**
     * Converts Time value to string using $this->dateFormat.
     *
     * The available time formats are:
     *  - 'int'      - Stores the date as an integer timestamp.
     *  - 'datetime' - Stores the data in the SQL datetime format.
     *  - 'date'     - Stores the date (only) in the SQL date format.
     *
     * @return int|string
     */
    protected function timeToDate(Time $value)
    {
        return match ($this->dateFormat) {
            'datetime' => $value->format($this->db->dateFormat['datetime']),
            'date'     => $value->format($this->db->dateFormat['date']),
            'int'      => $value->getTimestamp(),
            default    => (string) $value,
        };
    }

    /**
     * Set the value of the $skipValidation flag.
     *
     * @return $this
     */
    public function skipValidation(bool $skip = true)
    {
        $this->skipValidation = $skip;

        return $this;
    }

    /**
     * Allows to set (and reset) validation messages.
     * It could be used when you have to change default or override current validate messages.
     *
     * @param array<string, array<string, string>> $validationMessages
     *
     * @return $this
     */
    public function setValidationMessages(array $validationMessages)
    {
        $this->validationMessages = $validationMessages;

        return $this;
    }

    /**
     * Allows to set field wise validation message.
     * It could be used when you have to change default or override current validate messages.
     *
     * @param array<string, string> $fieldMessages
     *
     * @return $this
     */
    public function setValidationMessage(string $field, array $fieldMessages)
    {
        $this->validationMessages[$field] = $fieldMessages;

        return $this;
    }

    /**
     * Allows to set (and reset) validation rules.
     * It could be used when you have to change default or override current validate rules.
     *
     * @param array<string, array<string, array<string, string>|string>|string> $validationRules
     *
     * @return $this
     */
    public function setValidationRules(array $validationRules)
    {
        $this->validationRules = $validationRules;

        return $this;
    }

    /**
     * Allows to set field wise validation rules.
     * It could be used when you have to change default or override current validate rules.
     *
     * @param array<string, array<string, string>|string>|string $fieldRules
     *
     * @return $this
     */
    public function setValidationRule(string $field, $fieldRules)
    {
        $rules = $this->validationRules;

        // ValidationRules can be either a string, which is the group name,
        // or an array of rules.
        if (is_string($rules)) {
            $this->ensureValidation();

            [$rules, $customErrors] = $this->validation->loadRuleGroup($rules);

            $this->validationRules = $rules;
            $this->validationMessages += $customErrors;
        }

        $this->validationRules[$field] = $fieldRules;

        return $this;
    }

    /**
     * Should validation rules be removed before saving?
     * Most handy when doing updates.
     *
     * @return $this
     */
    public function cleanRules(bool $choice = false)
    {
        $this->cleanValidationRules = $choice;

        return $this;
    }

    /**
     * Validate the row data against the validation rules (or the validation group)
     * specified in the class property, $validationRules.
     *
     * @param object|row_array $row
     */
    public function validate($row): bool
    {
        if ($this->skipValidation) {
            return true;
        }

        $rules = $this->getValidationRules();

        if ($rules === []) {
            return true;
        }

        // Validation requires array, so cast away.
        if (is_object($row)) {
            $row = (array) $row;
        }

        if ($row === []) {
            return true;
        }

        $rules = $this->cleanValidationRules ? $this->cleanValidationRules($rules, $row) : $rules;

        // If no data existed that needs validation
        // our job is done here.
        if ($rules === []) {
            return true;
        }

        $this->ensureValidation();

        $this->validation->reset()->setRules($rules, $this->validationMessages);

        return $this->validation->run($row, null, $this->DBGroup);
    }

    /**
     * Returns the model's defined validation rules so that they
     * can be used elsewhere, if needed.
     *
     * @param array{only?: list<string>, except?: list<string>} $options Filter the list of rules
     *
     * @return array<string, array<string, array<string, string>|string>|string>
     */
    public function getValidationRules(array $options = []): array
    {
        $rules = $this->validationRules;

        // ValidationRules can be either a string, which is the group name,
        // or an array of rules.
        if (is_string($rules)) {
            $this->ensureValidation();

            [$rules, $customErrors] = $this->validation->loadRuleGroup($rules);

            $this->validationMessages += $customErrors;
        }

        if (isset($options['except'])) {
            $rules = array_diff_key($rules, array_flip($options['except']));
        } elseif (isset($options['only'])) {
            $rules = array_intersect_key($rules, array_flip($options['only']));
        }

        return $rules;
    }

    protected function ensureValidation(): void
    {
        if ($this->validation === null) {
            $this->validation = service('validation', null, false);
        }
    }

    /**
     * Returns the model's validation messages, so they
     * can be used elsewhere, if needed.
     *
     * @return array<string, array<string, string>>
     */
    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    /**
     * Removes any rules that apply to fields that have not been set
     * currently so that rules don't block updating when only updating
     * a partial row.
     *
     * @param array<string, array<string, array<string, string>|string>|string> $rules
     * @param row_array                                                         $row
     *
     * @return array<string, array<string, array<string, string>|string>|string>
     */
    protected function cleanValidationRules(array $rules, array $row): array
    {
        if ($row === []) {
            return [];
        }

        foreach (array_keys($rules) as $field) {
            if (! array_key_exists($field, $row)) {
                unset($rules[$field]);
            }
        }

        return $rules;
    }

    /**
     * Sets $tempAllowCallbacks value so that we can temporarily override
     * the setting. Resets after the next method that uses triggers.
     *
     * @return $this
     */
    public function allowCallbacks(bool $val = true)
    {
        $this->tempAllowCallbacks = $val;

        return $this;
    }

    /**
     * A simple event trigger for Model Events that allows additional
     * data manipulation within the model. Specifically intended for
     * usage by child models this can be used to format data,
     * save/load related classes, etc.
     *
     * It is the responsibility of the callback methods to return
     * the data itself.
     *
     * Each $eventData array MUST have a 'data' key with the relevant
     * data for callback methods (like an array of key/value pairs to insert
     * or update, an array of results, etc.)
     *
     * If callbacks are not allowed then returns $eventData immediately.
     *
     * @template TEventData of array<string, mixed>
     *
     * @param string     $event     Valid property of the model event: $this->before*, $this->after*, etc.
     * @param TEventData $eventData
     *
     * @return TEventData
     *
     * @throws DataException
     */
    protected function trigger(string $event, array $eventData)
    {
        // Ensure it's a valid event
        if (! isset($this->{$event}) || $this->{$event} === []) {
            return $eventData;
        }

        foreach ($this->{$event} as $callback) {
            if (! method_exists($this, $callback)) {
                throw DataException::forInvalidMethodTriggered($callback);
            }

            $eventData = $this->{$callback}($eventData);
        }

        return $eventData;
    }

    /**
     * Sets the return type of the results to be as an associative array.
     *
     * @return $this
     */
    public function asArray()
    {
        $this->tempReturnType = 'array';

        return $this;
    }

    /**
     * Sets the return type to be of the specified type of object.
     * Defaults to a simple object, but can be any class that has
     * class vars with the same name as the collection columns,
     * or at least allows them to be created.
     *
     * @param 'object'|class-string $class
     *
     * @return $this
     */
    public function asObject(string $class = 'object')
    {
        $this->tempReturnType = $class;

        return $this;
    }

    /**
     * Takes a class and returns an array of its public and protected
     * properties as an array suitable for use in creates and updates.
     * This method uses `$this->objectToRawArray()` internally and does conversion
     * to string on all Time instances.
     *
     * @param object $object
     * @param bool   $onlyChanged Returns only the changed properties.
     * @param bool   $recursive   If `true`, inner entities will be cast as array as well.
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    protected function objectToArray($object, bool $onlyChanged = true, bool $recursive = false): array
    {
        $properties = $this->objectToRawArray($object, $onlyChanged, $recursive);

        // Convert any Time instances to appropriate $dateFormat
        return $this->timeToString($properties);
    }

    /**
     * Convert any Time instances to appropriate $dateFormat.
     *
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    protected function timeToString(array $properties): array
    {
        if ($properties === []) {
            return [];
        }

        return array_map(function ($value) {
            if ($value instanceof Time) {
                return $this->timeToDate($value);
            }

            return $value;
        }, $properties);
    }

    /**
     * Takes a class and returns an array of its public and protected
     * properties as an array with raw values.
     *
     * @param object $object
     * @param bool   $onlyChanged Returns only the changed properties.
     * @param bool   $recursive   If `true`, inner entities will be cast as array as well.
     *
     * @return array<string, mixed> Array with raw values
     *
     * @throws ReflectionException
     */
    protected function objectToRawArray($object, bool $onlyChanged = true, bool $recursive = false): array
    {
        // Entity::toRawArray() returns array
        if (method_exists($object, 'toRawArray')) {
            $properties = $object->toRawArray($onlyChanged, $recursive);
        } else {
            $mirror = new ReflectionClass($object);
            $props  = $mirror->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

            $properties = [];

            // Loop over each property,
            // saving the name/value in a new array we can return
            foreach ($props as $prop) {
                $properties[$prop->getName()] = $prop->getValue($object);
            }
        }

        return $properties;
    }

    /**
     * Transform data to array.
     *
     * @param object|row_array|null $row
     *
     * @return array<int|string, mixed>
     *
     * @throws DataException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     *
     * @used-by insert()
     * @used-by insertBatch()
     * @used-by update()
     * @used-by updateBatch()
     */
    protected function transformDataToArray($row, string $type): array
    {
        if (! in_array($type, ['insert', 'update'], true)) {
            throw new InvalidArgumentException(sprintf('Invalid type "%s" used upon transforming data to array.', $type));
        }

        if (! $this->allowEmptyInserts && ($row === null || (array) $row === [])) {
            throw DataException::forEmptyDataset($type);
        }

        // If it validates with entire rules, all fields are needed.
        if ($this->skipValidation === false && $this->cleanValidationRules === false) {
            $onlyChanged = false;
        } else {
            $onlyChanged = ($type === 'update' && $this->updateOnlyChanged);
        }

        if ($this->useCasts()) {
            if (is_array($row)) {
                $row = $this->converter->toDataSource($row);
            } elseif ($row instanceof stdClass) {
                $row = (array) $row;
                $row = $this->converter->toDataSource($row);
            } elseif ($row instanceof Entity) {
                $row = $this->converter->extract($row, $onlyChanged);
            } elseif (is_object($row)) {
                $row = $this->converter->extract($row, $onlyChanged);
            }
        }
        // If $row is using a custom class with public or protected
        // properties representing the collection elements, we need to grab
        // them as an array.
        elseif (is_object($row) && ! $row instanceof stdClass) {
            $row = $this->objectToArray($row, $onlyChanged, true);
        }

        // If it's still a stdClass, go ahead and convert to
        // an array so doProtectFields and other model methods
        // don't have to do special checks.
        if (is_object($row)) {
            $row = (array) $row;
        }

        // If it's still empty here, means $row is no change or is empty object
        if (! $this->allowEmptyInserts && ($row === null || $row === [])) {
            throw DataException::forEmptyDataset($type);
        }

        // Convert any Time instances to appropriate $dateFormat
        return $this->timeToString($row);
    }

    /**
     * Provides the DB connection and model's properties.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return $this->db->{$name} ?? null;
    }

    /**
     * Checks for the existence of properties across this model, and DB connection.
     */
    public function __isset(string $name): bool
    {
        if (property_exists($this, $name)) {
            return true;
        }

        return isset($this->db->{$name});
    }

    /**
     * Provides direct access to method in the database connection.
     *
     * @param array<int|string, mixed> $params
     *
     * @return mixed
     */
    public function __call(string $name, array $params)
    {
        if (method_exists($this->db, $name)) {
            return $this->db->{$name}(...$params);
        }

        return null;
    }

    /**
     * Sets $allowEmptyInserts.
     */
    public function allowEmptyInserts(bool $value = true): self
    {
        $this->allowEmptyInserts = $value;

        return $this;
    }

    /**
     * Converts database data array to return type value.
     *
     * @param array<string, mixed>          $row        Raw data from database.
     * @param 'array'|'object'|class-string $returnType
     *
     * @return array<string, mixed>|object
     */
    protected function convertToReturnType(array $row, string $returnType): array|object
    {
        if ($returnType === 'array') {
            return $this->converter->fromDataSource($row);
        }

        if ($returnType === 'object') {
            return (object) $this->converter->fromDataSource($row);
        }

        return $this->converter->reconstruct($returnType, $row);
    }
}
