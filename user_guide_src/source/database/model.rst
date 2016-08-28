#########################
Using CodeIgniter's Model
#########################

.. contents::
    :local:
    :depth: 2

Manual Model Creation
=====================

You do not need to extend any special class to create a model for your application. All you need is to get an
instance of the database connection and you're good to go.

::

	use \CodeIgniter\Database\ConnectionInterface;

	class UserModel
	{
		protected $db;

		public function __construct(ConnectionInterface &$db)
		{
			$this->db =& $db;
		}
	}

CodeIgniter's Model
===================

CodeIgniter does provide a model class that provides a few nice features, including:

- automatic database connection
- basic CRUD methods
- in-model validation
- and more

This class provides a solid base from which to build your own models, allowing you to
rapidly build out your application's model layer.

Creating Your Model
===================

To take advantage of CodeIgniter's model, you would simply create a new model class
that extends ``CodeIgniter\Model``::

	class UserModel extends \CodeIgniter\Model
	{

	}

This empty class provides convenient access to the database connection, the Query Builder,
and a number of additional convenience methods.

Connecting to the Database
--------------------------

When the class is first instantiated, if no database connection instance is passed to constructor,
it will automatically connect to the default database group, as set in the configuration. You can
modify which group is used on a per-model basis by adding the DBGroup property to your class.
This ensures that within the model any references to ``$this->db`` are made through the appropriate
connection.
::

	class UserModel extends \CodeIgniter\Model
	{
		protected $DBGroup = 'group_name';
	}

You would replace "group_name" with the name of a defined database group from the database
configuration file.

Configuring Your Model
----------------------

The model class has a few configuration options that can be set to allow the class' methods
to work seamlessly for you. The first two are used by all of the CRUD methods to determine
what table to use and how we can find the required records::

	class UserModel extends \CodeIgniter\Model
	{
		protected $table      = 'users';
		protected $primaryKey = 'id';

		protected $returnType = 'array';
		protected $useSoftDeletes = true;

		protected $allowedFields = ['name', 'email'];

		protected $useTimestamps = false;
	}

**$table**

Specifies the database table that this model primarily works with. This only applies to the
built-in CRUD methods. You are not restricted to using only this table in your own
queries.

**$primaryKey**

This is the name of the column that uniquely identifies the records in this table. This
does not necessarilly have to match the primary key that is specified in the database, but
is used with methods like ``find()`` to know what column to match the specified value to.

**$returnType**

The Model's CRUD methods will take a step of work away from you and automatically return
the resulting data, instead of the Result object. This setting allows you to define
the type of data that is returned. Valid values are 'array', 'object', or the fully
qualified name of a class that can be used with the Result object's getCustomResultObject()
method.

**$useSoftDeletes**

If true, then any delete* method calls will simply set a flag in the database, instead of
actually deleting the row. This can preserve data when it might be referenced elsewhere, or
can maintain a "recylce bin" of objects that can be restored, or even simply preserve it as
part of a security trail. If true, the find* methods will only return non-deleted rows, unless
the withDeleted() method is called prior to calling the find* method.

This requires an INT or TINYINT field named ``deleted`` to be present in the table.

**$allowedFields**

This array should be updated with the field names that can be set during save, insert, or
update methods. Any field names other than these will be discarded. This helps to protect
against just taking input from a form and throwing it all at the model, resulting in
potential mass assignment vulnerabilities.

**$useTimestamps**

This boolean value determines whether the current date is automatically added to all inserts
and updates. If true, will set the current time in the format specified by $dateFormat. This
requires that the table have columns named 'created_at' and 'updated_at' in the appropriate
data type.

**$dateFormat**

This value works with $useTimestamps to ensure that the correct type of date value gets
inserted into the database. By default, this creates DATETIME values, but valid options
are: datetime, date, or int (a PHP timestamp).

Working With Data
=================

Finding Data
------------

Several functions are provided for doing basic CRUD work on your tables, including find(),
insert(), update(), delete() and more.

**find()**

Returns a single row where the primary key matches the value passed in as the first parameter::

	$user = $userModel->find($user_id);

The value is returned in the format specified in $returnType.

You can specify more than one row to return by passing an array of primaryKey values instead
of just one::

	$users = $userModel->find([1,2,3]);

**findWhere()**

Allows you to specify one or more criteria that must be matched against the data. Returns
all rows that match::

	// Use simple where
	$users = $userModel->findWhere('role_id >', '10');

	// Use array of where values
	$users = $userModel->findWhere([
		'status'  => 'active',
		'deleted' => 0
	]);

**findAll()**

Returns all results.

	$users = $userModel->findAll();

This query may be modified by interjecting Query Builder commands as needed prior to calling this method::

	$users = $userModel->where('active', 1)
	                   ->findAll();

You can pass in a limit and offset values as the first and second
parameters, respectively::

	$users = $userModel->findAll($limit, $offset);

**first()**

Returns the first row in the result set. This is best used in combination with the query builder.
::

	$user = $userModel->where('deleted', 0)
	                  ->first();


**withDeleted()**

If $useSoftDeletes is true, then the find* methods will not return any rows where 'deleted = 1'. To
temporarily override this, you can use the withDeleted() method prior to calling the find* method.
::

	// Only gets non-deleted rows (deleted = 0)
	$activeUsers = $userModel->findAll();

	// Gets all rows
	$allUsers = $userModel->withDeleted()
	                      ->findAll();

**onlyDeleted()**

Whereas withDeleted() will return both deleted and not-deleted rows, this method modifies
the next find* methods to return only soft deleted rows::

	$deletedUsers = $userModel->onlyDeleted()
							  ->findAll();

