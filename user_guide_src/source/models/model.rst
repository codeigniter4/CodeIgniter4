#########################
Using CodeIgniter's Model
#########################

.. contents::
    :local:
    :depth: 2

Models
======

Models provide a way to interact with a specific table in your database. They come out of the box with helper
methods for much of the standard ways you would need to interact with a database table, including finding records,
updating records, deleting records, and more.

Accessing Models
================

Models are typically stored in the ``app/Models`` directory. They should have a namespace that matches their
location within the directory, like ``namespace App\Models``.

You can access models within your classes by creating a new instance or using the ``model()`` helper function.

::

    // Create a new class manually
    $userModel = new \App\Models\UserModel();

    // Create a new class with the model function
    $userModel = model('App\Models\UserModel', false);

    // Create a shared instance of the model
    $userModel = model('App\Models\UserModel');

    // Create shared instance with a supplied database connection
    // When no namespace is given, it will search through all namespaces
    // the system knows about and attempt to located the UserModel class.
    $db = db_connect('custom');
    $userModel = model('UserModel', true, $db);


CodeIgniter's Model
===================

CodeIgniter does provide a model class that provides a few nice features, including:

- automatic database connection
- basic CRUD methods
- in-model validation
- automatic pagination
- and more

This class provides a solid base from which to build your own models, allowing you to
rapidly build out your application's model layer.

Creating Your Model
===================

To take advantage of CodeIgniter's model, you would simply create a new model class
that extends ``CodeIgniter\Model``::

    <?php namespace App\Models;

    use CodeIgniter\Model;

    class UserModel extends Model
    {

    }

This empty class provides convenient access to the database connection, the Query Builder,
and a number of additional convenience methods.

Connecting to the Database
--------------------------

