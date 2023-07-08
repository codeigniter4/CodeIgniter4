#########################
Using CodeIgniter's Model
#########################

.. contents::
    :local:
    :depth: 3

Models
******

The CodeIgniter's Model provides convenience features and additional functionality
that people commonly use to make working with a **single table** in your database more convenient.

It comes out of the box with helper
methods for much of the standard ways you would need to interact with a database table, including finding records,
updating records, deleting records, and more.

.. _accessing-models:

Accessing Models
****************

Models are typically stored in the **app/Models** directory. They should have a namespace that matches their
location within the directory, like ``namespace App\Models``.

You can access models within your classes by creating a new instance or using the :php:func:`model()` helper function.

.. literalinclude:: model/001.php

The ``model()`` uses ``Factories::models()`` internally.
See :ref:`factories-loading-class` for details on the first parameter.

CodeIgniter's Model
*******************

CodeIgniter does provide a model class that provides a few nice features, including:

- automatic database connection
- basic CRUD methods
- in-model validation
- :ref:`automatic pagination <paginating-with-models>`
- and more

This class provides a solid base from which to build your own models, allowing you to
rapidly build out your application's model layer.

Creating Your Model
*******************

To take advantage of CodeIgniter's model, you would simply create a new model class
that extends ``CodeIgniter\Model``:

.. literalinclude:: model/002.php

This empty class provides convenient access to the database connection, the Query Builder,
and a number of additional convenience methods.

initialize()
============

Should you need additional setup in your model you may extend the ``initialize()`` method
which will be run immediately after the Model's constructor. This allows you to perform
extra steps without repeating the constructor parameters, for example extending other models:

.. literalinclude:: model/003.php

Connecting to the Database
==========================

When the class is first instantiated, if no database connection instance is passed to the constructor,
it will automatically connect to the default database group, as set in the configuration. You can
modify which group is used on a per-model basis by adding the ``$DBGroup`` property to your class.
This ensures that within the model any references to ``$this->db`` are made through the appropriate
connection.

.. literalinclude:: model/004.php

You would replace "group_name" with the name of a defined database group from the database
configuration file.

Configuring Your Model
======================

The model class has some configuration options that can be set to allow the class' methods
to work seamlessly for you. The first two are used by all of the CRUD methods to determine
what table to use and how we can find the required records:

.. literalinclude:: model/005.php

$table
------

Specifies the database table that this model primarily works with. This only applies to the
built-in CRUD methods. You are not restricted to using only this table in your own
queries.

$primaryKey
-----------

This is the name of the column that uniquely identifies the records in this table. This
does not necessarily have to match the primary key that is specified in the database, but
is used with methods like ``find()`` to know what column to match the specified value to.

.. note:: All Models must have a primaryKey specified to allow all of the features to work
    as expected.

$useAutoIncrement
-----------------

Specifies if the table uses an auto-increment feature for ``$primaryKey``. If set to ``false``
then you are responsible for providing primary key value for every record in the table. This
feature may be handy when we want to implement 1:1 relation or use UUIDs for our model. The
default value is ``true``.

.. note:: If you set ``$useAutoIncrement`` to ``false``, then make sure to set your primary
    key in the database to ``unique``. This way you will make sure that all of Model's features
    will still work the same as before.

$returnType
-----------

The Model's CRUD methods will take a step of work away from you and automatically return
the resulting data, instead of the Result object. This setting allows you to define
the type of data that is returned. Valid values are '**array**' (the default), '**object**', or the **fully
qualified name of a class** that can be used with the Result object's ``getCustomResultObject()``
method. Using the special ``::class`` constant of the class will allow most IDEs to
auto-complete the name and allow functions like refactoring to better understand your code.

.. _model-use-soft-deletes:

$useSoftDeletes
---------------

If true, then any ``delete()`` method calls will set ``deleted_at`` in the database, instead of
actually deleting the row. This can preserve data when it might be referenced elsewhere, or
can maintain a "recycle bin" of objects that can be restored, or even simply preserve it as
part of a security trail. If true, the **find*()** methods will only return non-deleted rows, unless
the ``withDeleted()`` method is called prior to calling the **find*()** method.