Saving Data
-----------

**insert()**

An associative array of data is passed into this method as the only parameter to create a new
row of data in the database. The array's keys must match the name of the columns in $table, while
the array's values are the values to save for that key::

	$data = [
		'username' => 'darth',
		'email'    => 'd.vader@theempire.com'
	];

	$userModel->insert($data);

**update()**

Updates an existing record in the database. The first parameter is the $primaryKey of the record to update.
An associative array of data is passed into this method as the second parameter. The array's keys must match the name
of the columns in $table, while the array's values are the values to save for that key::

	$data = [
		'username' => 'darth',
		'email'    => 'd.vader@theempire.com'
	];

	$userModel->update($id, $data);

**save()**

This is a wrapper around the insert() and update() methods that handles inserting or updating the record
automatically, based on whether it finds an array key matching the $primaryKey value::

	// Defined as a model property
	$primaryKey = 'id';

	// Does an insert()
	$data = [
		'username' => 'darth',
		'email'    => 'd.vader@theempire.com'
	];

	$userModel->save($data);

	// Performs an update, since the primary key, 'id', is found.
	$data = [
		'id'       => 3,
		'username' => 'darth',
		'email'    => 'd.vader@theempire.com'
	];
	$userModel->save($data);

Deleting Data
-------------

**delete()**

Takes a primary key value as the first parameter and deletes the matching record from the model's table::

	$userModel->delete(12);

If the model's $useSoftDeletes value is true, this will update the row to set 'deleted = 1'. You can force
a permanent delete by setting the second parameter as true.

**deleteWhere()**

Deletes multiple records from the model's table based on the criteria pass into the first two parameters.
::

	// Simple where
	$userMdoel->deleteWhere('status', 'inactive');

	// Complex where
	$userModel->deleteWhere([
		'status' => 'inactive',
		'warn_lvl >=' => 50
	]);

If the model's $useSoftDeletes value is true, this will update the rows to set 'deleted = 1'. You can force
a permanent delete by setting the third parameter as true.

**purgeDeleted()**

Cleans out the database table by permanently removing all rows that have 'deleted = 1'. ::

	$userModel->purgeDeleted();

Protecting Fields
-----------------

To help protect against Mass Assignment Attacks, the Model class requires that you list all of the field names
that can be changed during inserts and updates in the ``$allowedFields`` class property. Any data provided
in addition to these will be removed prior to hitting the database. This is great for ensuring that timestamps,
or primary keys do not get changed.
::

	protected $allowedFields = ['name', 'email', 'address'];

Occasionally, you will find times where you need to be able to change these elements. This is often during
testing, migrations, or seeds. In these cases, you can turn the protection on or off::

	$model->protect(false)
		  ->insert($data)
		  ->protect(true);

Working With Query Builder
--------------------------

You can get access to a shared instance of the Query Builder for that model's database connection any time you
need it::

	$builder = $userModel->builder();

This builder is already setup with the model's $table.

You can also use Query Builder methods and the Model's CRUD methods in the same chained call, allowing for
very elegant use::

	$users = $userModel->where('status', 'active')
						->orderBy('last_login', 'asc')
						->findAll();

.. note:: You can also access the model's database connection seamlessly::

			$user_name = $userModel->escape($name);


Runtime Return Type Changes
----------------------------

You can specify the format that data should be returned as when using the find*() methods as the class property,
$returnType. There may be times that you would like the data back in a different format, though. The Model
provides methods that allow you to do just that.

.. note:: These methods only change the return type for the next find*() method call. After that,
			it is reset to its default value.

**asArray()**

Returns data from the next find*() method as associative arrays::

	$users = $userModel->asArray()->findWhere('status', 'active');

**asObject()**

Returns data from the next find*() method as standard objects or custom class intances::

	// Return as standard objects
	$users = $userModel->asObject()->findWhere('status', 'active');

	// Return as custom class instances
	$users = $userModel->asObject('User')->findWhere('status', 'active');


Processing Large Amounts of Data
--------------------------------

Sometimes, you need to process large amounts of data and would run the risk of running out of memory.
To make this simpler, you may use the chunk() method to get smaller chunks of data that you can then
do your work on. The first parameter is the number of rows to retrieve in a single chunk. The second
parameter is a Closure that will be called for each row of data.

This is best used during cronjobs, data exports, or other large tasks.
::

	$userModel->chunk(100, function ($data)
	{
		// do something.
		// $data is a single row of data.
	});

Obfuscating IDs in URLs
-----------------------

Instead of displaying the resource's ID in the URL (i.e. /users/123), the model provides a simple
way to obfuscate the ID. This provides some protection against attackers simply incrementing IDs in the
URL to do bad things to your data.

This is not a valid security use, but another simple layer of protection. Determined attackers could very easily
determine the actual ID.

The data is not stored in the database at any time, it is simply used to disguise the ID. When creating a URL
you can use the **encodeID()** method to get the hashed ID.
::

	// Creates something like: http://exmample.com/users/MTIz
	$url = '/users/'. $model->encodeID($user->id);

When you need to grab the item in your controller, you can use the **findByHashedID()** method instead of the
**find()** method.
::

	public function show($hashedID)
	{
		$user = $this->model->findByHashedID($hashedID);
	}

If you ever need to decode the hash, you may do so with the **decodeID()** method.
::

	$hash = $model->encodeID(123);
	$check = $model->decodeID($hash);

.. note:: While the name is "hashed id", this is not actually a hashed variable, but that term has become
		common in many circles to represent the encoding of an ID into a short, unique, identifier.