When the class is first instantiated, if no database connection instance is passed to the constructor,
it will automatically connect to the default database group, as set in the configuration. You can
modify which group is used on a per-model basis by adding the DBGroup property to your class.
This ensures that within the model any references to ``$this->db`` are made through the appropriate
connection.
::

    <?php namespace App\Models;

    use CodeIgniter\Model;

    class UserModel extends Model
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

    <?php namespace App\Models;

    use CodeIgniter\Model;

    class UserModel extends Model
    {
        protected $table      = 'users';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';
        protected $useSoftDeletes = true;

        protected $allowedFields = ['name', 'email'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';
        protected $deletedField  = 'deleted_at';

        protected $validationRules    = [];
        protected $validationMessages = [];
        protected $skipValidation     = false;
    }

**$table**

Specifies the database table that this model primarily works with. This only applies to the
built-in CRUD methods. You are not restricted to using only this table in your own
queries.

**$primaryKey**

This is the name of the column that uniquely identifies the records in this table. This
does not necessarily have to match the primary key that is specified in the database, but
is used with methods like ``find()`` to know what column to match the specified value to.

.. note:: All Models must have a primaryKey specified to allow all of the features to work
    as expected.

**$useAutoIncrement**

Specifies if the table uses an auto-increment feature for ``$primaryKey``. If set to ``false``
then you are responsible for providing primary key value for every record in the table. This 
feature may be handy when we want to implement 1:1 relation or use UUIDs for our model.

.. note:: If you set ``$useAutoIncrement`` to ``false`` then make sure to set your primary
    key in the database to ``unique``. This way you will make sure that all of Model's features
    will still work the same as before.

**$returnType**

The Model's CRUD methods will take a step of work away from you and automatically return
the resulting data, instead of the Result object. This setting allows you to define
the type of data that is returned. Valid values are 'array', 'object', or the fully
qualified name of a class that can be used with the Result object's getCustomResultObject()
method.

**$useSoftDeletes**

If true, then any delete* method calls will set ``deleted_at`` in the database, instead of
actually deleting the row. This can preserve data when it might be referenced elsewhere, or
can maintain a "recycle bin" of objects that can be restored, or even simply preserve it as
part of a security trail. If true, the find* methods will only return non-deleted rows, unless
the withDeleted() method is called prior to calling the find* method.

This requires either a DATETIME or INTEGER field in the database as per the model's
$dateFormat setting. The default field name is ``deleted_at`` however this name can be
configured to any name of your choice by using $deletedField property.

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

**$createdField**

Specifies which database field should use for keep data record create timestamp.
Leave it empty to avoid update it (even useTimestamps is enabled)

**$updatedField**

Specifies which database field should use for keep data record update timestamp.
Leave it empty to avoid update it (even useTimestamps is enabled)

**$dateFormat**

This value works with $useTimestamps and $useSoftDeletes to ensure that the correct type of
date value gets inserted into the database. By default, this creates DATETIME values, but
valid options are: datetime, date, or int (a PHP timestamp). Using 'useSoftDeletes' or
'useTimestamps' with an invalid or missing dateFormat will cause an exception.

**$validationRules**

Contains either an array of validation rules as described in :ref:`validation-array`
or a string containing the name of a validation group, as described in the same section.
Described in more detail below.

**$validationMessages**

Contains an array of custom error messages that should be used during validation, as
described in :ref:`validation-custom-errors`. Described in more detail below.

**$skipValidation**

Whether validation should be skipped during all ``inserts`` and ``updates``. The default
value is false, meaning that data will always attempt to be validated. This is
primarily used by the ``skipValidation()`` method, but may be changed to ``true`` so
this model will never validate.

**$beforeInsert**
**$afterInsert**
**$beforeUpdate**
**$afterUpdate**
**$afterFind**
**$afterDelete**

These arrays allow you to specify callback methods that will be run on the data at the
time specified in the property name.

**$allowCallbacks**

Whether the callbacks defined above should be used.

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

If no parameters are passed in, will return all rows in that model's table, effectively acting
like findAll(), though less explicit.

**findColumn()**

Returns null or an indexed array of column values::

    $user = $userModel->findColumn($column_name);

$column_name should be a name of single column else you will get the DataException.

**findAll()**

Returns all results::

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

If $useSoftDeletes is true, then the find* methods will not return any rows where 'deleted_at IS NOT NULL'.
To temporarily override this, you can use the withDeleted() method prior to calling the find* method.
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
row of data in the database. The array's keys must match the name of the columns in a $table, while
the array's values are the values to save for that key::

    $data = [
        'username' => 'darth',
        'email'    => 'd.vader@theempire.com'
    ];

    $userModel->insert($data);

**update()**

Updates an existing record in the database. The first parameter is the $primaryKey of the record to update.
An associative array of data is passed into this method as the second parameter. The array's keys must match the name
of the columns in a $table, while the array's values are the values to save for that key::

    $data = [
        'username' => 'darth',
        'email'    => 'd.vader@theempire.com'
    ];

    $userModel->update($id, $data);

Multiple records may be updated with a single call by passing an array of primary keys as the first parameter::

    $data = [
        'active' => 1
    ];

    $userModel->update([1, 2, 3], $data);

When you need a more flexible solution, you can leave the parameters empty and it functions like the Query Builder's
update command, with the added benefit of validation, events, etc::

    $userModel
        ->whereIn('id', [1,2,3])
        ->set(['active' => 1])
        ->update();

**save()**

This is a wrapper around the insert() and update() methods that handle inserting or updating the record
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

The save method also can make working with custom class result objects much simpler by recognizing a non-simple
object and grabbing its public and protected values into an array, which is then passed to the appropriate
insert or update method. This allows you to work with Entity classes in a very clean way. Entity classes are
simple classes that represent a single instance of an object type, like a user, a blog post, job, etc. This
class is responsible for maintaining the business logic surrounding the object itself, like formatting
elements in a certain way, etc. They shouldn't have any idea about how they are saved to the database. At their
simplest, they might look like this::

    namespace App\Entities;

    class Job
    {
        protected $id;
        protected $name;
        protected $description;

        public function __get($key)
        {
            if (property_exists($this, $key))
            {
                return $this->$key;
            }
        }

        public function __set($key, $value)
        {
            if (property_exists($this, $key))
            {
                $this->$key = $value;
            }
        }
    }

A very simple model to work with this might look like::

    use CodeIgniter\Model;

    class JobModel extends Model
    {
        protected $table = 'jobs';
        protected $returnType = '\App\Entities\Job';
        protected $allowedFields = [
            'name', 'description'
        ];
    }

This model works with data from the ``jobs`` table, and returns all results as an instance of ``App\Entities\Job``.
When you need to persist that record to the database, you will need to either write custom methods, or use the
model's ``save()`` method to inspect the class, grab any public and private properties, and save them to the database::

    // Retrieve a Job instance
    $job = $model->find(15);

    // Make some changes
    $job->name = "Foobar";

    // Save the changes
    $model->save($job);

.. note:: If you find yourself working with Entities a lot, CodeIgniter provides a built-in :doc:`Entity class </models/entities>`
    that provides several handy features that make developing Entities simpler.

Deleting Data
-------------

**delete()**

Takes a primary key value as the first parameter and deletes the matching record from the model's table::

    $userModel->delete(12);

If the model's $useSoftDeletes value is true, this will update the row to set ``deleted_at`` to the current
date and time. You can force a permanent delete by setting the second parameter as true.

An array of primary keys can be passed in as the first parameter to delete multiple records at once::

    $userModel->delete([1,2,3]);

If no parameters are passed in, will act like the Query Builder's delete method, requiring a where call
previously::

    $userModel->where('id', 12)->delete();

**purgeDeleted()**

Cleans out the database table by permanently removing all rows that have 'deleted_at IS NOT NULL'. ::

    $userModel->purgeDeleted();

Validating Data
---------------

For many people, validating data in the model is the preferred way to ensure the data is kept to a single
standard, without duplicating code. The Model class provides a way to automatically have all data validated
prior to saving to the database with the ``insert()``, ``update()``, or ``save()`` methods.

The first step is to fill out the ``$validationRules`` class property with the fields and rules that should
be applied. If you have custom error message that you want to use, place them in the ``$validationMessages`` array::

    class UserModel extends Model
    {
        protected $validationRules    = [
            'username'     => 'required|alpha_numeric_space|min_length[3]',
            'email'        => 'required|valid_email|is_unique[users.email]',
            'password'     => 'required|min_length[8]',
            'pass_confirm' => 'required_with[password]|matches[password]'
        ];

        protected $validationMessages = [
            'email'        => [
                'is_unique' => 'Sorry. That email has already been taken. Please choose another.'
            ]
        ];
    }

The other way to set the validation rules to fields by functions,

.. php:function:: setValidationRule($field, $fieldRules)

    :param  string  $field:
    :param  array   $fieldRules:

    This function will set the field validation rules.

    Usage example::

        $fieldName = 'username';
        $fieldRules = 'required|alpha_numeric_space|min_length[3]';
        
        $model->setValidationRule($fieldName, $fieldRules);

.. php:function:: setValidationRules($validationRules)

    :param  array   $validationRules:

    This function will set the validation rules.

    Usage example::

        $validationRules = [
            'username' => 'required|alpha_numeric_space|min_length[3]',
            'email' => [
                'rules'  => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'We really need your email.',
                ],
            ],
        ];
        $model->setValidationRules($validationRules);

