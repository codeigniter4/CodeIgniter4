<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter;

use Closure;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Exceptions\ModelException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Validation\ValidationInterface;
use Config\Services;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

/**
 * Class Model
 *
 * The BaseModel class provides a number of convenient features that
 * makes working with a databases less painful. Extending this class
 * provide means of implementing various database systems
 *
 * It will:
 *      - simplifies pagination
 *      - allow specifying the return type (array, object, etc) with each call
 *      - automatically set and update timestamps
 *      - handle soft deletes
 *      - ensure validation is run against objects when saving items
 *      - process various callbacks
 *      - allow intermingling calls to the db connection
 */
abstract class BaseModel
{
	// region Properties

	/**
	 * Pager instance.
	 * Populated after calling $this->paginate()
	 *
	 * @var Pager
	 */
	public $pager;

	/**
	 * Last insert ID
	 *
	 * @var integer|string
	 */
	protected $insertID = 0;

	/**
	 * The Database connection group that
	 * should be instantiated.
	 *
	 * @var string
	 */
	protected $DBGroup;

	/**
	 * The format that the results should be returned as.
	 * Will be overridden if the as* methods are used.
	 *
	 * @var string
	 */
	protected $returnType = 'array';

	/**
	 * If this model should use "softDeletes" and
	 * simply set a date when rows are deleted, or
	 * do hard deletes.
	 *
	 * @var boolean
	 */
	protected $useSoftDeletes = false;

	/**
	 * An array of field names that are allowed
	 * to be set by the user in inserts/updates.
	 *
	 * @var array
	 */
	protected $allowedFields = [];

	/**
	 * If true, will set created_at, and updated_at
	 * values during insert and update routines.
	 *
	 * @var boolean
	 */
	protected $useTimestamps = false;

	/**
	 * The type of column that created_at and updated_at
	 * are expected to.
	 *
	 * Allowed: 'datetime', 'date', 'int'
	 *
	 * @var string
	 */
	protected $dateFormat = 'datetime';

	/**
	 * The column used for insert timestamps
	 *
	 * @var string
	 */
	protected $createdField = 'created_at';

	/**
	 * The column used for update timestamps
	 *
	 * @var string
	 */
	protected $updatedField = 'updated_at';

	/**
	 * Used by withDeleted to override the
	 * model's softDelete setting.
	 *
	 * @var boolean
	 */
	protected $tempUseSoftDeletes;

	/**
	 * The column used to save soft delete state
	 *
	 * @var string
	 */
	protected $deletedField = 'deleted_at';

	/**
	 * Used by asArray and asObject to provide
	 * temporary overrides of model default.
	 *
	 * @var string
	 */
	protected $tempReturnType;

	/**
	 * Whether we should limit fields in inserts
	 * and updates to those available in $allowedFields or not.
	 *
	 * @var boolean
	 */
	protected $protectFields = true;

	/**
	 * Database Connection
	 *
	 * @var object
	 */
	protected $db;

	/**
	 * Rules used to validate data in insert, update, and save methods.
	 * The array must match the format of data passed to the Validation
	 * library.
	 *
	 * @var array|string
	 */
	protected $validationRules = [];

	/**
	 * Contains any custom error messages to be
	 * used during data validation.
	 *
	 * @var array
	 */
	protected $validationMessages = [];

	/**
	 * Skip the model's validation. Used in conjunction with skipValidation()
	 * to skip data validation for any future calls.
	 *
	 * @var boolean
	 */
	protected $skipValidation = false;

	/**
	 * Whether rules should be removed that do not exist
	 * in the passed in data. Used between inserts/updates.
	 *
	 * @var boolean
	 */
	protected $cleanValidationRules = true;

	/**
	 * Our validator instance.
	 *
	 * @var ValidationInterface
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
	 * Whether to trigger the defined callbacks
	 *
	 * @var boolean
	 */
	protected $allowCallbacks = true;

	/**
	 * Used by allowCallbacks() to override the
	 * model's allowCallbacks setting.
	 *
	 * @var boolean
	 */
	protected $tempAllowCallbacks;

	/**
	 * Callbacks for beforeInsert
	 *
	 * @var array
	 */
	protected $beforeInsert = [];

	/**
	 * Callbacks for afterInsert
	 *
	 * @var array
	 */
	protected $afterInsert = [];

	/**
	 * Callbacks for beforeUpdate
	 *
	 * @var array
	 */
	protected $beforeUpdate = [];

