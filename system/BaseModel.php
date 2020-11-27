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
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

/**
 * Class Model
 *
 * The Model class provides a number of convenient features that
 * makes working with a database table less painful.
 *
 * It will:
 *      - automatically connect to database
 *      - allow intermingling calls between db connection, the builder,
 *          and methods in this class.
 *      - simplifies pagination
 *      - removes the need to use Result object directly in most cases
 *      - allow specifying the return type (array, object, etc) with each call
 *      - ensure validation is run against objects when saving items
 */
abstract class BaseModel
{
	/**
	 * Pager instance.
	 * Populated after calling $this->paginate()
	 *
	 * @var Pager
	 */
	public $pager;

	/**
	 * Name of database table
	 *
	 * @var string
	 */
	protected $table;

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
	 *
	 * @todo check if ConnectionInterface can be used for NO-SQL
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

	/**
	 * Holds information passed in via 'set'
	 * so that we can capture it (not the builder)
	 * and ensure it gets validated first.
	 *
	 * @var array
	 */
	protected $tempData = [];

	/**
	 * Model constructor.
	 *
	 * @param object|null              $db         DB Connection
	 * @param ValidationInterface|null $validation Validation
	 *
	 * @phpstan-ignore-next-line
	 */
	public function __construct(object &$db = null, ValidationInterface $validation = null)
	{
		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;
		$this->tempAllowCallbacks = $this->allowCallbacks;
		$this->validation         = $validation ?? Services::validation(null, false);
	}

	//--------------------------------------------------------------------
	// CRUD & FINDERS
	//--------------------------------------------------------------------

	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id.
	 *
	 * @param array|integer|string|null $id One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
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
			'data'      => $this->_find($singleton, $id),
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
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id. This methods works only with dbCalls
	 *
	 * @param boolean                   $singleton Single or multiple results
	 * @param array|integer|string|null $id        One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	protected abstract function _find(bool $singleton, $id = null);

	/**
	 * Fetches the column of database from $this->table
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null   The resulting row of data, or null if no data found.
	 * @throws DataException Data Exception.
	 */
	public function findColumn(string $columnName)
	{
		if (strpos($columnName, ',') !== false)
		{
			throw DataException::forFindColumnHaveMultipleColumns();
		}

		$resultSet = $resultSet = $this->_findColumn($columnName);

		return $resultSet ? array_column($resultSet, $columnName) : null;
	}

	/**
	 * Fetches the column of database from $this->table
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null   The resulting row of data, or null if no data found.
	 * @throws DataException Data Exception.
	 */
	protected abstract function _findColumn(string $columnName);

	/**
	 * Works with the current Query Builder instance to return
	 * all results, while optionally limiting them.
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
			'data'      => $this->_findAll($limit, $offset),
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
	 * Works with the current Query Builder instance to return
	 * all results, while optionally limiting them.
	 *
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	protected abstract function _findAll(int $limit = 0, int $offset = 0);

	/**
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determining the result set.
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
			'data'      => $this->_first(),
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
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determining the result set.
	 *
	 * @return array|object|null
	 */
	protected abstract function _first();