The other way to set the validation message to fields by functions,

.. php:function:: setValidationMessage($field, $fieldMessages)

    :param  string  $field:
    :param  array   $fieldMessages:

    This function will set the field wise error messages.

    Usage example::

        $fieldName = 'name';
        $fieldValidationMessage = [
            'required' => 'Your name is required here',
        ];
        $model->setValidationMessage($fieldName, $fieldValidationMessage);

.. php:function:: setValidationMessages($fieldMessages)

    :param  array   $fieldMessages:

    This function will set the field messages.

    Usage example::

        $fieldValidationMessage = [
            'name' => [
                'required'   => 'Your baby name is missing.',
                'min_length' => 'Too short, man!',
            ],
        ];
        $model->setValidationMessages($fieldValidationMessage);

Now, whenever you call the ``insert()``, ``update()``, or ``save()`` methods, the data will be validated. If it fails,
the model will return boolean **false**. You can use the ``errors()`` method to retrieve the validation errors::

    if ($model->save($data) === false)
    {
        return view('updateUser', ['errors' => $model->errors()]);
    }

This returns an array with the field names and their associated errors that can be used to either show all of the
errors at the top of the form, or to display them individually::

    <?php if (! empty($errors)) : ?>
        <div class="alert alert-danger">
        <?php foreach ($errors as $field => $error) : ?>
            <p><?= $error ?></p>
        <?php endforeach ?>
        </div>
    <?php endif ?>