This requires either a DATETIME or INTEGER field in the database as per the model's
``$dateFormat`` setting. The default field name is ``deleted_at`` however this name can be
configured to any name of your choice by using ``$deletedField`` property.

.. important:: The ``deleted_at`` field must be nullable.

$allowedFields
--------------

This array should be updated with the field names that can be set during ``save()``, ``insert()``, or
``update()`` methods. Any field names other than these will be discarded. This helps to protect
against just taking input from a form and throwing it all at the model, resulting in
potential mass assignment vulnerabilities.

.. note:: The ``$primaryKey`` field should never be an allowed field.

Dates
-----

$useTimestamps
^^^^^^^^^^^^^^

This boolean value determines whether the current date is automatically added to all inserts
and updates. If true, will set the current time in the format specified by ``$dateFormat``. This
requires that the table have columns named **created_at**, **updated_at** and **deleted_at** in the appropriate
data type.

$dateFormat
^^^^^^^^^^^

This value works with ``$useTimestamps`` and ``$useSoftDeletes`` to ensure that the correct type of
date value gets inserted into the database. By default, this creates DATETIME values, but
valid options are: ``'datetime'``, ``'date'``, or ``'int'`` (a PHP timestamp). Using **useSoftDeletes** or
**useTimestamps** with an invalid or missing **dateFormat** will cause an exception.

$createdField
^^^^^^^^^^^^^

Specifies which database field to use for data record create timestamp.
Leave it empty to avoid updating it (even if ``$useTimestamps`` is enabled).

$updatedField
^^^^^^^^^^^^^

Specifies which database field should use for keep data record update timestamp.
Leave it empty to avoid update it (even ``$useTimestamps`` is enabled).

$deletedField
^^^^^^^^^^^^^

Specifies which database field to use for soft deletions. See :ref:`model-use-soft-deletes`.

Validation
----------

$validationRules
^^^^^^^^^^^^^^^^

Contains either an array of validation rules as described in :ref:`validation-array`
or a string containing the name of a validation group, as described in the same section.
Described in more detail below.

$validationMessages
^^^^^^^^^^^^^^^^^^^

Contains an array of custom error messages that should be used during validation, as
described in :ref:`validation-custom-errors`. Described in more detail below.

$skipValidation
^^^^^^^^^^^^^^^

Whether validation should be skipped during all **inserts** and **updates**. The default
value is ``false``, meaning that data will always attempt to be validated. This is
primarily used by the ``skipValidation()`` method, but may be changed to ``true`` so
this model will never validate.

.. _clean-validation-rules:

$cleanValidationRules
^^^^^^^^^^^^^^^^^^^^^

Whether validation rules should be removed that do not exist in the passed data.
This is used in **updates**.
The default value is ``true``, meaning that validation rules for the fields
that are not present in the passed data will be (temporarily) removed before the validation.
This is to avoid validation errors when updating only some fields.

You can also change the value by the ``cleanRules()`` method.

.. note:: Prior to v4.2.7, ``$cleanValidationRules`` did not work due to a bug.

Callbacks
---------

$allowCallbacks
^^^^^^^^^^^^^^^

Whether the callbacks defined below should be used.

$beforeInsert
^^^^^^^^^^^^^
$afterInsert
^^^^^^^^^^^^
$beforeInsertBatch
^^^^^^^^^^^^^^^^^^
$afterInsertBatch
^^^^^^^^^^^^^^^^^
$beforeUpdate
^^^^^^^^^^^^^
$afterUpdate
^^^^^^^^^^^^^
$beforeUpdateBatch
^^^^^^^^^^^^^^^^^^
$afterUpdateBatch
^^^^^^^^^^^^^^^^^
$beforeFind
^^^^^^^^^^^
$afterFind
^^^^^^^^^^
$beforeDelete
^^^^^^^^^^^^^
$afterDelete
^^^^^^^^^^^^

These arrays allow you to specify callback methods that will be run on the data at the
time specified in the property name.

Working with Data
*****************

Finding Data
============

Several functions are provided for doing basic CRUD work on your tables, including ``find()``,
``insert()``, ``update()``, ``delete()`` and more.

find()
------

Returns a single row where the primary key matches the value passed in as the first parameter:

.. literalinclude:: model/006.php