	/**
	 * Callbacks for afterUpdate
	 *
	 * @var array
	 */
	protected $afterUpdate = [];

	/**
	 * Callbacks for beforeFind
	 *
	 * @var array
	 */
	protected $beforeFind = [];

	/**
	 * Callbacks for afterFind
	 *
	 * @var array
	 */
	protected $afterFind = [];

	/**
	 * Callbacks for beforeDelete
	 *
	 * @var array
	 */
	protected $beforeDelete = [];

	/**
	 * Callbacks for afterDelete
	 *
	 * @var array
	 */
	protected $afterDelete = [];

	// endregion

	// region Constructor

	/**
	 * BaseModel constructor.
	 *
	 * @param ValidationInterface|null $validation Validation
	 */
	public function __construct(ValidationInterface $validation = null)
	{
		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;
		$this->tempAllowCallbacks = $this->allowCallbacks;
		$this->validation         = $validation ?? Services::validation(null, false);
	}

	// endregion

	// region Abstract Methods

	/**
	 * Fetches the row of database
	 * This methods works only with dbCalls
	 *
	 * @param boolean                   $singleton Single or multiple results
	 * @param array|integer|string|null $id        One primary key or an array of primary keys
	 *
	 * @return array|object|null The resulting row of data, or null.
	 */
	abstract protected function doFind(bool $singleton, $id = null);

	/**
	 * Fetches the column of database
	 * This methods works only with dbCalls
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null The resulting row of data, or null if no data found.
	 *
	 * @throws DataException
	 */
	abstract protected function doFindColumn(string $columnName);

	/**
	 * Fetches all results, while optionally limiting them.
	 * This methods works only with dbCalls
	 *
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	abstract protected function doFindAll(int $limit = 0, int $offset = 0);

	/**
	 * Returns the first row of the result set.
	 * This methods works only with dbCalls
	 *
	 * @return array|object|null
	 */
	abstract protected function doFirst();

	/**
	 * Inserts data into the current database
	 * This methods works only with dbCalls
	 *
	 * @param array $data Data
	 *
	 * @return object|integer|string|false
	 */
	abstract protected function doInsert(array $data);