If you'd rather organize your rules and error messages within the Validation configuration file, you can do that
and simply set ``$validationRules`` to the name of the validation rule group you created::

    class UserModel extends Model
    {
        protected $validationRules = 'users';
    }

Retrieving Validation Rules
---------------------------

You can retrieve a model's validation rules by accessing its ``validationRules``
property::

    $rules = $model->validationRules;

You can also retrieve just a subset of those rules by calling the accessor
method directly, with options::

    $rules = $model->getValidationRules($options);

The ``$options`` parameter is an associative array with one element,
whose key is either "except" or "only", and which has as its
value an array of fieldnames of interest.::

    // get the rules for all but the "username" field
    $rules = $model->getValidationRules(['except' => ['username']]);
    // get the rules for only the "city" and "state" fields
    $rules = $model->getValidationRules(['only' => ['city', 'state']]);

Validation Placeholders
-----------------------

The model provides a simple method to replace parts of your rules based on data that's being passed into it. This
sounds fairly obscure but can be especially handy with the ``is_unique`` validation rule. Placeholders are simply
the name of the field (or array key) that was passed in as $data surrounded by curly brackets. It will be
replaced by the **value** of the matched incoming field. An example should clarify this::

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]'
    ];

In this set of rules, it states that the email address should be unique in the database, except for the row
that has an id matching the placeholder's value. Assuming that the form POST data had the following::

    $_POST = [
        'id' => 4,
        'email' => 'foo@example.com'
    ];

then the ``{id}`` placeholder would be replaced with the number **4**, giving this revised rule::

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email,id,4]'
    ];

So it will ignore the row in the database that has ``id=4`` when it verifies the email is unique.

This can also be used to create more dynamic rules at runtime, as long as you take care that any dynamic
keys passed in don't conflict with your form data.

Protecting Fields
-----------------

To help protect against Mass Assignment Attacks, the Model class **requires** that you list all of the field names
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

This builder is already set up with the model's $table.

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

    $users = $userModel->asArray()->where('status', 'active')->findAll();

**asObject()**

Returns data from the next find*() method as standard objects or custom class intances::

    // Return as standard objects
    $users = $userModel->asObject()->where('status', 'active')->findAll();

    // Return as custom class instances
    $users = $userModel->asObject('User')->where('status', 'active')->findAll();

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

Model Events
============

There are several points within the model's execution that you can specify multiple callback methods to run.
These methods can be used to normalize data, hash passwords, save related entities, and much more. The following
points in the model's execution can be affected, each through a class property: **$beforeInsert**, **$afterInsert**,
**$beforeUpdate**, **$afterUpdate**, **$afterFind**, and **$afterDelete**.

Defining Callbacks
------------------