The value is returned in the format specified in ``$returnType``.

You can specify more than one row to return by passing an array of primaryKey values instead
of just one:

.. literalinclude:: model/007.php

.. note:: If no parameters are passed in, ``find()`` will return all rows in that model's table,
    effectively acting like ``findAll()``, though less explicit.

findColumn()
------------

Returns null or an indexed array of column values:

.. literalinclude:: model/008.php

``$column_name`` should be a name of single column else you will get the ``DataException``.

findAll()
---------

Returns all results:

.. literalinclude:: model/009.php

This query may be modified by interjecting Query Builder commands as needed prior to calling this method:

.. literalinclude:: model/010.php

You can pass in a limit and offset values as the first and second
parameters, respectively:

.. literalinclude:: model/011.php

first()
-------

Returns the first row in the result set. This is best used in combination with the query builder.

.. literalinclude:: model/012.php

withDeleted()
-------------

If ``$useSoftDeletes`` is true, then the **find*()** methods will not return any rows where ``deleted_at IS NOT NULL``.
To temporarily override this, you can use the ``withDeleted()`` method prior to calling the **find*()** method.

.. literalinclude:: model/013.php

onlyDeleted()
-------------

Whereas ``withDeleted()`` will return both deleted and not-deleted rows, this method modifies
the next **find*()** methods to return only soft deleted rows:

.. literalinclude:: model/014.php

Saving Data
===========

insert()
--------

The first parameter is an associative array of data to create a new row of data in the database.
If an object is passed instead of an array, it will attempt to convert it to an array.

The array's keys must match the name of the columns in the ``$table``, while the array's values are the values to save for that key.

The optional second parameter is of type boolean, and if it is set to false, the method will return a boolean value,
which indicates the success or failure of the query.

You can retrieve the last inserted row's primary key using the ``getInsertID()`` method.

.. literalinclude:: model/015.php

.. _model-allow-empty-inserts:

allowEmptyInserts()
-------------------

.. versionadded:: 4.3.0

You can use ``allowEmptyInserts()`` method to insert empty data. The Model throws an exception when you try to insert empty data by default. But if you call this method, the check will no longer be performed.

.. literalinclude:: model/056.php

You can enable the check again by calling ``allowEmptyInserts(false)``.

update()
--------

Updates an existing record in the database. The first parameter is the ``$primaryKey`` of the record to update.
An associative array of data is passed into this method as the second parameter. The array's keys must match the name
of the columns in a ``$table``, while the array's values are the values to save for that key:

.. literalinclude:: model/016.php

.. important:: Since v4.3.0, this method raises a ``DatabaseException``
    if it generates an SQL statement without a WHERE clause.
    In previous versions, if it is called without ``$primaryKey`` specified and
    an SQL statement was generated without a WHERE clause, the query would still
    execute and all records in the table would be updated.

Multiple records may be updated with a single call by passing an array of primary keys as the first parameter:

.. literalinclude:: model/017.php

When you need a more flexible solution, you can leave the parameters empty and it functions like the Query Builder's
update command, with the added benefit of validation, events, etc:

.. literalinclude:: model/018.php

.. _model-save:

save()
------

This is a wrapper around the ``insert()`` and ``update()`` methods that handle inserting or updating the record
automatically, based on whether it finds an array key matching the **primary key** value:

.. literalinclude:: model/019.php

The save method also can make working with custom class result objects much simpler by recognizing a non-simple
object and grabbing its public and protected values into an array, which is then passed to the appropriate
insert or update method. This allows you to work with Entity classes in a very clean way. Entity classes are
simple classes that represent a single instance of an object type, like a user, a blog post, job, etc. This
class is responsible for maintaining the business logic surrounding the object itself, like formatting
elements in a certain way, etc. They shouldn't have any idea about how they are saved to the database. At their
simplest, they might look like this:

.. literalinclude:: model/020.php

A very simple model to work with this might look like:

.. literalinclude:: model/021.php

This model works with data from the ``jobs`` table, and returns all results as an instance of ``App\Entities\Job``.
When you need to persist that record to the database, you will need to either write custom methods, or use the
model's ``save()`` method to inspect the class, grab any public and private properties, and save them to the database:

.. literalinclude:: model/022.php