	/**
	 * Compiles batch insert and runs the queries, validating each row prior.
	 * This methods works only with dbCalls
	 *
	 * @param array|null   $set       An associative array of insert values
	 * @param boolean|null $escape    Whether to escape values and identifiers
	 * @param integer      $batchSize The size of the batch to run
	 * @param boolean      $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 */
	abstract protected function doInsertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false);

	/**
	 * Updates a single record in the database.
	 * This methods works only with dbCalls
	 *
	 * @param integer|array|string|null $id   ID
	 * @param array|null                $data Data
	 *
	 * @return boolean
	 */
	abstract protected function doUpdate($id = null, $data = null): bool;

	/**
	 * Compiles an update and runs the query
	 * This methods works only with dbCalls
	 *
	 * @param array|null  $set       An associative array of update values
	 * @param string|null $index     The where key
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 *
	 * @throws DatabaseException
	 */
	abstract protected function doUpdateBatch(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false);

	/**
	 * Deletes a single record from the database where $id matches
	 * This methods works only with dbCalls
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return object|boolean
	 *
	 * @throws DatabaseException
	 */
	abstract protected function doDelete($id = null, bool $purge = false);

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 * This methods works only with dbCalls
	 *
	 * @return boolean|mixed
	 */
	abstract protected function doPurgeDeleted();

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 * This methods works only with dbCalls
	 *
	 * @return void
	 */
	abstract protected function doOnlyDeleted();

	/**
	 * Compiles a replace and runs the query
	 * This methods works only with dbCalls
	 *
	 * @param array|null $data      Data
	 * @param boolean    $returnSQL Set to true to return Query String
	 *
	 * @return mixed
	 */
	abstract protected function doReplace(array $data = null, bool $returnSQL = false);

	/**
	 * Grabs the last error(s) that occurred from the Database connection.
	 * This methods works only with dbCalls
	 *
	 * @return array|null
	 */
	abstract protected function doErrors();

	/**
	 * Returns the id value for the data array or object
	 *
	 * @param array|object $data Data
	 *
	 * @return integer|array|string|null
	 */
	abstract protected function idValue($data);

	/**
	 * Override countAllResults to account for soft deleted accounts.
	 * This methods works only with dbCalls
	 *
	 * @param boolean $reset Reset
	 * @param boolean $test  Test
	 *
	 * @return mixed
	 */
	abstract public function countAllResults(bool $reset = true, bool $test = false);

	/**
	 * Loops over records in batches, allowing you to operate on them.
	 * This methods works only with dbCalls
	 *
	 * @param integer $size     Size
	 * @param Closure $userFunc Callback Function
	 *
	 * @return void
	 *
	 * @throws DataException
	 */
	abstract public function chunk(int $size, Closure $userFunc);

	// endregion

	// region CRUD & Finders

	/**
	 * Fetches the row of database
	 *
	 * @param array|integer|string|null $id One primary key or an array of primary keys
	 *
	 * @return array|object|null The resulting row of data, or null.
	 */
	public function find($id = null)
	{
		$singleton = is_numeric($id) || is_string($id);

		if ($this->tempAllowCallbacks)
		{
			// Call the before event and check for a return
			$eventData = $this->trigger('beforeFind', [
				'id'        => $id,
				'method'    => 'find',
				'singleton' => $singleton,
			]);

			if (! empty($eventData['returnData']))
			{
				return $eventData['data'];
			}
		}

		$eventData = [
			'id'        => $id,
			'data'      => $this->doFind($singleton, $id),
			'method'    => 'find',
			'singleton' => $singleton,
		];

		if ($this->tempAllowCallbacks)
		{
			$eventData = $this->trigger('afterFind', $eventData);
		}

		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;
		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $eventData['data'];
	}

	/**
	 * Fetches the column of database
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null The resulting row of data, or null if no data found.
	 *
	 * @throws DataException
	 */
	public function findColumn(string $columnName)
	{
		if (strpos($columnName, ',') !== false)
		{
			throw DataException::forFindColumnHaveMultipleColumns();
		}

		$resultSet = $resultSet = $this->doFindColumn($columnName);

		return $resultSet ? array_column($resultSet, $columnName) : null;
	}

	/**
	 * Fetches all results, while optionally limiting them.
	 *
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	public function findAll(int $limit = 0, int $offset = 0)
	{
		if ($this->tempAllowCallbacks)
		{
			// Call the before event and check for a return
			$eventData = $this->trigger('beforeFind', [
				'method'    => 'findAll',
				'limit'     => $limit,
				'offset'    => $offset,
				'singleton' => false,
			]);

			if (! empty($eventData['returnData']))
			{
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

		if ($this->tempAllowCallbacks)
		{
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
	 * @return array|object|null
	 */
	public function first()
	{
		if ($this->tempAllowCallbacks)
		{
			// Call the before event and check for a return
			$eventData = $this->trigger('beforeFind', [
				'method'    => 'first',
				'singleton' => true,
			]);

			if (! empty($eventData['returnData']))
			{
				return $eventData['data'];
			}
		}

		$eventData = [
			'data'      => $this->doFirst(),
			'method'    => 'first',
			'singleton' => true,
		];

		if ($this->tempAllowCallbacks)
		{
			$eventData = $this->trigger('afterFind', $eventData);
		}

		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;
		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $eventData['data'];
	}

	/**
	 * A convenience method that will attempt to determine whether the
	 * data should be inserted or updated. Will work with either
	 * an array or object. When using with custom class objects,
	 * you must ensure that the class will provide access to the class
	 * variables, even if through a magic method.
	 *
	 * @param array|object $data Data
	 *
	 * @return boolean
	 *
	 * @throws ReflectionException
	 */
	public function save($data): bool
	{
		if (empty($data))
		{
			return true;
		}

		if ($this->shouldUpdate($data))
		{
			$response = $this->update($this->idValue($data), $data);
		}
		else
		{
			$response = $this->insert($data, false);

			if ($response instanceof BaseResult)
			{
				$response = $response->resultID !== false;
			}
			elseif ($response !== false)
			{
				$response = true;
			}
		}
		return $response;
	}

	/**
	 * This method is called on save to determine if entry have to be updated
	 * If this method return false insert operation will be executed
	 *
	 * @param array|object $data Data
	 *
	 * @return boolean
	 */
	protected function shouldUpdate($data) : bool
	{
		return ! empty($this->idValue($data));
	}

	/**
	 * Returns last insert ID or 0.
	 *
	 * @return integer|string
	 */
	public function getInsertID()
	{
		return is_numeric($this->insertID) ? (int) $this->insertID : $this->insertID;
	}

	/**
	 * Inserts data into the database. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object|null $data     Data
	 * @param boolean           $returnID Whether insert ID should be returned or not.
	 *
	 * @return BaseResult|object|integer|string|false
	 *
	 * @throws ReflectionException
	 */
	public function insert($data = null, bool $returnID = true)
	{
		$this->insertID = 0;

		$data = $this->transformDataToArray($data, 'insert');

		// Validate data before saving.
		if (! $this->skipValidation && ! $this->cleanRules()->validate($data))
		{
			return false;
		}

		// Must be called first so we don't
		// strip out created_at values.
		$data = $this->doProtectFields($data);

		// doProtectFields() can further remove elements from
		// $data so we need to check for empty dataset again
		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// Set created_at and updated_at with same time
		$date = $this->setDate();

		if ($this->useTimestamps && $this->createdField && ! array_key_exists($this->createdField, $data))
		{
			$data[$this->createdField] = $date;
		}

		if ($this->useTimestamps && $this->updatedField && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = $date;
		}

		$eventData = ['data' => $data];

		if ($this->tempAllowCallbacks)
		{
			$eventData = $this->trigger('beforeInsert', $eventData);
		}

		$result = $this->doInsert($eventData['data']);

		$eventData = [
			'id'     => $this->insertID,
			'data'   => $eventData['data'],
			'result' => $result,
		];

		if ($this->tempAllowCallbacks)
		{
			// Trigger afterInsert events with the inserted data and new ID
			$this->trigger('afterInsert', $eventData);
		}

		$this->tempAllowCallbacks = $this->allowCallbacks;

		// If insertion failed, get out of here
		if (! $result)
		{
			return $result;
		}

		// otherwise return the insertID, if requested.
		return $returnID ? $this->insertID : $result;
	}

	/**
	 * Compiles batch insert runs the queries, validating each row prior.
	 *
	 * @param array|null   $set       an associative array of insert values
	 * @param boolean|null $escape    Whether to escape values and identifiers
	 * @param integer      $batchSize The size of the batch to run
	 * @param boolean      $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 *
	 * @throws ReflectionException
	 */
	public function insertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public or protected
				// properties representing the collection elements, we need to grab
				// them as an array.
				if (is_object($row) && ! $row instanceof stdClass)
				{
					$row = $this->objectToArray($row, false, true);
				}

				// If it's still a stdClass, go ahead and convert to
				// an array so doProtectFields and other model methods
				// don't have to do special checks.
				if (is_object($row))
				{
					$row = (array) $row;
				}

				// Validate every row..
				if (! $this->skipValidation && ! $this->cleanRules()->validate($row))
				{
					return false;
				}

				// Must be called first so we don't
				// strip out created_at values.
				$row = $this->doProtectFields($row);

				// Set created_at and updated_at with same time
				$date = $this->setDate();

				if ($this->useTimestamps && $this->createdField && ! array_key_exists($this->createdField, $row))
				{
					$row[$this->createdField] = $date;
				}

				if ($this->useTimestamps && $this->updatedField && ! array_key_exists($this->updatedField, $row))
				{
					$row[$this->updatedField] = $date;
				}
			}
		}

		return $this->doInsertBatch($set, $escape, $batchSize, $testing);
	}

	/**
	 * Updates a single record in the database. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param integer|array|string|null $id   ID
	 * @param array|object|null         $data Data
	 *
	 * @return boolean
	 *
	 * @throws ReflectionException
	 */
	public function update($id = null, $data = null): bool
	{
		if (is_numeric($id) || is_string($id))
		{
			$id = [$id];
		}

		$data = $this->transformDataToArray($data, 'update');

		// Validate data before saving.
		if (! $this->skipValidation && ! $this->cleanRules(true)->validate($data))
		{
			return false;
		}

		// Must be called first so we don't
		// strip out updated_at values.
		$data = $this->doProtectFields($data);

		// doProtectFields() can further remove elements from
		// $data so we need to check for empty dataset again
		if (empty($data))
		{
			throw DataException::forEmptyDataset('update');
		}

		if ($this->useTimestamps && $this->updatedField && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = $this->setDate();
		}

		$eventData = [
			'id'   => $id,
			'data' => $data,
		];

		if ($this->tempAllowCallbacks)
		{
			$eventData = $this->trigger('beforeUpdate', $eventData);
		}

		$eventData = [
			'id'     => $id,
			'data'   => $eventData['data'],
			'result' => $this->doUpdate($id, $eventData['data']),
		];

		if ($this->tempAllowCallbacks)
		{
			$this->trigger('afterUpdate', $eventData);
		}

		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $eventData['result'];
	}

	/**
	 * Compiles an update and runs the query
	 *
	 * @param array|null  $set       An associative array of update values
	 * @param string|null $index     The where key
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 *
	 * @throws DatabaseException
	 * @throws ReflectionException
	 */
	public function updateBatch(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public or protected
				// properties representing the collection elements, we need to grab
				// them as an array.
				if (is_object($row) && ! $row instanceof stdClass)
				{
					$row = $this->objectToArray($row, true, true);
				}

				// If it's still a stdClass, go ahead and convert to
				// an array so doProtectFields and other model methods
				// don't have to do special checks.
				if (is_object($row))
				{
					$row = (array) $row;
				}

				// Validate data before saving.
				if (! $this->skipValidation && ! $this->cleanRules(true)->validate($row))
				{
					return false;
				}

				// Save updateIndex for later
				$updateIndex = $row[$index] ?? null;

				// Must be called first so we don't
				// strip out updated_at values.
				$row = $this->doProtectFields($row);

				// Restore updateIndex value in case it was wiped out
				if ($updateIndex !== null)
				{
					$row[$index] = $updateIndex;
				}

				if ($this->useTimestamps && $this->updatedField && ! array_key_exists($this->updatedField, $row))
				{
					$row[$this->updatedField] = $this->setDate();
				}
			}
		}

		return $this->doUpdateBatch($set, $index, $batchSize, $returnSQL);
	}

	/**
	 * Deletes a single record from the database where $id matches
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return BaseResult|boolean
	 *
	 * @throws DatabaseException
	 */
	public function delete($id = null, bool $purge = false)
	{
		if ($id && (is_numeric($id) || is_string($id)))
		{
			$id = [$id];
		}

		$eventData = [
			'id'    => $id,
			'purge' => $purge,
		];

		if ($this->tempAllowCallbacks)
		{
			$this->trigger('beforeDelete', $eventData);
		}

		$eventData = [
			'id'     => $id,
			'data'   => null,
			'purge'  => $purge,
			'result' => $this->doDelete($id, $purge),
		];

		if ($this->tempAllowCallbacks)
		{
			$this->trigger('afterDelete', $eventData);
		}

		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $eventData['result'];
	}

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 *
	 * @return boolean|mixed
	 */
	public function purgeDeleted()
	{
		if (! $this->useSoftDeletes)
		{
			return true;
		}

		return $this->doPurgeDeleted();
	}

	/**
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the soft deletes settings. Can be used for all find* methods.
	 *
	 * @param boolean $val Value
	 *
	 * @return $this
	 */
	public function withDeleted(bool $val = true)
	{
		$this->tempUseSoftDeletes = ! $val;

		return $this;
	}

	/**
	 * Works with the find* methods to return only the rows that
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
	 * Compiles a replace and runs the query
	 *
	 * @param array|null $data      Data
	 * @param boolean    $returnSQL Set to true to return Query String
	 *
	 * @return mixed
	 */
	public function replace(array $data = null, bool $returnSQL = false)
	{
		// Validate data before saving.
		if ($data && ! $this->skipValidation && ! $this->cleanRules(true)->validate($data))
		{
			return false;
		}

		return $this->doReplace($data, $returnSQL);
	}

	/**
	 * Grabs the last error(s) that occurred. If data was validated,
	 * it will first check for errors there, otherwise will try to
	 * grab the last error from the Database connection.
	 *
	 * @param boolean $forceDB Always grab the db error, not validation
	 *
	 * @return array|null
	 */
	public function errors(bool $forceDB = false)
	{
		// Do we have validation errors?
		if (! $forceDB && ! $this->skipValidation)
		{
			$errors = $this->validation->getErrors();

			if (! empty($errors))
			{
				return $errors;
			}
		}

		$error = $this->doErrors();

		return $error['message'] ?? null;
	}

	// endregion

	// region Pager

	/**
	 * Works with Pager to get the size and offset parameters.
	 * Expects a GET variable (?page=2) that specifies the page of results
	 * to display.
	 *
	 * @param integer|null $perPage Items per page
	 * @param string       $group   Will be used by the pagination library to identify a unique pagination set.
	 * @param integer|null $page    Optional page number (useful when the page number is provided in different way)
	 * @param integer      $segment Optional URI segment number (if page number is provided by URI segment)
	 *
	 * @return array|null
	 */
	public function paginate(int $perPage = null, string $group = 'default', int $page = null, int $segment = 0)
	{
		$pager = Services::pager(null, null, false);

		if ($segment)
		{
			$pager->setSegment($segment);
		}

		$page = $page >= 1 ? $page : $pager->getCurrentPage($group);
		// Store it in the Pager library, so it can be paginated in the views.
		$this->pager = $pager->store($group, $page, $perPage, $this->countAllResults(false), $segment);
		$perPage     = $this->pager->getPerPage($group);
		$offset      = ($page - 1) * $perPage;

		return $this->findAll($perPage, $offset);
	}

	// endregion

	// region Allowed Fields

	/**
	 * It could be used when you have to change default or override current allowed fields.
	 *
	 * @param array $allowedFields Array with names of fields
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
	 * @param boolean $protect Value
	 *
	 * @return $this
	 */
	public function protect(bool $protect = true)
	{
		$this->protectFields = $protect;

		return $this;
	}

	/**
	 * Ensures that only the fields that are allowed to be updated
	 * are in the data array.
	 *
	 * Used by insert() and update() to protect against mass assignment
	 * vulnerabilities.
	 *
	 * @param array $data Data
	 *
	 * @return array
	 *
	 * @throws DataException
	 */
	protected function doProtectFields(array $data): array
	{
		if (! $this->protectFields)
		{
			return $data;
		}

		if (empty($this->allowedFields))
		{
			throw DataException::forInvalidAllowedFields(get_class($this));
		}

		foreach ($data as $key => $val)
		{
			if (! in_array($key, $this->allowedFields, true))
			{
				unset($data[$key]);
			}
		}

		return $data;
	}

	// endregion

	// region Timestamps

	/**
	 * Sets the date or current date if null value is passed
	 *
	 * @param integer|null $userData An optional PHP timestamp to be converted.
	 *
	 * @return mixed
	 *
	 * @throws ModelException
	 */
	protected function setDate(?int $userData = null)
	{
		$currentDate = $userData ?? time();
		return $this->intToDate($currentDate);
	}

	/**
	 * A utility function to allow child models to use the type of
	 * date/time format that they prefer. This is primarily used for
	 * setting created_at, updated_at and deleted_at values, but can be
	 * used by inheriting classes.
	 *
	 * The available time formats are:
	 *  - 'int'      - Stores the date as an integer timestamp
	 *  - 'datetime' - Stores the data in the SQL datetime format
	 *  - 'date'     - Stores the date (only) in the SQL date format.
	 *
	 * @param integer $value value
	 *
	 * @return integer|string
	 *
	 * @throws ModelException
	 */
	protected function intToDate(int $value)
	{
		switch ($this->dateFormat)
		{
			case 'int':
				return $value;
			case 'datetime':
				return date('Y-m-d H:i:s', $value);
			case 'date':
				return date('Y-m-d', $value);
			default:
				throw ModelException::forNoDateFormat(static::class);
		}
	}

	/**
	 * Converts Time value to string using $this->dateFormat
	 *
	 * The available time formats are:
	 *  - 'int'      - Stores the date as an integer timestamp
	 *  - 'datetime' - Stores the data in the SQL datetime format
	 *  - 'date'     - Stores the date (only) in the SQL date format.
	 *
	 * @param Time $value value
	 *
	 * @return string|integer
	 */
	protected function timeToDate(Time $value)
	{
		switch ($this->dateFormat)
		{
			case 'datetime':
				return $value->format('Y-m-d H:i:s');
			case 'date':
				return $value->format('Y-m-d');
			case 'int':
				return $value->getTimestamp();
			default:
				return (string) $value;
		}
	}

	// endregion

	// region Validation

	/**
	 * Set the value of the skipValidation flag.
	 *
	 * @param boolean $skip Value
	 *
	 * @return $this
	 */
	public function skipValidation(bool $skip = true)
	{
		$this->skipValidation = $skip;

		return $this;
	}

	/**
	 * Allows to set validation messages.
	 * It could be used when you have to change default or override current validate messages.
	 *
	 * @param array $validationMessages Value
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
	 * @param string $field         Field Name
	 * @param array  $fieldMessages Validation messages
	 *
	 * @return $this
	 */
	public function setValidationMessage(string $field, array $fieldMessages)
	{
		$this->validationMessages[$field] = $fieldMessages;

		return $this;
	}

	/**
	 * Allows to set validation rules.
	 * It could be used when you have to change default or override current validate rules.
	 *
	 * @param array $validationRules Value
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
	 * @param string       $field      Field Name
	 * @param string|array $fieldRules Validation rules
	 *
	 * @return $this
	 */
	public function setValidationRule(string $field, $fieldRules)
	{
		$this->validationRules[$field] = $fieldRules;

		return $this;
	}

	/**
	 * Should validation rules be removed before saving?
	 * Most handy when doing updates.
	 *
	 * @param boolean $choice Value
	 *
	 * @return $this
	 */
	public function cleanRules(bool $choice = false)
	{
		$this->cleanValidationRules = $choice;

		return $this;
	}

	/**
	 * Validate the data against the validation rules (or the validation group)
	 * specified in the class property, $validationRules.
	 *
	 * @param array|object $data Data
	 *
	 * @return boolean
	 */
	public function validate($data): bool
	{
		$rules = $this->getValidationRules();

		if ($this->skipValidation || empty($rules) || empty($data))
		{
			return true;
		}

		//Validation requires array, so cast away.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		$rules = $this->cleanValidationRules ? $this->cleanValidationRules($rules, $data) : $rules;

		// If no data existed that needs validation
		// our job is done here.
		if (empty($rules))
		{
			return true;
		}

		return $this->validation->setRules($rules, $this->validationMessages)->run($data, null, $this->DBGroup);
	}

	/**
	 * Returns the model's defined validation rules so that they
	 * can be used elsewhere, if needed.
	 *
	 * @param array $options Options
	 *
	 * @return array
	 */
	public function getValidationRules(array $options = []): array
	{
		$rules = $this->validationRules;

		// ValidationRules can be either a string, which is the group name,
		// or an array of rules.
		if (is_string($rules))
		{
			// @phpstan-ignore-next-line
			$rules = $this->validation->loadRuleGroup($rules);
		}

		if (isset($options['except']))
		{
			$rules = array_diff_key($rules, array_flip($options['except']));
		}
		elseif (isset($options['only']))
		{
			$rules = array_intersect_key($rules, array_flip($options['only']));
		}

		return $rules;
	}

	/**
	 * Returns the model's define validation messages so they
	 * can be used elsewhere, if needed.
	 *
	 * @return array
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
	 * @param array      $rules Array containing field name and rule
	 * @param array|null $data  Data
	 *
	 * @return array
	 */
	protected function cleanValidationRules(array $rules, array $data = null): array
	{
		if (empty($data))
		{
			return [];
		}

		foreach ($rules as $field => $rule)
		{
			if (! array_key_exists($field, $data))
			{
				unset($rules[$field]);
			}
		}

		return $rules;
	}

	// endregion

	// region Callbacks

	/**
	 * Sets $tempAllowCallbacks value so that we can temporarily override
	 * the setting. Resets after the next method that uses triggers.
	 *
	 * @param boolean $val value
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
	 * or update, an array of results, etc)
	 *
	 * If callbacks are not allowed then returns $eventData immediately.
	 *
	 * @param string $event     Event
	 * @param array  $eventData Event Data
	 *
	 * @return mixed
	 *
	 * @throws DataException
	 */
	protected function trigger(string $event, array $eventData)
	{
		// Ensure it's a valid event
		if (! isset($this->{$event}) || empty($this->{$event}))
		{
			return $eventData;
		}

		foreach ($this->{$event} as $callback)
		{
			if (! method_exists($this, $callback))
			{
				throw DataException::forInvalidMethodTriggered($callback);
			}

			$eventData = $this->{$callback}($eventData);
		}

		return $eventData;
	}

	// endregion

	// region Utility

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
	 * @param string $class Class Name
	 *
	 * @return $this
	 */
	public function asObject(string $class = 'object')
	{
		$this->tempReturnType = $class;

		return $this;
	}

	/**
	 * Takes a class an returns an array of it's public and protected
	 * properties as an array suitable for use in creates and updates.
	 * This method use objectToRawArray internally and does conversion
	 * to string on all Time instances
	 *
	 * @param string|object $data        Data
	 * @param boolean       $onlyChanged Only Changed Property
	 * @param boolean       $recursive   If true, inner entities will be casted as array as well
	 *
	 * @return array Array
	 *
	 * @throws ReflectionException
	 */
	protected function objectToArray($data, bool $onlyChanged = true, bool $recursive = false): array
	{
		$properties = $this->objectToRawArray($data, $onlyChanged, $recursive);

		// Convert any Time instances to appropriate $dateFormat
		if ($properties)
		{
			$properties = array_map(function ($value) {
				if ($value instanceof Time)
				{
					return $this->timeToDate($value);
				}
				return $value;
			}, $properties);
		}

		return $properties;
	}

	/**
	 * Takes a class an returns an array of it's public and protected
	 * properties as an array with raw values.
	 *
	 * @param string|object $data        Data
	 * @param boolean       $onlyChanged Only Changed Property
	 * @param boolean       $recursive   If true, inner entities will be casted as array as well
	 *
	 * @return array|null Array
	 *
	 * @throws ReflectionException
	 */
	protected function objectToRawArray($data, bool $onlyChanged = true, bool $recursive = false): ?array
	{
		if (method_exists($data, 'toRawArray'))
		{
			$properties = $data->toRawArray($onlyChanged, $recursive);
		}
		else
		{
			$mirror = new ReflectionClass($data);
			$props  = $mirror->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

			$properties = [];

			// Loop over each property,
			// saving the name/value in a new array we can return.
			foreach ($props as $prop)
			{
				// Must make protected values accessible.
				$prop->setAccessible(true);
				$properties[$prop->getName()] = $prop->getValue($data);
			}
		}

		return $properties;
	}

	/**
	 * Transform data to array
	 *
	 * @param array|object|null $data Data
	 * @param string            $type Type of data (insert|update)
	 *
	 * @return array
	 *
	 * @throws DataException
	 * @throws InvalidArgumentException
	 * @throws ReflectionException
	 */
	protected function transformDataToArray($data, string $type): array
	{
		if (! in_array($type, ['insert', 'update'], true))
		{
			throw new InvalidArgumentException(sprintf('Invalid type "%s" used upon transforming data to array.', $type));
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset($type);
		}

		// If $data is using a custom class with public or protected
		// properties representing the collection elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof stdClass)
		{
			$data = $this->objectToArray($data, true, true);
		}

		// If it's still a stdClass, go ahead and convert to
		// an array so doProtectFields and other model methods
		// don't have to do special checks.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		// If it's still empty here, means $data is no change or is empty object
		if (empty($data))
		{
			throw DataException::forEmptyDataset($type);
		}

		return $data;
	}

	// endregion

	// region Magic

	/**
	 * Provides the db connection and model's properties.
	 *
	 * @param string $name Name
	 *
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}

		if (isset($this->db->$name))
		{
			return $this->db->$name;
		}

		return null;
	}

	/**
	 * Checks for the existence of properties across this model, and db connection.
	 *
	 * @param string $name Name
	 *
	 * @return boolean
	 */
	public function __isset(string $name): bool
	{
		if (property_exists($this, $name))
		{
			return true;
		}

		if (isset($this->db->$name))
		{
			return true;
		}

		return false;
	}

	/**
	 * Provides direct access to method in the database connection.
	 *
	 * @param string $name   Name
	 * @param array  $params Params
	 *
	 * @return $this|null
	 */
	public function __call(string $name, array $params)
	{
		$result = null;

		if (method_exists($this->db, $name))
		{
			$result = $this->db->{$name}(...$params);
		}

		return $result;
	}

	// endregion

	// region Deprecated

	/**
	 * Replace any placeholders within the rules with the values that
	 * match the 'key' of any properties being set. For example, if
	 * we had the following $data array:
	 *
	 * [ 'id' => 13 ]
	 *
	 * and the following rule:
	 *
	 *  'required|is_unique[users,email,id,{id}]'
	 *
	 * The value of {id} would be replaced with the actual id in the form data:
	 *
	 *  'required|is_unique[users,email,id,13]'
	 *
	 * @param array $rules Validation rules
	 * @param array $data  Data
	 *
	 * @codeCoverageIgnore
	 *
	 * @deprecated use fillPlaceholders($rules, $data) from Validation instead
	 *
	 * @return array
	 */
	protected function fillPlaceholders(array $rules, array $data): array
	{
		$replacements = [];

		foreach ($data as $key => $value)
		{
			$replacements['{' . $key . '}'] = $value;
		}

		if (! empty($replacements))
		{
			foreach ($rules as &$rule)
			{
				if (is_array($rule))
				{
					foreach ($rule as &$row)
					{
						// Should only be an `errors` array
						// which doesn't take placeholders.
						if (is_array($row))
						{
							continue;
						}

						$row = strtr($row, $replacements);
					}

					continue;
				}

				$rule = strtr($rule, $replacements);
			}
		}

		return $rules;
	}

	// endregion
}