	/**
	 * Captures the builder's set() method so that we can validate the
	 * data here. This allows it to be used with any of the other
	 * builder methods and still get validated data, like replace.
	 *
	 * @param mixed       $key    Field name, or an array of field/value pairs
	 * @param string|null $value  Field value, if $key is a single field
	 * @param boolean     $escape Whether to escape values and identifiers
	 *
	 * @return $this
	 */
	public function set($key, ?string $value = '', bool $escape = null)
	{
		$data = is_array($key) ? $key : [$key => $value];

		$this->tempData['escape'] = $escape;
		$this->tempData['data']   = array_merge($this->tempData['data'] ?? [], $data);

		return $this;
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
	 */
	public function save($data): bool
	{
		if (empty($data))
		{
			return true;
		}

		return $this->_save($data);
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
	 */
	protected abstract function _save($data): bool;

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
	 * @throws ReflectionException ReflectionException.
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
	 * @throws ReflectionException ReflectionException.
	 */
	protected function objectToRawArray($data, bool $onlyChanged = true, bool $recursive = false): ?array
	{
		if (method_exists($data, 'toRawArray'))
		{
			$properties = $data->toRawArray($onlyChanged, $recursive);

			// Always grab the primary key otherwise updates will fail.
			if (! empty($properties) && ! empty($this->primaryKey) && ! in_array($this->primaryKey, $properties, true)
				&& ! empty($data->{$this->primaryKey}))
			{
				$properties[$this->primaryKey] = $data->{$this->primaryKey};
			}
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
	 * Returns last insert ID or 0.
	 *
	 * @return integer|string
	 */
	public function getInsertID()
	{
		return is_numeric($this->insertID) ? (int) $this->insertID : $this->insertID;
	}

	/**
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object|null $data     Data
	 * @param boolean           $returnID Whether insert ID should be returned or not.
	 *
	 * @return BaseResult|object|integer|string|false
	 * @throws ReflectionException ReflectionException.
	 */
	public function insert($data = null, bool $returnID = true)
	{
		$escape = null;

		$this->insertID = 0;

		if (empty($data))
		{
			$data           = $this->tempData['data'] ?? null;
			$escape         = $this->tempData['escape'] ?? null;
			$this->tempData = [];
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof stdClass)
		{
			$data = $this->objectToArray($data, false, true);
		}

		// If it's still a stdClass, go ahead and convert to
		// an array so doProtectFields and other model methods
		// don't have to do special checks.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// Validate data before saving.
		if (! $this->skipValidation && ! $this->cleanRules()->validate($data))
		{
			return false;
		}

		// Must be called first so we don't
		// strip out created_at values.
		$data = $this->doProtectFields($data);

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

		$result = $this->_insert($eventData['data'], $escape);

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
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object $data   Data
	 * @param boolean|null $escape Escape
	 *
	 * @return object|integer|string|false
	 */
	protected abstract function _insert($data, ?bool $escape = null);

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 *
	 * @param array|null $set       An associative array of insert values
	 * @param boolean    $escape    Whether to escape values and identifiers
	 * @param integer    $batchSize The size of the batch to run
	 * @param boolean    $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 * @throws ReflectionException ReflectionException.
	 */
	public function insertBatch(array $set = null, bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public or protected
				// properties representing the table elements, we need to grab
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

		return $this->_insertBatch($set, $escape, $batchSize, $testing);
	}

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 *
	 * @param array|null $set       An associative array of insert values
	 * @param boolean    $escape    Whether to escape values and identifiers
	 * @param integer    $batchSize The size of the batch to run
	 * @param boolean    $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 * @throws ReflectionException ReflectionException.
	 */
	protected abstract function _insertBatch(
		array $set = null,
		bool $escape = null,
		int $batchSize = 100,
		bool $testing = false
	);

	/**
	 * Updates a single record in $this->table. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param integer|array|string|null $id   ID
	 * @param array|object|null         $data Data
	 *
	 * @return boolean
	 * @throws ReflectionException ReflectionException.
	 */
	public function update($id = null, $data = null): bool
	{
		$escape = null;

		if (is_numeric($id) || is_string($id))
		{
			$id = [$id];
		}

		if (empty($data))
		{
			$data           = $this->tempData['data'] ?? null;
			$escape         = $this->tempData['escape'] ?? null;
			$this->tempData = [];
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset('update');
		}

		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
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
			throw DataException::forEmptyDataset('update');
		}

		// Validate data before saving.
		if (! $this->skipValidation && ! $this->cleanRules(true)->validate($data))
		{
			return false;
		}

		// Must be called first so we don't
		// strip out updated_at values.
		$data = $this->doProtectFields($data);

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
			'result' => $this->_update($id, $eventData['data']),
		];

		if ($this->tempAllowCallbacks)
		{
			$this->trigger('afterUpdate', $eventData);
		}

		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $eventData['result'];
	}

	/**
	 * Updates a single record in $this->table. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param integer|array|string|null $id     ID
	 * @param array|object|null         $data   Data
	 * @param boolean|null              $escape Escape
	 *
	 * @return boolean
	 */
	protected abstract function _update($id = null, $data = null, ?bool $escape = null): bool;

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param array|null  $set       An associative array of update values
	 * @param string|null $index     The where key
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 * @throws DatabaseException DatabaseException.
	 * @throws ReflectionException ReflectionException.
	 */
	public function updateBatch(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public or protected
				// properties representing the table elements, we need to grab
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

		return $this->_updateBatch($set, $index, $batchSize, $returnSQL);
	}

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param array|null  $set       An associative array of update values
	 * @param string|null $index     The where key
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 * @throws DatabaseException DatabaseException.
	 * @throws ReflectionException ReflectionException.
	 */
	protected abstract function _updateBatch(
		array $set = null,
		string $index = null,
		int $batchSize = 100,
		bool $returnSQL = false
	);

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return BaseResult|boolean
	 * @throws DatabaseException DatabaseException.
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
			'result' => $this->_delete($id, $purge),
		];

		if ($this->tempAllowCallbacks)
		{
			$this->trigger('afterDelete', $eventData);
		}

		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $eventData['result'];
	}

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return object|boolean
	 * @throws DatabaseException DatabaseException.
	 */
	protected abstract function _delete($id = null, bool $purge = false);

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

		return $this->_purgeDeleted();
	}

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 *
	 * @return boolean|mixed
	 */
	protected abstract function _purgeDeleted();