.. note:: If you find yourself working with Entities a lot, CodeIgniter provides a built-in :doc:`Entity class </models/entities>`
    that provides several handy features that make developing Entities simpler.

Deleting Data
=============

delete()
--------

Takes a primary key value as the first parameter and deletes the matching record from the model's table:

.. literalinclude:: model/023.php

If the model's ``$useSoftDeletes`` value is true, this will update the row to set ``deleted_at`` to the current
date and time. You can force a permanent delete by setting the second parameter as true.

An array of primary keys can be passed in as the first parameter to delete multiple records at once:

.. literalinclude:: model/024.php

If no parameters are passed in, will act like the Query Builder's delete method, requiring a where call
previously:

.. literalinclude:: model/025.php

purgeDeleted()
--------------

Cleans out the database table by permanently removing all rows that have 'deleted_at IS NOT NULL'.

.. literalinclude:: model/026.php

.. _in-model-validation:

In-Model Validation
===================

Validating Data
---------------

For many people, validating data in the model is the preferred way to ensure the data is kept to a single
standard, without duplicating code. The Model class provides a way to automatically have all data validated
prior to saving to the database with the ``insert()``, ``update()``, or ``save()`` methods.

.. important:: When you update data, by default, the validation in the model class only
    validates provided fields. This is to avoid validation errors when updating only some fields.

    But this means ``required*`` rules do not work as expected when updating.
    If you want to check required fields, you can change the behavior by configuration.
    See :ref:`clean-validation-rules` for details.

Setting Validation Rules
------------------------

The first step is to fill out the ``$validationRules`` class property with the fields and rules that should
be applied. If you have custom error message that you want to use, place them in the ``$validationMessages`` array:

.. literalinclude:: model/027.php

If you'd rather organize your rules and error messages within the Validation configuration file, you can do that
and simply set ``$validationRules`` to the name of the validation rule group you created:

.. literalinclude:: model/034.php

The other way to set the validation rules to fields by functions,

.. php:namespace:: CodeIgniter

.. php:class:: Model

.. php:method:: setValidationRule($field, $fieldRules)

    :param  string  $field:
    :param  array   $fieldRules:

    This function will set the field validation rules.

    Usage example:

    .. literalinclude:: model/028.php

.. php:method:: setValidationRules($validationRules)

    :param  array   $validationRules:

    This function will set the validation rules.

    Usage example:

    .. literalinclude:: model/029.php

The other way to set the validation message to fields by functions,

.. php:method:: setValidationMessage($field, $fieldMessages)

    :param  string  $field:
    :param  array   $fieldMessages:

    This function will set the field wise error messages.

    Usage example:

    .. literalinclude:: model/030.php

.. php:method:: setValidationMessages($fieldMessages)

    :param  array   $fieldMessages:

    This function will set the field messages.

    Usage example:

    .. literalinclude:: model/031.php

Getting Validation Result
-------------------------

Now, whenever you call the ``insert()``, ``update()``, or ``save()`` methods, the data will be validated. If it fails,
the model will return boolean **false**.

.. _model-getting-validation-errors:

Getting Validation Errors
-------------------------

You can use the ``errors()`` method to retrieve the validation errors:

.. literalinclude:: model/032.php

This returns an array with the field names and their associated errors that can be used to either show all of the
errors at the top of the form, or to display them individually:

.. literalinclude:: model/033.php

Retrieving Validation Rules
---------------------------

You can retrieve a model's validation rules by accessing its ``validationRules``
property:

.. literalinclude:: model/035.php

You can also retrieve just a subset of those rules by calling the accessor
method directly, with options:

.. literalinclude:: model/036.php

The ``$options`` parameter is an associative array with one element,
whose key is either ``'except'`` or ``'only'``, and which has as its
value an array of fieldnames of interest:

.. literalinclude:: model/037.php

Validation Placeholders
-----------------------

The model provides a simple method to replace parts of your rules based on data that's being passed into it. This
sounds fairly obscure but can be especially handy with the ``is_unique`` validation rule. Placeholders are simply
the name of the field (or array key) that was passed in as ``$data`` surrounded by curly brackets. It will be
replaced by the **value** of the matched incoming field. An example should clarify this:

