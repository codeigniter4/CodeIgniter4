<?php namespace CodeIgniter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @copyright    2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 3.0.0
 * @filesource
 */
use CodeIgniter\Pager\Pager;
use CodeIgniter\Validation\ValidationInterface;
use Config\App;
use Config\Database;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;

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
	protected $allowedFields = ['name'];

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
	 * @var array|null
	 */
	protected $validationMessages = null;

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
	 * @var \CodeIgniter\Validation\ValidationInterface
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
	protected $afterDelete = [];

	//--------------------------------------------------------------------

	/**
	 * Model constructor.
	 *
	 * @param ConnectionInterface $db
	 * @param BaseConfig          $config Config/App()
	 */
	public function __construct(ConnectionInterface &$db = null, BaseConfig $config = null, ValidationInterface $validation = null)
	{
		if ($db instanceof ConnectionInterface)
		{
			$this->db = & $db;
		}
		else
		{
			$this->db = Database::connect($this->DBGroup);
		}

		if (is_null($config) || ! isset($config->salt))
		{
			$config = new App();
		}

		$this->salt = $config->salt ?: '';
		unset($config);

		$this->tempReturnType = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		if (is_null($validation))
		{
			$validation = \Config\Services::validation();
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
	 * @param mixed|array $id One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	public function find($id)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where('deleted', 0);
		}

		if (is_array($id))
		{
			$row = $builder->whereIn($this->primaryKey, $id)
					->get();
			$row = $row->getResult($this->tempReturnType);
		}
		else
		{
			$row = $builder->where($this->primaryKey, $id)
					->get();

			$row = $row->getFirstRow($this->tempReturnType);
		}

		$row = $this->trigger('afterFind', ['id' => $id, 'data' => $row]);

		$this->tempReturnType = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $row['data'];
	}

	//--------------------------------------------------------------------

	/**
	 * Extract a subset of data
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return array|null The rows of data.
	 */
	public function findWhere($key, $value = null)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where('deleted', 0);
		}

		$rows = $builder->where($key, $value)
				->get();

		$rows = $rows->getResult($this->tempReturnType);

		$rows = $this->trigger('afterFind', ['data' => $rows]);

		$this->tempReturnType = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $rows['data'];
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
			$builder->where('deleted', 0);
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
			$builder->where('deleted', 0);
		}

		// Some databases, like PostgreSQL, need order
		// information to consistently return correct results.
		if (empty($builder->QBOrderBy))
		{
			$builder->orderBy($this->primaryKey, 'asc');
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
	 * Finds a single record by a "hashed" primary key. Used in conjunction
	 * with $this->getIDHash().
	 *
	 * THIS IS NOT VALID TO USE FOR SECURITY!
	 *
	 * @param string $hashedID
	 *
	 * @return array|null|object
	 */
	public function findByHashedID(string $hashedID)
	{
		return $this->find($this->decodeID($hashedID));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a "hashed id", which isn't really hashed, but that's
	 * become a fairly common term for this. Essentially creates
	 * an obfuscated id, intended to be used to disguise the
	 * ID from incrementing IDs to get access to things they shouldn't.
	 *
	 * THIS IS NOT VALID TO USE FOR SECURITY!
	 *
	 * Note, at some point we might want to move to something more
	 * complex. The hashid library is good, but only works on integers.
	 *
	 * @see http://hashids.org/php/
	 * @see http://raymorgan.net/web-development/how-to-obfuscate-integer-ids/
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function encodeID($id)
	{
		// Strings don't currently have a secure
		// method, so simple base64 encoding will work for now.
		if ( ! is_numeric($id))
		{
			return '=_' . base64_encode($id);
		}

		$id = (int) $id;
		if ($id < 1)
		{
			return false;
		}
		if ($id > pow(2, 31))
		{
			return false;
		}

		$segment1 = $this->getHash($id, 16);
		$segment2 = $this->getHash($segment1, 8);
		$dec = (int) base_convert($segment2, 16, 10);
		$dec = ($dec > $id) ? $dec - $id : $dec + $id;
		$segment2 = base_convert($dec, 10, 16);
		$segment2 = str_pad($segment2, 8, '0', STR_PAD_LEFT);
		$segment3 = $this->getHash($segment1 . $segment2, 8);
		$hex = $segment1 . $segment2 . $segment3;
		$bin = pack('H*', $hex);
		$oid = base64_encode($bin);
		$oid = str_replace(['+', '/', '='], ['$', ':', ''], $oid);

		return $oid;
	}

	//--------------------------------------------------------------------

	/**
	 * Decodes our hashed id.
	 *
	 * @see http://raymorgan.net/web-development/how-to-obfuscate-integer-ids/
	 *
	 * @param $hash
	 *
	 * @return mixed
	 */
	public function decodeID($hash)
	{
		// Was it a simple string we encoded?
		if (substr($hash, 0, 2) == '=_')
		{
			$hash = substr($hash, 2);

			return base64_decode($hash);
		}

		if ( ! preg_match('/^[A-Z0-9\:\$]{21,23}$/i', $hash))
		{
			return 0;
		}
		$hash = str_replace(['$', ':'], ['+', '/'], $hash);
		$bin = base64_decode($hash);
		$hex = unpack('H*', $bin);
		$hex = $hex[1];
		if ( ! preg_match('/^[0-9a-f]{32}$/', $hex))
		{
			return 0;
		}
		$segment1 = substr($hex, 0, 16);
		$segment2 = substr($hex, 16, 8);
		$segment3 = substr($hex, 24, 8);
		$exp2 = $this->getHash($segment1, 8);
		$exp3 = $this->getHash($segment1 . $segment2, 8);
		if ($segment3 != $exp3)
		{
			return 0;
		}
		$v1 = (int) base_convert($segment2, 16, 10);
		$v2 = (int) base_convert($exp2, 16, 10);
		$id = abs($v1 - $v2);

		return $id;
	}

	//--------------------------------------------------------------------

	/**
	 * Used for our hashed IDs. Requires $salt to be defined
	 * within the Config\App file.
	 *
	 * @param $str
	 * @param $len
	 *
	 * @return string
	 */
	protected function getHash($str, $len)
	{
		return substr(sha1($str . $this->salt), 0, $len);
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method that will attempt to determine whether the
	 * data should be inserted or updated. Will work with either
	 * an array or object. When using with custom class objects,
	 * you must ensure that the class will provide access to the class
	 * variables, even if through a magic method.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function save($data)
	{
		$saveData = $data;

		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof \stdClass)
		{
			$data = static::classToArray($data);
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

		// If it was an Entity class, check it for an onSave method.
		if (is_object($saveData) && ! $saveData instanceof \stdClass)
		{
			if (method_exists($saveData, 'onSave'))
			{
				$saveData->onSave();
			}
		}

		return $response;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a class an returns an array of it's public and protected
	 * properties as an array suitable for use in creates and updates.
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function classToArray($data): array
	{
		$mirror = new \ReflectionClass($data);
		$props = $mirror->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

		$properties = [];

		// Loop over each property,
		// saving the name/value in a new array we can return.
		foreach ($props as $prop)
		{
			// Must make protected values accessible.
			$prop->setAccessible(true);
			$properties[$prop->getName()] = $prop->getValue($data);
		}

		return $properties;
	}

	//--------------------------------------------------------------------

	/**
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param      $data
	 * @param bool $returnID Whether insert ID should be returned or not.
	 *
	 * @return bool
	 */
	public function insert($data, bool $returnID = true)
	{
		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof \stdClass)
		{
			$data = static::classToArray($data);
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
			throw new \InvalidArgumentException('No data to insert.');
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $this->builder()
				->set($data['data'])
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
	 * Updates a single record in $this->table. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return bool
	 */
	public function update($id, $data)
	{
		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof \stdClass)
		{
			$data = static::classToArray($data);
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
			throw new \InvalidArgumentException('No data to update.');
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $this->builder()
				->where($this->primaryKey, $id)
				->set($data['data'])
				->update();

		$this->trigger('afterUpdate', ['id' => $id, 'data' => $originalData, 'result' => $result]);

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 *
	 * @param mixed $id    The rows primary key
	 * @param bool  $purge Allows overriding the soft deletes setting.
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function delete($id, $purge = false)
	{
		if ($this->useSoftDeletes && ! $purge)
		{
			$result = $this->builder()
					->where($this->primaryKey, $id)
					->update(['deleted' => 1]);
		}
		else
		{
			$result = $this->builder()
					->where($this->primaryKey, $id)
					->delete();
		}

		$this->trigger('afterDelete', ['id' => $id, 'purge' => $purge, 'result' => $result, 'data' => null]);

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes multiple records from $this->table where the specified
	 * key/value matches.
	 *
	 * @param      $key
	 * @param null $value
	 * @param bool $purge Allows overriding the soft deletes setting.
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function deleteWhere($key, $value = null, $purge = false)
	{
		// Don't let them shoot themselves in the foot...
		if (empty($key))
		{
			throw new DatabaseException('You must provided a valid key to deleteWhere.');
		}

		if ($this->useSoftDeletes && ! $purge)
		{
			$result = $this->builder()
					->where($key, $value)
					->update(['deleted' => 1]);
		}
		else
		{
			$result = $this->builder()
					->where($key, $value)
					->delete();
		}

		$this->trigger('afterDelete', ['key' => $key, 'value' => $value, 'purge' => $purge, 'result' => $result, 'data' => null]);

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 *
	 * @return bool|mixed
	 * @throws DatabaseException
	 */
	public function purgeDeleted()
	{
		if ( ! $this->useSoftDeletes)
		{
			return true;
		}

		return $this->builder()
						->where('deleted', 1)
						->delete();
	}

	//--------------------------------------------------------------------

	/**
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the softdeletes settings. Can be used for all find* methods.
	 *
	 * @param bool $val
	 *
	 * @return $this
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
	 * @return $this
	 */
	public function onlyDeleted()
	{
		$this->tempUseSoftDeletes = false;

		$this->builder()
				->where('deleted', 1);

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Utility
	//--------------------------------------------------------------------

	/**
	 * Sets the return type of the results to be as an associative array.
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
	 * @return $this
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
	 * @throws DatabaseException
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
				throw new DatabaseException('Unable to get results from the query.');
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
	 *
	 * @return array|null
	 */
	public function paginate(int $perPage = 20, string $group = 'default')
	{
		// Get the necessary parts.
		$page = $_GET['page'] ?? 1;

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
	 * @return $this
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
	 * @return BaseBuilder|Database\QueryBuilder
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
	 * @param $data
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	protected function doProtectFields($data)
	{
		if ($this->protectFields === false)
		{
			return $data;
		}

		if (empty($this->allowedFields))
		{
			throw new DatabaseException('No Allowed fields specified for model: ' . get_class($this));
		}

		foreach ($data as $key => $val)
		{
			if ( ! in_array($key, $this->allowedFields))
			{
				unset($data[$key]);
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
	 * @return $this
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
	 * @return $this
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
		if ($this->skipValidation === true || empty($this->validationRules))
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
			$this->validation->setRules($this->validationRules, $this->validationMessages);
			$valid = $this->validation->run($data);
		}

		return (bool) $valid;
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
				throw new \BadMethodCallException(lang('Database.invalidEvent', [$callback]));
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
	 * Provides/instantiates the builder/db connection.
	 *
	 * @param string $name
	 *
	 * @return null
	 */
	public function __get(string $name)
	{
		if (isset($this->db->$name))
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
	 * @return $this|null
	 */
	public function __call($name, array $params)
	{
		$result = null;

		if (method_exists($this->db, $name))
		{
			$result = call_user_func_array([$this->db, $name], $params);
		}
		elseif (method_exists($this->builder(), $name))
		{
			$result = call_user_func_array([$this->builder(), $name], $params);
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