	/**
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the softdeletes settings. Can be used for all find* methods.
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
		$this->_onlyDeleted();

		return $this;
	}

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 *
	 * @return void
	 */
	protected abstract function _onlyDeleted();

	/**
	 * Replace
	 *
	 * Compiles a replace into string and runs the query
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

		return $this->_replace($data, $returnSQL);
	}

	/**
	 * Replace
	 *
	 * Compiles a replace into string and runs the query
	 *
	 * @param array|null $data      Data
	 * @param boolean    $returnSQL Set to true to return Query String
	 *
	 * @return mixed
	 */
	protected abstract function _replace(array $data = null, bool $returnSQL = false);

	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

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
	 * class vars with the same name as the table columns, or at least
	 * allows them to be created.
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
	 * Loops over records in batches, allowing you to operate on them.
	 * Works with $this->builder to get the Compiled select to
	 * determine the rows to operate on.
	 *
	 * @param integer $size     Size
	 * @param Closure $userFunc Callback Function
	 *
	 * @throws DataException DataException.
	 *
	 * @return void
	 */
	public abstract function chunk(int $size, Closure $userFunc);

	/**
	 * Works with $this->builder to get the Compiled Select to operate on.
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
		// Store it in the Pager libraryو so it can be paginated in the views.
		$this->pager = $pager->store($group, $page, $perPage, $this->countAllResults(false), $segment);
		$perPage     = $this->pager->getPerPage($group);
		$offset      = ($page - 1) * $perPage;

		return $this->findAll($perPage, $offset);
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
	 * @throws DataException DataException.
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
	 * @param integer|null $userData An optional PHP timestamp to be converted.
	 *
	 * @return mixed
	 * @throws ModelException ModelException.
	 */
	protected function setDate(?int $userData = null)
	{
		$currentDate = $userData ?? time();

		switch ($this->dateFormat)
		{
			case 'int':
				return $currentDate;
			case 'datetime':
				return date('Y-m-d H:i:s', $currentDate);
			case 'date':
				return date('Y-m-d', $currentDate);
			default:
				throw ModelException::forNoDateFormat(static::class);
		}
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

		$error = $this->_errors();

		return $error['message'] ?? null;
	}

	/**
	 * Grabs the last error(s) that occurred from the Database connection.
	 *
	 * @return array|null
	 */
	protected abstract function _errors();

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

	//--------------------------------------------------------------------
	// Validation
	//--------------------------------------------------------------------

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

		// Query Builder works with objects as well as arrays,
		// but validation requires array, so cast away.
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
			$replacements["{{$key}}"] = $value;
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
			$rules = $this->validation->loadRuleGroup($rules); // @phpstan-ignore-line
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
	 * Override countAllResults to account for soft deleted accounts.
	 *
	 * @param boolean $reset Reset
	 * @param boolean $test  Test
	 *
	 * @return mixed
	 */
	public abstract function countAllResults(bool $reset = true, bool $test = false);

	/**
	 * Sets $tempAllowCallbacks value so that we can temporarily override
	 * the setting. Resets after the next method that uses triggers.
	 *
	 * @param boolean $val
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
	 * @throws DataException DataException.
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

	//--------------------------------------------------------------------
	// Magic
	//--------------------------------------------------------------------

	/**
	 * Provides/instantiates the builder/db connection and model's table/primary key names and return type.
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
	 * Checks for the existence of properties across this model, builder, and db connection.
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
	 * Provides direct access to method in the builder (if available)
	 * and the database connection.
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
}