.. literalinclude:: model/038.php

.. note:: Since v4.3.5, you must set the validation rules for the placeholder
    field (``id``).

In this set of rules, it states that the email address should be unique in the database, except for the row
that has an id matching the placeholder's value. Assuming that the form POST data had the following:

.. literalinclude:: model/039.php

then the ``{id}`` placeholder would be replaced with the number **4**, giving this revised rule:

.. literalinclude:: model/040.php

So it will ignore the row in the database that has ``id=4`` when it verifies the email is unique.

.. note:: Since v4.3.5, if the placeholder (``id``) value does not pass the
    validation, the placeholder would not be replaced.

This can also be used to create more dynamic rules at runtime, as long as you take care that any dynamic
keys passed in don't conflict with your form data.

Protecting Fields
=================

To help protect against Mass Assignment Attacks, the Model class **requires** that you list all of the field names
that can be changed during inserts and updates in the ``$allowedFields`` class property. Any data provided
in addition to these will be removed prior to hitting the database. This is great for ensuring that timestamps,
or primary keys do not get changed.

.. literalinclude:: model/041.php

Occasionally, you will find times where you need to be able to change these elements. This is often during
testing, migrations, or seeds. In these cases, you can turn the protection on or off:

.. literalinclude:: model/042.php

Runtime Return Type Changes
===========================

You can specify the format that data should be returned as when using the **find*()** methods as the class property,
``$returnType``. There may be times that you would like the data back in a different format, though. The Model
provides methods that allow you to do just that.

.. note:: These methods only change the return type for the next **find*()** method call. After that,
    it is reset to its default value.

asArray()
---------

Returns data from the next **find*()** method as associative arrays:

.. literalinclude:: model/047.php

asObject()
----------

Returns data from the next **find*()** method as standard objects or custom class instances:

.. literalinclude:: model/048.php

Processing Large Amounts of Data
================================

Sometimes, you need to process large amounts of data and would run the risk of running out of memory.
To make this simpler, you may use the chunk() method to get smaller chunks of data that you can then
do your work on. The first parameter is the number of rows to retrieve in a single chunk. The second
parameter is a Closure that will be called for each row of data.

This is best used during cronjobs, data exports, or other large tasks.

.. literalinclude:: model/049.php

.. _model-events-callbacks:

Working with Query Builder
**************************

Getting Query Builder for the Model's Table
===========================================

CodeIgniter Model has one instance of the Query Builder for that model's database connection.
You can get access to the **shared** instance of the Query Builder any time you need it:

.. literalinclude:: model/043.php

This builder is already set up with the model's ``$table``.

.. note:: Once you get the Query Builder instance, you can call methods of the
    :doc:`Query Builder <../database/query_builder>`.
    However, since Query Builder is not a Model, you cannot call methods of the Model.

Getting Query Builder for Another Table
=======================================

If you need access to another table, you can get another instance of the Query Builder.
Pass the table name in as a parameter, but be aware that this will **not** return
a shared instance:

.. literalinclude:: model/044.php

Mixing Methods of Query Builder and Model
=========================================

You can also use Query Builder methods and the Model's CRUD methods in the same chained call, allowing for
very elegant use:

.. literalinclude:: model/045.php

In this case, it operates on the shared instance of the Query Builder held by the model.

.. important:: The Model does not provide a perfect interface to the Query Builder.
    The Model and the Query Builder are separate classes with different purposes.
    They should not be expected to return the same data.

If the Query Builder returns a result, it is returned as is.
In that case, the result may be different from the one returned by the model's method
and may not be what was expected. The model's events are not triggered.

To prevent unexpected behavior, do not use Query Builder methods that return results
and specify the model's method at the end of the method chaining.

.. note:: You can also access the model's database connection seamlessly:

    .. literalinclude:: model/046.php

Model Events
************

There are several points within the model's execution that you can specify multiple callback methods to run.
These methods can be used to normalize data, hash passwords, save related entities, and much more. The following
points in the model's execution can be affected, each through a class property: ``$beforeInsert``, ``$afterInsert``,
``$beforeInsertBatch``, ``$afterInsertBatch``, ``$beforeUpdate``, ``$afterUpdate``, ``$beforeUpdateBatch``,
``$afterUpdateBatch``, ``$afterFind``, and ``$afterDelete``.

