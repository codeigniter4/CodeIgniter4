<?php namespace CodeIgniter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */
use Config\Database;
use CodeIgniter\I18n\Time;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\Exceptions\DataException;

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
 * @package CodeIgniter
 * @mixin BaseBuilder
 */
class Model
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
	 * The table's primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

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
	 * simply set a flag when rows are deleted, or
	 * do hard deletes.
	 *
	 * @var bool
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
	 * @var bool
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

	//--------------------------------------------------------------------

	/**
	 * The column used for insert timestampes
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
	 * @var bool
	 */
	protected $tempUseSoftDeletes;

    /**
	 * The column used to save soft delete state
	 *
	 * @var string
	 */
	protected $deletedField = 'deleted';

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
	 * @var bool
	 */
	protected $protectFields = true;

	/**
	 * Database Connection
	 *
	 * @var ConnectionInterface
	 */
	protected $db;

	/**
	 * Query Builder object
	 *
	 * @var BaseBuilder
	 */
	protected $builder;

	/**
	 * Rules used to validate data in insert, update, and save methods.
	 * The array must match the format of data passed to the Validation
	 * library.
	 *
	 * @var array
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
	 * @var bool
	 */
	protected $skipValidation = false;

	/**
	 * Our validator instance.
	 *
	 * @var \CodeIgniter\Validation\Validation
	 */
	protected $validation;

	/**
	 * Callbacks. Each array should contain the method
	 * names (within the model) that should be called
	 * when those events are triggered. With the exception
	 * of 'afterFind', all methods are passed the same
	 * items that are given to the update/insert method.
	 * 'afterFind' will also include the results that were found.
	 *
	 * @var array
	 */
	protected $beforeInsert = [];
	protected $afterInsert = [];
	protected $beforeUpdate = [];
	protected $afterUpdate = [];
	protected $afterFind = [];
	protected $beforeDelete = [];
	protected $afterDelete = [];

	/**
	 * Holds information passed in via 'set'
	 * so that we can capture it (not the builder)
	 * and ensure it gets validated first.
	 *
	 * @var array
	 */
	protected $tempData = [];

	//--------------------------------------------------------------------

	/**
	 * Model constructor.
	 *
	 * @param ConnectionInterface $db
	 * @param ValidationInterface $validation
	 */
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
		if ($db instanceof ConnectionInterface)
		{
			$this->db = & $db;
		}
		else
		{
			$this->db = Database::connect($this->DBGroup);
		}

		$this->tempReturnType = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		if (is_null($validation))
		{
			$validation = \Config\Services::validation(null, false);
		}

		$this->validation = $validation;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// CRUD & FINDERS
	//--------------------------------------------------------------------

	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id.
	 *
	 * @param mixed|array|null   $id One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	public function find($id = null)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where($this->deletedField, 0);
		}

		if (is_array($id))
		{
			$row = $builder->whereIn($this->table.'.'.$this->primaryKey, $id)
					->get();
			$row = $row->getResult($this->tempReturnType);
		}
		elseif (is_numeric($id) || is_string($id))
		{
			$row = $builder->where($this->table.'.'.$this->primaryKey, $id)
					->get();

			$row = $row->getFirstRow($this->tempReturnType);
		}
		else
		{
			$row = $builder->get();

			$row = $row->getResult($this->tempReturnType);
		}

		$row = $this->trigger('afterFind', ['id' => $id, 'data' => $row]);

		$this->tempReturnType = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $row['data'];
	}

	//--------------------------------------------------------------------

	/**
	 * Works with the current Query Builder instance to return
	 * all results, while optionally limiting them.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array|null
	 */
	public function findAll(int $limit = 0, int $offset = 0)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where($this->deletedField, 0);
		}

		$row = $builder->limit($limit, $offset)
				->get();

		$row = $row->getResult($this->tempReturnType);

		$row = $this->trigger('afterFind', ['data' => $row, 'limit' => $limit, 'offset' => $offset]);

		$this->tempReturnType = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $row['data'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determing the result set.
	 *
	 * @return array|object|null
	 */
	public function first()
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where($this->deletedField, 0);
		}

		// Some databases, like PostgreSQL, need order
		// information to consistently return correct results.
		if (empty($builder->QBOrderBy))
		{
			$builder->orderBy($this->table.'.'.$this->primaryKey, 'asc');
		}

		$row = $builder->limit(1, 0)
				->get();

		$row = $row->getFirstRow($this->tempReturnType);

		$row = $this->trigger('afterFind', ['data' => $row]);

		$this->tempReturnType = $this->returnType;

		return $row['data'];
	}

	//--------------------------------------------------------------------

	/**
	 * Captures the builder's set() method so that we can validate the
	 * data here. This allows it to be used with any of the other
	 * builder methods and still get validated data, like replace.
	 *
	 * @param           $key
	 * @param string    $value
	 * @param bool|null $escape
	 *
	 * @return $this
	 */
	public function set($key, $value = '', bool $escape = null)
	{
		$data = is_array($key)
			? $key
			: [$key => $value];

		$this->tempData['escape'] = $escape;
		$this->tempData['data'] = array_merge($this->tempData['data'] ?? [], $data);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method that will attempt to determine whether the
	 * data should be inserted or updated. Will work with either
	 * an array or object. When using with custom class objects,
	 * you must ensure that the class will provide access to the class
	 * variables, even if through a magic method.
	 *
	 * @param array|object $data
	 *
	 * @return bool
	 */
	public function save($data)
	{
		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof \stdClass)
		{
			$data = static::classToArray($data, $this->dateFormat);
		}

		if (is_object($data) && isset($data->{$this->primaryKey}))
		{
			$response = $this->update($data->{$this->primaryKey}, $data);
		}
		elseif (is_array($data) && ! empty($data[$this->primaryKey]))
		{
			$response = $this->update($data[$this->primaryKey], $data);
		}
		else
		{
			$response = $this->insert($data);
		}

		return $response;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a class an returns an array of it's public and protected
	 * properties as an array suitable for use in creates and updates.
	 *
	 * @param string|object $data
	 * @param string        $dateFormat
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function classToArray($data, string $dateFormat = 'datetime'): array
	{
		if (method_exists($data, 'toArray'))
		{
			$properties = $data->toArray(true);
		}
		else
		{
			$mirror = new \ReflectionClass($data);
			$props  = $mirror->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

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
		if (count($properties))
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
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object $data
	 * @param bool         $returnID Whether insert ID should be returned or not.
	 *
	 * @return int|string|bool
	 */
	public function insert($data = null, bool $returnID = true)
	{
		$escape = null;

		if (empty($data))
		{
			$data   = $this->tempData['data'] ?? null;
			$escape = $this->tempData['escape'] ?? null;
			$this->tempData = [];
		}

		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof \stdClass)
		{
			$data = static::classToArray($data, $this->dateFormat);
		}

		// If it's still a stdClass, go ahead and convert to
		// an array so doProtectFields and other model methods
		// don't have to do special checks.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		// Validate data before saving.
		if ($this->skipValidation === false)
		{
			if ($this->validate($data) === false)
			{
				return false;
			}
		}

		// Save the original data so it can be passed to
		// any Model Event callbacks and not stripped
		// by doProtectFields
		$originalData = $data;

		// Must be called first so we don't
		// strip out created_at values.
		$data = $this->doProtectFields($data);

		if ($this->useTimestamps && ! array_key_exists($this->createdField, $data))
		{
			$date = $this->setDate();
			$data[$this->createdField] = $date;
			$data[$this->updatedField] = $date;
		}

		$data = $this->trigger('beforeInsert', ['data' => $data]);

		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $this->builder()
				->set($data['data'], '', $escape)
				->insert();

		$this->trigger('afterInsert', ['data' => $originalData, 'result' => $result]);

		// If insertion failed, get our of here
		if ( ! $result)
		{
			return $result;
		}

		// otherwise return the insertID, if requested.
		return $returnID ? $this->db->insertID() : $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 *
	 * @param    array $set    An associative array of insert values
	 * @param    bool  $escape Whether to escape values and identifiers
	 *
	 * @param int      $batchSize
	 * @param bool     $testing
	 *
	 * @return int Number of rows inserted or FALSE on failure
	 */
	public function insertBatch($set = null, $escape = null, $batchSize = 100, $testing = false)
	{
		if (is_array($set) && $this->skipValidation === false)
		{
			foreach ($set as $row)
			{
				if ($this->validate($row) === false)
				{
					return false;
				}
			}
		}

		return $this->builder()->insertBatch($set, $escape, $batchSize, $testing);
	}

	//--------------------------------------------------------------------

	/**
	 * Updates a single record in $this->table. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param int|array|string   $id
	 * @param array|object $data
	 *
	 * @return bool
	 */
	public function update($id = null, $data = null)
	{
		$escape = null;

		if (is_numeric($id))
		{
			$id = [$id];
		}

		if (empty($data))
		{
			$data   = $this->tempData['data'] ?? null;
			$escape = $this->tempData['escape'] ?? null;
			$this->tempData = [];
		}

		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof \stdClass)
		{
			$data = static::classToArray($data, $this->dateFormat);
		}

		// If it's still a stdClass, go ahead and convert to
		// an array so doProtectFields and other model methods
		// don't have to do special checks.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		// Validate data before saving.
		if ($this->skipValidation === false)
		{
			if ($this->validate($data) === false)
			{
				return false;
			}
		}

		// Save the original data so it can be passed to
		// any Model Event callbacks and not stripped
		// by doProtectFields
		$originalData = $data;

		// Must be called first so we don't
		// strip out updated_at values.
		$data = $this->doProtectFields($data);

		if ($this->useTimestamps && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = $this->setDate();
		}

		$data = $this->trigger('beforeUpdate', ['id' => $id, 'data' => $data]);

		if (empty($data))
		{
			throw DataException::forEmptyDataset('update');
		}

		$builder = $this->builder();

		if ($id)
		{
			$builder = $builder->whereIn($this->table.'.'.$this->primaryKey, $id);
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $builder
				->set($data['data'], '', $escape)
				->update();

		$this->trigger('afterUpdate', ['id' => $id, 'data' => $originalData, 'result' => $result]);

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param    array  $set       An associative array of update values
	 * @param    string $index     The where key
	 * @param    int    $batchSize The size of the batch to run
	 * @param    bool   $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return    mixed    Number of rows affected or FALSE on failure
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function updateBatch($set = null, $index = null, $batchSize = 100, $returnSQL = false)
	{
		if (is_array($set) && $this->skipValidation === false)
		{
			foreach ($set as $row)
			{
				if ($this->validate($row) === false)
				{
					return false;
				}
			}
		}

		return $this->builder()->updateBatch($set, $index, $batchSize, $returnSQL);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 *
	 * @param int|array|null $id    The rows primary key(s)
	 * @param bool           $purge Allows overriding the soft deletes setting.
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function delete($id = null, $purge = false)
	{
		if (! empty($id) && is_numeric($id))
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
            $set[$this->deletedField] = 1;

            if ($this->useTimestamps)
            {
                $set[$this->updatedField] = $this->setDate();
            }

			$result = $builder->update($set);
		}
		else
		{
			$result = $builder->delete();
		}

		$this->trigger('afterDelete', ['id' => $id, 'purge' => $purge, 'result' => $result, 'data' => null]);

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 *
	 * @return bool|mixed
	 */
	public function purgeDeleted()
	{
		if ( ! $this->useSoftDeletes)
		{
			return true;
		}

		return $this->builder()
						->where($this->deletedField, 1)
						->delete();
	}

	//--------------------------------------------------------------------

	/**
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the softdeletes settings. Can be used for all find* methods.
	 *
	 * @param bool $val
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
				->where($this->deletedField, 1);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Replace
	 *
	 * Compiles an replace into string and runs the query
	 *
	 * @param null $data
	 * @param bool $returnSQL
	 *
	 * @return bool TRUE on success, FALSE on failure
	 */
	public function replace($data = null, $returnSQL = false)
	{
		// Validate data before saving.
		if (! empty($data) && $this->skipValidation === false)
		{
			if ($this->validate($data) === false)
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
	 * @param int      $size
	 * @param \Closure $userFunc
	 *
	 * @throws \CodeIgniter\Database\Exceptions\DataException
	 */
	public function chunk($size = 100, \Closure $userFunc)
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

			$rows = $rows->getResult();

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
	 * @param int    $perPage
	 * @param string $group    Will be used by the pagination library
	 *                         to identify a unique pagination set.
	 * @param int    $page     Optional page number (useful when the page number is provided in different way)
	 *
	 * @return array|null
	 */
	public function paginate(int $perPage = 20, string $group = 'default', int $page = 0)
	{
		// Get the necessary parts.
		$page = $page >= 1 ? $page : (ctype_digit($_GET['page'] ?? '') && $_GET['page'] > 1 ? $_GET['page'] : 1);

		$total = $this->countAllResults(false);

		// Store it in the Pager library so it can be
		// paginated in the views.
		$pager = \Config\Services::pager();
		$this->pager = $pager->store($group, $page, $perPage, $total);

		$offset = ($page - 1) * $perPage;

		return $this->findAll($perPage, $offset);
	}

	//--------------------------------------------------------------------

	/**
	 * Sets whether or not we should whitelist data set during
	 * updates or inserts against $this->availableFields.
	 *
	 * @param bool $protect
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
	 */
	protected function builder(string $table = null)
	{
		if ($this->builder instanceof BaseBuilder)
		{
			return $this->builder;
		}

		$table = empty($table) ? $this->table : $table;

		// Ensure we have a good db connection
		if ( ! $this->db instanceof BaseConnection)
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
	protected function doProtectFields($data)
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
	 * setting created_at and updated_at values, but can be used
	 * by inheriting classes.
	 *
	 * The available time formats are:
	 *  - 'int'      - Stores the date as an integer timestamp
	 *  - 'datetime' - Stores the data in the SQL datetime format
	 *  - 'date'     - Stores the date (only) in the SQL date format.
	 *
	 * @param int $userData An optional PHP timestamp to be converted.
	 *
	 * @return mixed
	 */
	protected function setDate($userData = null)
	{
		$currentDate = is_numeric($userData) ? (int) $userData : time();

		switch ($this->dateFormat)
		{
			case 'int':
				return $currentDate;
				break;
			case 'datetime':
				return date('Y-m-d H:i:s', $currentDate);
				break;
			case 'date':
				return date('Y-m-d', $currentDate);
				break;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Specify the table associated with a model
	 *
	 * @param string $table
	 *
	 * @return Model
	 */
	public function setTable(string $table)
	{
		$this->table = $table;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the last error(s) that occurred. If data was validated,
	 * it will first check for errors there, otherwise will try to
	 * grab the last error from the Database connection.
	 *
	 * @param bool $forceDB Always grab the db error, not validation
	 *
	 * @return array|null
	 */
	public function errors(bool $forceDB = false)
	{
		// Do we have validation errors?
		if ($forceDB === false && $this->skipValidation === false)
		{
			$errors = $this->validation->getErrors();

			if ( ! empty($errors))
			{
				return $errors;
			}
		}

		// Still here? Grab the database-specific error, if any.
		$error = $this->db->getError();

		return $error['message'] ?? null;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Validation
	//--------------------------------------------------------------------

	/**
	 * Set the value of the skipValidation flag.
	 *
	 * @param bool $skip
	 *
	 * @return Model
	 */
	public function skipValidation(bool $skip = true)
	{
		$this->skipValidation = $skip;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Validate the data against the validation rules (or the validation group)
	 * specified in the class property, $validationRules.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validate($data): bool
	{
		if ($this->skipValidation === true || empty($this->validationRules) || empty($data))
		{
			return true;
		}

		// Query Builder works with objects as well as arrays,
		// but validation requires array, so cast away.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		// ValidationRules can be either a string, which is the group name,
		// or an array of rules.
		if (is_string($this->validationRules))
		{
			$valid = $this->validation->run($data, $this->validationRules);
		}
		else
		{
			// Replace any placeholders (i.e. {id}) in the rules with
			// the value found in $data, if exists.
			$rules = $this->fillPlaceholders($this->validationRules, $data);

			$this->validation->setRules($rules, $this->validationMessages);
			$valid = $this->validation->run($data);
		}

		return (bool) $valid;
	}

	//--------------------------------------------------------------------

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
	 * @param array $rules
	 * @param array $data
	 *
	 * @return array
	 */
	protected function fillPlaceholders(array $rules, array $data)
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
						$row = strtr($row, $replacements);
					}
					continue;
				}

				$rule = strtr($rule, $replacements);
			}
		}

		return $rules;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the model's defined validation rules so that they
	 * can be used elsewhere, if needed.
	 *
	 * @return array
	 */
	public function getValidationRules(array $options=[])
	{
		$rules = $this->validationRules;

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

	//--------------------------------------------------------------------

	/**
	 * Returns the model's define validation messages so they
	 * can be used elsewhere, if needed.
	 *
	 * @return array
	 */
	public function getValidationMessages()
	{
		return $this->validationMessages;
	}

	//--------------------------------------------------------------------

	/**
	 * A simple event trigger for Model Events that allows additional
	 * data manipulation within the model. Specifically intended for
	 * usage by child models this can be used to format data,
	 * save/load related classes, etc.
	 *
	 * It is the responsibility of the callback methods to return
	 * the data itself.
	 *
	 * Each $data array MUST have a 'data' key with the relevant
	 * data for callback methods (like an array of key/value pairs to insert
	 * or update, an array of results, etc)
	 *
	 * @param string $event
	 * @param array  $data
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Database\Exceptions\DataException
	 */
	protected function trigger(string $event, array $data)
	{
		// Ensure it's a valid event
		if ( ! isset($this->{$event}) || empty($this->{$event}))
		{
			return $data;
		}

		foreach ($this->{$event} as $callback)
		{
			if ( ! method_exists($this, $callback))
			{
				throw DataException::forInvalidMethodTriggered($callback);
			}

			$data = $this->{$callback}($data);
		}

		return $data;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Magic
	//--------------------------------------------------------------------

	/**
	 * Provides/instantiates the builder/db connection and model's table/primary key names and return type.
	 *
	 * @param string $name
	 *
	 * @return null
	 */
	public function __get(string $name)
	{
		if(in_array($name, ['primaryKey', 'table', 'returnType']))
		{
			return $this->{$name};
		}
		elseif (isset($this->db->$name))
		{
			return $this->db->$name;
		}
		elseif (isset($this->builder()->$name))
		{
			return $this->builder()->$name;
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides direct access to method in the builder (if available)
	 * and the database connection.
	 *
	 * @param string $name
	 * @param array  $params
	 *
	 * @return Model|null
	 */
	public function __call($name, array $params)
	{
		$result = null;

		if (method_exists($this->db, $name))
		{
			$result = $this->db->$name(...$params);
		}
		elseif (method_exists($builder = $this->builder(), $name))
		{
			$result = $builder->$name(...$params);
		}

		// Don't return the builder object unless specifically requested
		//, since that will interrupt the usability flow
		// and break intermingling of model and builder methods.
		if ($name !== 'builder' && empty($result))
		{
			return $result;
		}
		if ($name !== 'builder' && ! $result instanceof BaseBuilder)
		{
			return $result;
		}

		return $this;
	}

	//--------------------------------------------------------------------
}