You specify the callbacks by first creating a new class method in your model to use. This class will always
receive a $data array as its only parameter. The exact contents of the $data array will vary between events, but
will always contain a key named **data** that contains the primary data passed to the original method. In the case
of the insert* or update* methods, that will be the key/value pairs that are being inserted into the database. The
main array will also contain the other values passed to the method, and be detailed later. The callback method
must return the original $data array so other callbacks have the full information.

::

    protected function hashPassword(array $data)
    {
        if (! isset($data['data']['password']) return $data;

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password'];

        return $data;
    }

Specifying Callbacks To Run
---------------------------

You specify when to run the callbacks by adding the method name to the appropriate class property (beforeInsert, afterUpdate,
etc). Multiple callbacks can be added to a single event and they will be processed one after the other. You can
use the same callback in multiple events::

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

Additionally, each model may allow (default) or deny callbacks class-wide by setting its $allowCallbacks property::

	protected $allowCallbacks = false;

You may also change this setting temporarily for a single model call sing the ``allowCallbacks()`` method::

	$model->allowCallbacks(false)->find(1); // No callbacks triggered
	$model->find(1);                        // Callbacks subject to original property value

Event Parameters
----------------

Since the exact data passed to each callback varies a bit, here are the details on what is in the $data parameter
passed to each event:

================ =========================================================================================================
Event            $data contents
================ =========================================================================================================
beforeInsert      **data** = the key/value pairs that are being inserted. If an object or Entity class is passed to the
                  insert method, it is first converted to an array.
afterInsert       **id** = the primary key of the new row, or 0 on failure.
                  **data** = the key/value pairs being inserted.
                  **result** = the results of the insert() method used through the Query Builder.
beforeUpdate      **id** = the array of primary keys of the rows being updated.
                  **data** = the key/value pairs that are being inserted. If an object or Entity class is passed to the
                  insert method, it is first converted to an array.
afterUpdate       **id** = the array of primary keys of the rows being updated.
                  **data** = the key/value pairs being updated.
                  **result** = the results of the update() method used through the Query Builder.
afterFind         Varies by find* method. See the following:
- find()          **id** = the primary key of the row being searched for.
                  **data** = The resulting row of data, or null if no result found.
- findAll()       **data** = the resulting rows of data, or null if no result found.
                  **limit** = the number of rows to find.
                  **offset** = the number of rows to skip during the search.
- first()         **data** = the resulting row found during the search, or null if none found.
beforeFind        Same as **afterFind** but with the name of the calling **$method** instead of **$data**.
beforeDelete      Varies by delete* method. See the following:
- delete()        **id** = primary key of row being deleted.
                  **purge** = boolean whether soft-delete rows should be hard deleted.
afterDelete       **id** = primary key of row being deleted.
                  **purge** = boolean whether soft-delete rows should be hard deleted.
                  **result** = the result of the delete() call on the Query Builder.
                  **data** = unused.
================ =========================================================================================================

Modifying Find* Data
--------------------

The ``beforeFind`` and ``afterFind`` methods can both return a modified set of data to override the normal response
from the model. For ``afterFind`` any changes made to ``data`` in the return array will automatically be passed back
to the calling context. In order for ``beforeFind`` to intercept the find workflow it must also return an additional
boolean, ``returnData``::

    protected $beforeFind = ['checkCache'];
    ...
	protected function checkCache(array $data)
	{
		// Check if the requested item is already in our cache
		if (isset($data['id']) && $item = $this->getCachedItem($data['id']]))
		{
			$data['data']       = $item;
			$data['returnData'] = true;

			return $data;
	...

Manual Model Creation
=====================

You do not need to extend any special class to create a model for your application. All you need is to get an
instance of the database connection and you're good to go. This allows you to bypass the features CodeIgniter's
Model gives you out of the box, and create a fully custom experience.

::

    <?php namespace App\Models;

    use CodeIgniter\Database\ConnectionInterface;

    class UserModel
    {
        protected $db;

        public function __construct(ConnectionInterface &$db)
        {
            $this->db =& $db;
        }
    }
