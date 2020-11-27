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
use CodeIgniter\Exceptions\ModelException;
use CodeIgniter\Validation\ValidationInterface;
use Config\Database;
use ReflectionException;

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
 *
 * @property ConnectionInterface $db
 *
 * @mixin BaseBuilder
 */
class Model extends BaseModel
{
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
	 * The type of column that created_at and updated_at
	 * are expected to.
	 *
	 * Allowed: 'datetime', 'date', 'int'
	 *
	 * @var string
	 */
	protected $dateFormat = 'datetime';

	/**
	 * Query Builder object
	 *
	 * @var BaseBuilder|null
	 */
	protected $builder;

	/**
	 * Model constructor.
	 *
	 * @param ConnectionInterface|null $db         DB Connection
	 * @param ValidationInterface|null $validation Validation
	 */
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
		parent::__construct($db, $validation);

		$this->db = $db ?? Database::connect($this->DBGroup);
	}

	//--------------------------------------------------------------------
	// CRUD & FINDERS
	//--------------------------------------------------------------------

	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id. This methods works only with dbCalls
	 *
	 * @param boolean                   $singleton Single or multiple results
	 * @param array|integer|string|null $id        One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	protected function _find(bool $singleton, $id = null)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes)
		{
			$builder->where($this->table . '.' . $this->deletedField, null);
		}

		if (is_array($id))
		{
			$row = $builder->whereIn($this->table . '.' . $this->primaryKey, $id)
				->get()
				->getResult($this->tempReturnType);
		}
		elseif ($singleton)
		{
			$row = $builder->where($this->table . '.' . $this->primaryKey, $id)
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
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null   The resulting row of data, or null if no data found.
	 */
	protected function _findColumn(string $columnName)
	{
		return $this->select($columnName)->asArray()->find();
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
	protected function _findAll(int $limit = 0, int $offset = 0)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes)
		{
			$builder->where($this->table . '.' . $this->deletedField, null);
		}

		return $builder->limit($limit, $offset)
			->get()
			->getResult($this->tempReturnType);
	}

	/**
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determining the result set.
	 *
	 * @return array|object|null
	 */
	protected function _first()
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes)
		{
			$builder->where($this->table . '.' . $this->deletedField, null);
		}
		else
		{
			if ($this->useSoftDeletes && empty($builder->QBGroupBy) && $this->primaryKey)
			{
				$builder->groupBy($this->table . '.' . $this->primaryKey);
			}
		}

		// Some databases, like PostgreSQL, need order
		// information to consistently return correct results.
		if ($builder->QBGroupBy && empty($builder->QBOrderBy) && $this->primaryKey)
		{
			$builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
		}

		return $builder->limit(1, 0)->get()->getFirstRow($this->tempReturnType);
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
	protected function _save($data): bool
	{
		// When useAutoIncrement feature is disabled check
		// in the database if given record already exists
		if (! $makeUpdate = $this->useAutoIncrement)
		{
			$count = 0;

			if (is_object($data) && isset($data->{$this->primaryKey}))
			{
				$count = $this->where($this->primaryKey, $data->{$this->primaryKey})->countAllResults();
			}
			elseif (is_array($data) && ! empty($data[$this->primaryKey]))
			{
				$count = $this->where($this->primaryKey, $data[$this->primaryKey])->countAllResults();
			}

			if ($count === 1)
			{
				$makeUpdate = true;
			}
		}

		if ($makeUpdate && is_object($data) && isset($data->{$this->primaryKey}))
		{
			$response = $this->update($data->{$this->primaryKey}, $data);
		}
		elseif ($makeUpdate && is_array($data) && ! empty($data[$this->primaryKey]))
		{
			$response = $this->update($data[$this->primaryKey], $data);
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
		$properties = parent::objectToRawArray($data, $onlyChanged);

		if (method_exists($data, 'toRawArray'))
		{
			// Always grab the primary key otherwise updates will fail.
			if (! empty($properties) && ! empty($this->primaryKey) && ! in_array($this->primaryKey, $properties, true)
				&& ! empty($data->{$this->primaryKey}))
			{
				$properties[$this->primaryKey] = $data->{$this->primaryKey};
			}
		}

		return $properties;
	}

	/**
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object $data   Data
	 * @param boolean|null $escape Escape
	 *
	 * @return BaseResult|integer|string|false
	 */
	protected function _insert($data, bool $escape = null)
	{
		// Require non empty primaryKey when
		// not using auto-increment feature
		if (! $this->useAutoIncrement && empty($data[$this->primaryKey]))
		{
			throw DataException::forEmptyPrimaryKey('insert');
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $this->builder()
			->set($data, '', $escape)
			->insert();

		// If insertion succeeded then save the insert ID
		if ($result->resultID)
		{
			if (! $this->useAutoIncrement)
			{
				$this->insertID = $data[$this->primaryKey];
			}
			else
			{
				$this->insertID = $this->db->insertID(); // @phpstan-ignore-line
			}
		}

		return $result;
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
	protected function _insertBatch(
		array $set = null,
		bool $escape = null,
		int $batchSize = 100,
		bool $testing = false
	)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
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
	 * Updates a single record in $this->table. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param integer|array|string|null $id     ID
	 * @param array|object|null         $data   Data
	 * @param boolean|null              $escape Escape
	 *
	 * @return boolean
	 */
	protected function _update($id = null, $data = null, ?bool $escape = null): bool
	{
		$builder = $this->builder();

		if ($id)
		{
			$builder = $builder->whereIn($this->table . '.' . $this->primaryKey, $id);
		}

		// Must use the set() method to ensure objects get converted to arrays
		return $builder
			->set($data, '', $escape)
			->update();
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
	protected function _updateBatch(
		array $set = null,
		string $index = null,
		int $batchSize = 100,
		bool $returnSQL = false
	)
	{
		return $this->builder()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
	}

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
	protected function _delete($id = null, bool $purge = false)
	{
		$builder = $this->builder();

		if ($id)
		{
			$builder = $builder->whereIn($this->primaryKey, $id);
		}

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

			$result = $builder->update($set);
		}
		else
		{
			$result = $builder->delete();
		}

		return $result;
	}

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 *
	 * @return boolean|mixed
	 */
	protected function _purgeDeleted()
	{
		return $this->builder()
			->where($this->table . '.' . $this->deletedField . ' IS NOT NULL')
			->delete();
	}

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 *
	 * @return void
	 */
	protected function _onlyDeleted()
	{
		$this->builder()->where($this->table . '.' . $this->deletedField . ' IS NOT NULL');
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
	protected function _replace(array $data = null, bool $returnSQL = false)
	{
		return $this->builder()->testMode($returnSQL)->replace($data);
	}

	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

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
	public function chunk(int $size, Closure $userFunc)
	{
		$total  = $this->builder()->countAllResults(false);
		$offset = 0;

		while ($offset <= $total)
		{
			$builder = clone $this->builder();
			$rows    = $builder->get($size, $offset);

			if (! $rows)
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

	/**
	 * Provides a shared instance of the Query Builder.
	 *
	 * @param string $table
	 *
	 * @return BaseBuilder
	 * @throws ModelException
	 */
	public function builder(string $table = null)
	{
		// Check for an existing Builder
		if ($this->builder instanceof BaseBuilder)
		{
			// Make sure the requested table matches the builder
			if ($table && $this->builder->getTable() !== $table)
			{
				return $this->db->table($table);
			}

			return $this->builder;
		}

		// We're going to force a primary key to exist
		// so we don't have overly convoluted code,
		// and future features are likely to require them.
		if (empty($this->primaryKey))
		{
			throw ModelException::forNoPrimaryKey(static::class);
		}

		$table = empty($table) ? $this->table : $table;

		// Ensure we have a good db connection
		if (! $this->db instanceof BaseConnection)
		{
			$this->db = Database::connect($this->DBGroup);
		}

		$builder = $this->db->table($table);

		// Only consider it "shared" if the table is correct
		if ($table === $this->table)
		{
			$this->builder = $builder;
		}

		return $builder;
	}

	/**
	 * Grabs the last error(s) that occurred from the Database connection.
	 *
	 * @return array|null
	 */
	protected function _errors()
	{
		return $this->db->error();
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

		if (isset($this->builder()->$name))
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
}
