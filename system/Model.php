<?php namespace CodeIgniter;

use Config\Database;
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
	 * Name of database table
	 * @var string
	 */
	protected $table;

	/**
	 * The table's primary key.
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * The Database connection group that
	 * should be instantiated.
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

	//--------------------------------------------------------------------

	/**
	 * Used by asArray and asObject to provide
	 * temporary overrides of model default.
	 * @var string
	 */
	protected $tempReturnType;

	/**
	 * Database Connection
	 * @var ConnectionInterface
	 */
	protected $db;

	/**
	 * Query Builder object
	 * @var BaseBuilder
	 */
	protected $builder;


	//--------------------------------------------------------------------

	/**
	 * Model constructor.
	 *
	 * @param ConnectionInterface $db
	 */
	public function __construct(ConnectionInterface $db = null)
	{
	    if ($db instanceof ConnectionInterface)
	    {
		    $this->db = $db;
	    }
		else
		{
			$this->db = Database::connect($this->DBGroup);
		}

		$this->tempReturnType = $this->returnType;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// CRUD & FINDERS
	//--------------------------------------------------------------------

	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id.
	 *
	 * @param $id
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	public function find($id)
	{
		$row = $this->builder()->where($this->primaryKey, $id)
		                       ->get();

		$row = $row->getFirstRow($this->tempReturnType);

		$this->tempReturnType = $this->returnType;

		return $row;
	}

	//--------------------------------------------------------------------

	/**
	 * @param      $key
	 * @param null $value
	 *
	 * @return array|null The rows of data.
	 */
	public function findWhere($key, $value = null)
	{
		$rows = $this->builder()->where($key, $value)
		            ->get();

		$rows = $rows->getResult($this->tempReturnType);

		$this->tempReturnType = $this->returnType;

		return $rows;
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
	public function findAll($limit = 0, $offset = 0)
	{
		$row = $this->builder()->limit($limit, $offset)->get();

		$row = $row->getResult($this->tempReturnType);

		$this->tempReturnType = $this->returnType;

		return $row;
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
		$row = $this->builder()->limit(1, 0)
		            ->get();

		$row = $row->getFirstRow($this->tempReturnType);

		$this->tempReturnType = $this->returnType;

		return $row;
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
		if (is_object($data) && isset($data->{$this->primaryKey}))
		{
			unset($data->{$this->primaryKey});
			return $this->update($data->{$this->primaryKey}, $data);
		}
		elseif (is_array($data) && isset($data[$this->primaryKey]))
		{
			unset($data[$this->primaryKey]);
			return $this->update($data[$this->primaryKey], $data);
		}

		return $this->insert($data);
	}

	//--------------------------------------------------------------------

	/**
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function insert($data)
	{
		// Must use the set() method to ensure objects get converted to arrays
		return $this->builder()->set($data)
		                       ->insert();
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
		// Must use the set() method to ensure objects get converted to arrays
		return $this->builder()->where($this->primaryKey, $id)
		                       ->set($data)
		                       ->update();
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	public function delete($id)
	{
		return $this->builder()->where($this->primaryKey, $id)
								->delete();
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
	 * @param int $size
	 */
	public function chunk($size = 100, \Closure $func)
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Works with $this->builder to get the Compiled Select to operate on.
	 * Expects a GET variable (?page=2) that specifies the page of results
	 * to display.
	 *
	 * @param int $perPage
	 *
	 * @return array|null
	 */
	public function paginate($perPage = 20)
	{
		$page = $_GET['page'] ?? 1;

		$offset = ($page - 1) * $perPage;

		return $this->findAll($perPage, $offset);
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
		if (! $this->db instanceof BaseConnection)
		{
			$this->db = Database::connect($this->DBGroup);
		}

		$this->builder = $this->db->table($table);

		return $this->builder;
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
	public function __call(string $name, array $params)
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

		// Don't return the builder object, since
		// that will interrupt the usability flow
		// and break intermingling of model and builder methods.
		if (empty($result))
		{
			return $result;
		}
		if (! $result instanceof BaseBuilder)
		{
			return $result;
		}

		return $this;
	}
	
	//--------------------------------------------------------------------

}
