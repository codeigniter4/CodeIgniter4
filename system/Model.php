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
 * Class Model
 *
 * The Model class extends BaseModel and provides additional
 * convenient features that makes working with a SQL database
 * table less painful.
 *
 * It will:
 *      - automatically connect to database
 *      - allow intermingling calls to the builder
 *      - removes the need to use Result object directly in most cases
 *
 * @mixin    BaseBuilder
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
	 * The table's alias
	 *
	 * @var string
	 */
	protected $tableAlias;

	/**
	 * The table's primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * Whether primary key uses auto increment.
	 *
	 * @var boolean
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

	/**
	 * Model constructor.
	 *
	 * @param ConnectionInterface|null $db         DB Connection
	 * @param ValidationInterface|null $validation Validation
	 */
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
		/**
		 * @var BaseConnection $db
		 */
		$db = $db ?? Database::connect($this->DBGroup);

		$this->db = &$db;

		parent::__construct($validation);
		if (strpos($this->table, ' ') !== false)
		{
			// if the alias is written with the AS keyword, remove it
			$this->tableAlias = preg_replace('/\s+AS\s+/i', ' ', $this->table);

			// Grab the alias
			$this->tableAlias = trim(strrchr($this->tableAlias, ' '));

			// Store the alias, if it doesn't already exist
			$this->db->addTableAlias($this->tableAlias);
		} else {
			$this->tableAlias = $this->table;
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
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id. This methods works only with dbCalls
	 * This methods works only with dbCalls
	 *
	 * @param boolean                   $singleton Single or multiple results
	 * @param array|integer|string|null $id        One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	protected function doFind(bool $singleton, $id = null)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes)
		{
			$builder->where($this->tableAlias . '.' . $this->deletedField, null);
		}

		if (is_array($id))
		{
			$row = $builder->whereIn($this->tableAlias . '.' . $this->primaryKey, $id)
				->get()
				->getResult($this->tempReturnType);
		}
		elseif ($singleton)
		{
			$row = $builder->where($this->tableAlias . '.' . $this->primaryKey, $id)
				->get()
				->getFirstRow($this->tempReturnType);
		}
		else
		{
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
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	protected function doFindAll(int $limit = 0, int $offset = 0)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes)
		{
			$builder->where($this->tableAlias . '.' . $this->deletedField, null);
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

		if ($this->tempUseSoftDeletes)
		{
			$builder->where($this->tableAlias . '.' . $this->deletedField, null);
		}
		elseif ($this->useSoftDeletes && empty($builder->QBGroupBy) && $this->primaryKey)
		{
			$builder->groupBy($this->tableAlias . '.' . $this->primaryKey);
		}

		// Some databases, like PostgreSQL, need order
		// information to consistently return correct results.
		if ($builder->QBGroupBy && empty($builder->QBOrderBy) && $this->primaryKey)
		{
			$builder->orderBy($this->tableAlias . '.' . $this->primaryKey, 'asc');
		}

		return $builder->limit(1, 0)->get()->getFirstRow($this->tempReturnType);
	}

	/**
	 * Inserts data into the current table.
	 * This methods works only with dbCalls
	 *
	 * @param array $data Data
	 *
	 * @return Query|boolean
	 */
	protected function doInsert(array $data)
	{
		$escape       = $this->escape;
		$this->escape = [];

		// Require non empty primaryKey when
		// not using auto-increment feature
		if (! $this->useAutoIncrement && empty($data[$this->primaryKey]))
		{
			throw DataException::forEmptyPrimaryKey('insert');
		}

		$builder = $this->builder();

		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
			$builder->set($key, $val, $escape[$key] ?? null);
		}

		$result = $builder->insert();

		// If insertion succeeded then save the insert ID
		if ($result)
		{
			$this->insertID = ! $this->useAutoIncrement ? $data[$this->primaryKey] : $this->db->insertID();
		}

		return $result;
	}

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 * This methods works only with dbCalls
	 *
	 * @param array|null   $set       An associative array of insert values
	 * @param boolean|null $escape    Whether to escape values and identifiers
	 * @param integer      $batchSize The size of the batch to run
	 * @param boolean      $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 */
	protected function doInsertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set))
		{
			foreach ($set as $row)
			{
				// Require non empty primaryKey when
				// not using auto-increment feature
				if (! $this->useAutoIncrement && empty($row[$this->primaryKey]))
				{
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
	 * @param integer|array|string|null $id   ID
	 * @param array|null                $data Data
	 *
	 * @return boolean
	 */
	protected function doUpdate($id = null, $data = null): bool
	{
		$escape       = $this->escape;
		$this->escape = [];

		$builder = $this->builder();

		if ($id)
		{
			$builder = $builder->whereIn($this->table . '.' . $this->primaryKey, $id);
		}

		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
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
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 *
	 * @throws DatabaseException
	 */
	protected function doUpdateBatch(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		return $this->builder()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
	}

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 * This methods works only with dbCalls
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return string|boolean
	 *
	 * @throws DatabaseException
	 */
	protected function doDelete($id = null, bool $purge = false)
	{
		$builder = $this->builder();

		if ($id)
		{
			$properties = $data->toRawArray($onlyChanged);

			// Always grab the primary key otherwise updates will fail.
			if (! empty($properties) && ! empty($primaryKey) && ! in_array($primaryKey, $properties) && ! empty($data->{$primaryKey}))
			{
				$properties[$primaryKey] = $data->{$primaryKey};
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
				$propName              = $prop->getName();
				$properties[$propName] = $prop->getValue($data);
			}
		}

		// Convert any Time instances to appropriate $dateFormat
		if ($properties)
		{
			foreach ($properties as $key => $value)
			{
				if ($value instanceof Time)
				{
					switch ($dateFormat)
					{
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
							$converted = (string)$value;
					}

					$properties[$key] = $converted;
				}
			}
		}

		return $properties;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns last insert ID or 0.
	 *
	 * @return integer
	 */
	public function getInsertID(): int
	{
		return $this->insertID;
	}

	//--------------------------------------------------------------------

	/**
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object $data
	 * @param boolean      $returnID Whether insert ID should be returned or not.
	 *
	 * @return BaseResult|integer|string|false
	 * @throws \ReflectionException
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
			$data = static::classToArray($data, $this->primaryKey, $this->dateFormat, false);
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
		if ($this->skipValidation === false)
		{
			if ($this->cleanRules()->validate($data) === false)
			{
				return false;
			}
		}

		// Must be called first so we don't
		// strip out created_at values.
		$data = $this->doProtectFields($data);

		// Set created_at and updated_at with same time
		$date = $this->setDate();

		if ($this->useTimestamps && ! empty($this->createdField) && ! array_key_exists($this->createdField, $data))
		{
			$data[$this->createdField] = $date;
		}

		if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = $date;
		}

		$eventData = $this->trigger('beforeInsert', ['data' => $data]);

		// Must use the set() method to ensure objects get converted to arrays
		$result = $this->builder()
				->set($eventData['data'], '', $escape)
				->insert();

		// If insertion succeeded then save the insert ID
		if ($result->resultID)
		{
			$this->insertID = $this->db->insertID();
		}

		// Trigger afterInsert events with the inserted data and new ID
		$this->trigger('afterInsert', ['id' => $this->insertID, 'data' => $eventData['data'], 'result' => $result]);

		// If insertion failed, get out of here
		if (! $result)
		{
			return $result;
		}

		// otherwise return the insertID, if requested.
		return $returnID ? $this->insertID : $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 *
	 * @param array   $set       An associative array of insert values
	 * @param boolean $escape    Whether to escape values and identifiers
	 * @param integer $batchSize The size of the batch to run
	 * @param boolean $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 */
	public function insertBatch(array $set = null, bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set) && $this->skipValidation === false)
		{
			foreach ($set as $row)
			{
				if ($this->cleanRules()->validate($row) === false)
				{
					return false;
				}
			}
		}

		return $this->builder()->testMode($testing)->insertBatch($set, $escape, $batchSize);
	}

	//--------------------------------------------------------------------

	/**
	 * Updates a single record in $this->table. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param integer|array|string $id
	 * @param array|object         $data
	 *
	 * @return boolean
	 * @throws \ReflectionException
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
			$data = static::classToArray($data, $this->primaryKey, $this->dateFormat);
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
		if ($this->skipValidation === false)
		{
			if ($this->cleanRules(true)->validate($data) === false)
			{
				return false;
			}
		}

		// Must be called first so we don't
		// strip out updated_at values.
		$data = $this->doProtectFields($data);

		if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = $this->setDate();
		}

		$eventData = $this->trigger('beforeUpdate', ['id' => $id, 'data' => $data]);

		$builder = $this->builder();

		if ($id)
		{
			$builder = $builder->whereIn($this->tableAlias . '.' . $this->primaryKey, $id);
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $builder
				->set($eventData['data'], '', $escape)
				->update();

		$this->trigger('afterUpdate', ['id' => $id, 'data' => $eventData['data'], 'result' => $result]);

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param array   $set       An associative array of update values
	 * @param string  $index     The where key
	 * @param integer $batchSize The size of the batch to run
	 * @param boolean $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function updateBatch(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		if (is_array($set) && $this->skipValidation === false)
		{
			foreach ($set as $row)
			{
				if ($this->cleanRules(true)->validate($row) === false)
				{
					return false;
				}
			}
		}

		return $this->builder()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return BaseResult|boolean
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function delete($id = null, bool $purge = false)
	{
		if (! empty($id) && (is_numeric($id) || is_string($id)))
		{
			$id = [$id];
		}

		$builder = $this->builder();
		if (! empty($id))
		{
			$builder = $builder->whereIn($this->primaryKey, $id);
		}

		$this->trigger('beforeDelete', ['id' => $id, 'purge' => $purge]);

		if ($this->useSoftDeletes && ! $purge)
		{
			if (empty($builder->getCompiledQBWhere()))
			{
				if (CI_DEBUG)
				{
					throw new DatabaseException(
						'Deletes are not allowed unless they contain a "where" or "like" clause.'
					);
				}

				return false; // @codeCoverageIgnore
			}

			$set[$this->deletedField] = $this->setDate();

			if ($this->useTimestamps && $this->updatedField)
			{
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
	 * @return boolean|mixed
	 */
	protected function doPurgeDeleted()
	{
		return $this->builder()
				->where($this->tableAlias . '.' . $this->deletedField . ' IS NOT NULL')
				->delete();
	}

	//--------------------------------------------------------------------

	/**
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the softdeletes settings. Can be used for all find* methods.
	 *
	 * @param boolean $val
	 *
	 * @return Model
	 */
	public function withDeleted($val = true)
	{
		$this->tempUseSoftDeletes = ! $val;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 *
	 * @return Model
	 */
	public function onlyDeleted()
	{
		$this->tempUseSoftDeletes = false;

		$this->builder()
			 ->where($this->tableAlias . '.' . $this->deletedField . ' IS NOT NULL');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Replace
	 *
	 * Compiles an replace into string and runs the query
	 *
	 * @param null    $data
	 * @param boolean $returnSQL
	 *
	 * @return mixed
	 */
	public function replace($data = null, bool $returnSQL = false)
	{
		// Validate data before saving.
		if (! empty($data) && $this->skipValidation === false)
		{
			if ($this->cleanRules(true)->validate($data) === false)
			{
				return false;
			}
		}

		return $this->builder()->replace($data, $returnSQL);
	}

	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

	/**
	 * Sets the return type of the results to be as an associative array.
	 *
	 * @return Model
	 */
	public function asArray()
	{
		$this->tempReturnType = 'array';

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the return type to be of the specified type of object.
	 * Defaults to a simple object, but can be any class that has
	 * class vars with the same name as the table columns, or at least
	 * allows them to be created.
	 *
	 * @param string $class
	 *
	 * @return Model
	 */
	public function asObject(string $class = 'object')
	{
		$this->tempReturnType = $class;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Loops over records in batches, allowing you to operate on them.
	 * Works with $this->builder to get the Compiled select to
	 * determine the rows to operate on.
	 *
	 * @param integer  $size
	 * @param \Closure $userFunc
	 *
	 * @throws \CodeIgniter\Database\Exceptions\DataException
	 */
	public function chunk(int $size, Closure $userFunc)
	{
		$total = $this->builder()
				->countAllResults(false);

		$offset = 0;

		while ($offset <= $total)
		{
			$builder = clone($this->builder());

			$rows = $builder->get($size, $offset);

			if ($rows === false)
			{
				throw DataException::forEmptyDataset('chunk');
			}

			$rows = $rows->getResult($this->tempReturnType);

			$offset += $size;

			if (empty($rows))
			{
				continue;
			}

			foreach ($rows as $row)
			{
				if ($userFunc($row) === false)
				{
					return;
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Works with $this->builder to get the Compiled Select to operate on.
	 * Expects a GET variable (?page=2) that specifies the page of results
	 * to display.
	 *
	 * @param integer $perPage
	 * @param string  $group   Will be used by the pagination library
	 *                         to identify a unique pagination set.
	 * @param integer $page    Optional page number (useful when the page number is provided in different way)
	 * @param integer $segment Optional URI segment number (if page number is provided by URI segment)
	 *
	 * @return array|null
	 */
	public function paginate(int $perPage = null, string $group = 'default', int $page = null, int $segment = 0)
	{
		$pager = \Config\Services::pager(null, null, false);

		if ($segment)
		{
			$pager->setSegment($segment);
		}

		$page = $page >= 1 ? $page : $pager->getCurrentPage($group);

		$total = $this->countAllResults(false);

		// Store it in the Pager library so it can be
		// paginated in the views.
		$this->pager = $pager->store($group, $page, $perPage, $total, $segment);
		$perPage     = $this->pager->getPerPage($group);
		$offset      = ($page - 1) * $perPage;

		return $this->findAll($perPage, $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Sets whether or not we should whitelist data set during
	 * updates or inserts against $this->availableFields.
	 *
	 * @param boolean $protect
	 *
	 * @return Model
	 */
	public function protect(bool $protect = true)
	{
		$this->protectFields = $protect;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a shared instance of the Query Builder.
	 *
	 * @param string $table
	 *
	 * @return BaseBuilder
	 * @throws \CodeIgniter\Exceptions\ModelException;
	 */
	protected function builder(string $table = null)
	{
		if ($this->builder instanceof BaseBuilder)
		{
			return $this->builder;
		}

		// We're going to force a primary key to exist
		// so we don't have overly convoluted code,
		// and future features are likely to require them.
		if (empty($this->primaryKey))
		{
			throw ModelException::forNoPrimaryKey(get_class($this));
		}

		$table = empty($table) ? $this->table : $table;

		// Ensure we have a good db connection
		if (! $this->db instanceof BaseConnection)
		{
			$this->db = Database::connect($this->DBGroup);
		}

		$this->builder = $this->db->table($table);

		return $this->builder;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that only the fields that are allowed to be updated
	 * are in the data array.
	 *
	 * Used by insert() and update() to protect against mass assignment
	 * vulnerabilities.
	 *
	 * @param array $data
	 *
	 * @return array
	 * @throws \CodeIgniter\Database\Exceptions\DataException
	 */
	protected function doProtectFields(array $data): array
	{
		if ($this->protectFields === false)
		{
			return $data;
		}

		if (empty($this->allowedFields))
		{
			throw DataException::forInvalidAllowedFields(get_class($this));
		}

		if (is_array($data) && count($data))
		{
			foreach ($data as $key => $val)
			{
				if (! in_array($key, $this->allowedFields))
				{
					unset($data[$key]);
				}
			}
		}

		return $data;
	}

	//--------------------------------------------------------------------

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
	 * @param integer $userData An optional PHP timestamp to be converted.
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Exceptions\ModelException;
	 */
	protected function setDate(int $userData = null)
	{
		$currentDate = is_numeric($userData) ? (int) $userData : time();

		switch ($this->dateFormat)
		{
			case 'int':
				return $currentDate;
			case 'datetime':
				return date('Y-m-d H:i:s', $currentDate);
			case 'date':
				return date('Y-m-d', $currentDate);
			default:
				throw ModelException::forNoDateFormat(get_class($this));
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 * This methods works only with dbCalls
	 *
	 * @return void
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
	 * @param boolean    $returnSQL Set to true to return Query String
	 *
	 * @return mixed
	 */
	protected function doReplace(array $data = null, bool $returnSQL = false)
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

		if ((int) $error['code'] === 0)
		{
			return [];
		}

		return [get_class($this->db) => $error['message']];
	}

	/**
	 * Returns the id value for the data array or object
	 *
	 * @param array|object $data Data
	 *
	 * @return integer|array|string|null
	 */
	protected function idValue($data)
	{
		if (is_object($data) && isset($data->{$this->primaryKey}))
		{
			return $data->{$this->primaryKey};
		}

		if (is_array($data) && ! empty($data[$this->primaryKey]))
		{
			return $data[$this->primaryKey];
		}

		return null;
	}

	/**
	 * Override countAllResults to account for soft deleted accounts.
	 *
	 * @param boolean $reset Reset
	 * @param boolean $test  Test
	 *
	 * @return mixed
	 */
	public function countAllResults(bool $reset = true, bool $test = false)
	{
		if ($this->tempUseSoftDeletes)
		{
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
	 * Captures the builder's set() method so that we can validate the
	 * data here. This allows it to be used with any of the other
	 * builder methods and still get validated data, like replace.
	 *
	 * @param mixed        $key    Field name, or an array of field/value pairs
	 * @param string|null  $value  Field value, if $key is a single field
	 * @param boolean|null $escape Whether to escape values and identifiers
	 *
	 * @return $this
	 */
	public function set($key, ?string $value = '', ?bool $escape = null)
	{
		$data = is_array($key) ? $key : [$key => $value];

		foreach (array_keys($data) as $k)
		{
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
	 *
	 * @return boolean
	 */
	protected function shouldUpdate($data) : bool
	{
		// When useAutoIncrement feature is disabled check
		// in the database if given record already exists
		return parent::shouldUpdate($data) &&
			($this->useAutoIncrement
				? true
				: $this->where($this->primaryKey, $this->idValue($data))->countAllResults() === 1
			);
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
		$properties = parent::objectToRawArray($data, $onlyChanged);

		// Always grab the primary key otherwise updates will fail.
		if (method_exists($data, 'toRawArray') && (! empty($properties) && ! empty($this->primaryKey) && ! in_array($this->primaryKey, $properties, true)
				&& ! empty($data->{$this->primaryKey})))
		{
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
		if (parent::__isset($name))
		{
			return parent::__get($name);
		}

		if (isset($this->builder()->$name))
		{
			return $this->builder()->$name;
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
		if (parent::__isset($name))
		{
			return true;
		}
		return isset($this->builder()->$name);
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
		$result = parent::__call($name, $params);

		if ($result === null && method_exists($builder = $this->builder(), $name))
		{
			$result = $builder->{$name}(...$params);
		}

		if (empty($result))
		{
			if (! method_exists($this->builder(), $name))
			{
				$className = static::class;

				throw new BadMethodCallException('Call to undefined method ' . $className . '::' . $name);
			}

			return $result;
		}

		if ($result instanceof BaseBuilder)
		{
			return $this;
		}

		return $result;
	}

	/**
	 * Takes a class an returns an array of it's public and protected
	 * properties as an array suitable for use in creates and updates.
	 *
	 * @param string|object $data        Data
	 * @param string|null   $primaryKey  Primary Key
	 * @param string        $dateFormat  Date Format
	 * @param boolean       $onlyChanged Only Changed
	 *
	 * @return array
	 *
	 * @throws ReflectionException
	 *
	 * @codeCoverageIgnore
	 *
	 * @deprecated since 4.1
	 */
	public static function classToArray($data, $primaryKey = null, string $dateFormat = 'datetime', bool $onlyChanged = true): array
	{
		if (method_exists($data, 'toRawArray'))
		{
			$properties = $data->toRawArray($onlyChanged);

			// Always grab the primary key otherwise updates will fail.
			if (! empty($properties) && ! empty($primaryKey) && ! in_array($primaryKey, $properties, true) && ! empty($data->{$primaryKey}))
			{
				$properties[$primaryKey] = $data->{$primaryKey};
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

		// Convert any Time instances to appropriate $dateFormat
		if ($properties)
		{
			foreach ($properties as $key => $value)
			{
				if ($value instanceof Time)
				{
					switch ($dateFormat)
					{
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