.. note:: ``$beforeInsertBatch``, ``$afterInsertBatch``, ``$beforeUpdateBatch`` and
    ``$afterUpdateBatch`` can be used since v4.3.0.

Defining Callbacks
==================

You specify the callbacks by first creating a new class method in your model to use. This class will always
receive a ``$data`` array as its only parameter. The exact contents of the ``$data`` array will vary between events, but
will always contain a key named **data** that contains the primary data passed to the original method. In the case
of the insert* or update* methods, that will be the key/value pairs that are being inserted into the database. The
main array will also contain the other values passed to the method, and be detailed later. The callback method
must return the original $data array so other callbacks have the full information.

.. literalinclude:: model/050.php

Specifying Callbacks To Run
===========================

You specify when to run the callbacks by adding the method name to the appropriate class property (``$beforeInsert``, ``$afterUpdate``,
etc). Multiple callbacks can be added to a single event and they will be processed one after the other. You can
use the same callback in multiple events:

.. literalinclude:: model/051.php

Additionally, each model may allow (default) or deny callbacks class-wide by setting its ``$allowCallbacks`` property:

.. literalinclude:: model/052.php

You may also change this setting temporarily for a single model call using the ``allowCallbacks()`` method:

.. literalinclude:: model/053.php

Event Parameters
================

Since the exact data passed to each callback varies a bit, here are the details on what is in the ``$data`` parameter
passed to each event:

================= =========================================================================================================
Event             $data contents
================= =========================================================================================================
beforeInsert      **data** = the key/value pairs that are being inserted. If an object or Entity class is passed to the
                  insert method, it is first converted to an array.
afterInsert       **id** = the primary key of the new row, or 0 on failure.
                  **data** = the key/value pairs being inserted.
                  **result** = the results of the insert() method used through the Query Builder.
beforeInsertBatch **data** = associative array of values that are being inserted. If an object or Entity class is passed to the
                  insertBatch method, it is first converted to an array.
afterInsertBatch  **data** = the associative array of values being inserted.
                  **result** = the results of the insertbatch() method used through the Query Builder.
beforeUpdate      **id** = the array of primary keys of the rows being updated.
                  **data** = the key/value pairs that are being updated. If an object or Entity class is passed to the
                  update method, it is first converted to an array.
afterUpdate       **id** = the array of primary keys of the rows being updated.
                  **data** = the key/value pairs being updated.
                  **result** = the results of the update() method used through the Query Builder.
beforeUpdateBatch **data** = associative array of values that are being updated. If an object or Entity class is passed to the
                  updateBatch method, it is first converted to an array.
afterUpdateBatch  **data** = the key/value pairs being updated.
                  **result** = the results of the updateBatch() method used through the Query Builder.
beforeFind        The name of the calling **method**, whether a **singleton** was requested, and these additional fields:
- first()         No additional fields
- find()          **id** = the primary key of the row being searched for.
- findAll()       **limit** = the number of rows to find.
                  **offset** = the number of rows to skip during the search.
afterFind         Same as **beforeFind** but including the resulting row(s) of data, or null if no result found.
beforeDelete      Varies by delete* method. See the following:
- delete()        **id** = primary key of row being deleted.
                  **purge** = boolean whether soft-delete rows should be hard deleted.
afterDelete       **id** = primary key of row being deleted.
                  **purge** = boolean whether soft-delete rows should be hard deleted.
                  **result** = the result of the delete() call on the Query Builder.
                  **data** = unused.
================= =========================================================================================================

Modifying Find* Data
====================

The ``beforeFind`` and ``afterFind`` methods can both return a modified set of data to override the normal response
from the model. For ``afterFind`` any changes made to ``data`` in the return array will automatically be passed back
to the calling context. In order for ``beforeFind`` to intercept the find workflow it must also return an additional
boolean, ``returnData``:

.. literalinclude:: model/054.php

Manual Model Creation
*********************

You do not need to extend any special class to create a model for your application. All you need is to get an
instance of the database connection and you're good to go. This allows you to bypass the features CodeIgniter's
Model gives you out of the box, and create a fully custom experience.

.. literalinclude:: model/055.php
