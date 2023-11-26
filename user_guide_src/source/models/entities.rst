#####################
Using Entity Classes
#####################

CodeIgniter supports Entity classes as a first-class citizen in it's database layer, while keeping
them completely optional to use. They are commonly used as part of the Repository pattern, but can
be used directly with the :doc:`Model </models/model>` if that fits your needs better.

.. contents::
    :local:
    :depth: 2

************
Entity Usage
************

At its core, an Entity class is simply a class that represents a single database row. It has class properties
to represent the database columns, and provides any additional methods to implement the business logic for
that row. The core feature, though, is that it doesn't know anything about how to persist itself. That's the
responsibility of the model or the repository class. That way, if anything changes on how you need to save the
object, you don't have to change how that object is used throughout the application. This makes it possible to
use JSON or XML files to store the objects during a rapid prototyping stage, and then easily switch to a
database when you've proven the concept works.

Let's walk through a very simple User Entity and how we'd work with it to help make things clear.

Assume you have a database table named ``users`` that has the following schema::

    id          - integer
    username    - string
    email       - string
    password    - string
    created_at  - datetime

.. important:: ``attributes`` is a reserved word for internal use. If you use it as a column name, the Entity does not work correctly.

Create the Entity Class
=======================

Now create a new Entity class. Since there's no default location to store these classes, and it doesn't fit
in with the existing directory structure, create a new directory at **app/Entities**. Create the
Entity itself at **app/Entities/User.php**.

.. literalinclude:: entities/001.php

At its simplest, this is all you need to do, though we'll make it more useful in a minute.

Create the Model
================

Create the model first at **app/Models/UserModel.php** so that we can interact with it:

.. literalinclude:: entities/002.php

The model uses the ``users`` table in the database for all of its activities. We've set the ``$allowedFields`` property
to include all of the fields that we want outside classes to change. The ``id``, ``created_at``, and ``updated_at`` fields
are handled automatically by the class or the database, so we don't want to change those. Finally, we've set our Entity
class as the ``$returnType``. This ensures that all methods on the model that return rows from the database will return
instances of our User Entity class instead of an object or array like normal.

Working with the Entity Class
=============================

Now that all of the pieces are in place, you would work with the Entity class as you would any other class:

.. literalinclude:: entities/003.php

You may have noticed that the ``User`` class has not set any properties for the columns, but you can still
access them as if they were public properties. The base class, ``CodeIgniter\Entity\Entity``, takes care of this for you, as
well as providing the ability to check the properties with ``isset()``, or ``unset()`` the property, and keep track
of what columns have changed since the object was created or pulled from the database.

.. note:: The Entity class stores the data in the property ``$attributes``.

When the User is passed to the model's ``save()`` method, it automatically takes care of reading the  properties
and saving any changes to columns listed in the model's ``$allowedFields`` property. It also knows whether to create
a new row, or update an existing one.

.. note:: When we are making a call to the ``insert()`` all the values from Entity are passed to the method, but when we
    call the ``update()``, then only values that have changed are passed.

Filling Properties Quickly
==========================

The Entity class also provides a method, ``fill()`` that allows you to shove an array of key/value pairs into the class
and populate the class properties. Any property in the array will be set on the Entity. However, when saving through
the model, only the fields in ``$allowedFields`` will actually be saved to the database, so you can store additional data
on your entities without worrying much about stray fields getting saved incorrectly.

.. literalinclude:: entities/004.php

You can also pass the data in the constructor and the data will be passed through the ``fill()`` method during instantiation.

.. literalinclude:: entities/005.php

Bulk Accessing Properties
=========================

The Entity class has two methods to extract all available properties into an array: ``toArray()`` and ``toRawArray()``.
Using the raw version will bypass magic "getter" methods and casts. Both methods can take a boolean first parameter
to specify whether returned values should be filtered by those that have changed, and a boolean final parameter to
make the method recursive, in case of nested Entities.

***********************
Handling Business Logic
***********************

While the examples above are convenient, they don't help enforce any business logic. The base Entity class implements
some smart ``__get()`` and ``__set()`` methods that will check for special methods and use those instead of using
the attributes directly, allowing you to enforce any business logic or data conversion that you need.

Here's an updated User entity to provide some examples of how this could be used:

.. literalinclude:: entities/006.php

The first thing to notice is the name of the methods we've added. For each one, the class expects the snake_case
column name to be converted into PascalCase, and prefixed with either ``set`` or ``get``. These methods will then
be automatically called whenever you set or retrieve the class property using the direct syntax (i.e., ``$user->email``).
The methods do not need to be public unless you want them accessed from other classes. For example, the ``created_at``
class property will be accessed through the ``setCreatedAt()`` and ``getCreatedAt()`` methods.

.. note:: This only works when trying to access the properties from outside of the class. Any methods internal to the
    class must call the ``setX()`` and ``getX()`` methods directly.

In the ``setPassword()`` method we ensure that the password is always hashed.

In ``setCreatedAt()`` we convert the string we receive from the model into a DateTime object, ensuring that our timezone
is UTC so we can easily convert the viewer's current timezone. In ``getCreatedAt()``, it converts the time to
a formatted string in the application's current timezone.

While fairly simple, these examples show that using Entity classes can provide a very flexible way to enforce
business logic and create objects that are pleasant to use.

.. literalinclude:: entities/007.php

.. _entities-special-getter-setter:

Special Getter/Setter
=====================

.. versionadded:: 4.4.0

For example, if your Entity's parent class already has a ``getParent()`` method
defined, and your Entity also has a column named ``parent``, when you try to add
business logic to the ``getParent()`` method in your Entity class, the method is
already defined.

In such a case, you can use the special getter/setter. Instead of ``getX()``/``setX()``,
set ``_getX()``/``_setX()``.

In the above example, if your Entity has the ``_getParent()`` method, the method
will be used when you get ``$entity->parent``, and the ``_setParent()`` method
will be used when you set ``$entity->parent``.

************
Data Mapping
************

At many points in your career, you will run into situations where the use of an application has changed and the
original column names in the database no longer make sense. Or you find that your coding style prefers camelCase
class properties, but your database schema required snake_case names. These situations can be easily handled
with the Entity class' data mapping features.

As an example, imagine you have the simplified User Entity that is used throughout your application:

.. literalinclude:: entities/008.php

Your boss comes to you and says that no one uses usernames anymore, so you're switching to just use emails for login.
But they do want to personalize the application a bit, so they want you to change the ``name`` field to represent a user's
full name now, not their username like it does currently. To keep things tidy and ensure things continue making sense
in the database you whip up a migration to rename the ``name`` field to ``full_name`` for clarity.

Ignoring how contrived this example is, we now have two choices on how to fix the User class. We could modify the class
property from ``$name`` to ``$full_name``, but that would require changes throughout the application. Instead, we can
simply map the ``full_name`` column in the database to the ``$name`` property, and be done with the Entity changes:

.. literalinclude:: entities/009.php

By adding our new database name to the ``$datamap`` array, we can tell the class what class property the database column
should be accessible through. The key of the array is class property to map it to, where the value in the array is the
name of the column in the database.

In this example, when the model sets the ``full_name`` field on the User class, it actually assigns that value to the
class' ``$name`` property, so it can be set and retrieved through ``$user->name``. The value will still be accessible
through the original ``$user->full_name``, also, as this is needed for the model to get the data back out and save it
to the database. However, ``unset()`` and ``isset()`` only work on the mapped property, ``$user->name``, not on the database column name,
``$user->full_name``.

.. note:: When you use Data Mapping, you must define ``set*()`` and ``get*()`` method
    for the database column name. In this example, you must define ``setFullName()`` and
    ``getFullName()``.

********
Mutators
********

Date Mutators
=============

By default, the Entity class will convert fields named `created_at`, `updated_at`, or `deleted_at` into
:doc:`Time </libraries/time>` instances whenever they are set or retrieved. The Time class provides a large number
of helpful methods in an immutable, localized way.

You can define which properties are automatically converted by adding the name to the ``$dates`` property:

.. literalinclude:: entities/010.php

Now, when any of those properties are set, they will be converted to a Time instance, using the application's
current timezone, as set in **app/Config/App.php**:

.. literalinclude:: entities/011.php

.. _entities-property-casting:

Property Casting
================

You can specify that properties in your Entity should be converted to common data types with the ``$casts`` property.
This option should be an array where the key is the name of the class property, and the value is the data type it
should be cast to.

Property casting affects both read (get) and write (set), but some types affect
only read (get).

Scalar Type Casting
-------------------

Properties can be cast to any of the following data types:
**integer**, **float**, **double**, **string**, **boolean**, **object**, **array**, **datetime**, **timestamp**, **uri** and **int-bool**.
Add a question mark at the beginning of type to mark property as nullable, i.e., **?string**, **?integer**.

.. note:: **int-bool** can be used since v4.3.0.

For example, if you had a User entity with an ``is_banned`` property, you can cast it as a boolean:

.. literalinclude:: entities/012.php

Array/Json Casting
------------------

Array/Json casting is especially useful with fields that store serialized arrays or json in them. When cast as:

* an **array**, they will automatically be unserialized,
* a **json**, they will automatically be set as an value of ``json_decode($value, false)``,
* a **json-array**, they will automatically be set as an value of ``json_decode($value, true)``,

when you set the property's value.
Unlike the rest of the data types that you can cast properties into, the:

* **array** cast type will serialize,
* **json** and **json-array** cast will use json_encode function on

the value whenever the property is set:

.. literalinclude:: entities/013.php

.. literalinclude:: entities/014.php

CSV Casting
-----------

If you know you have a flat array of simple values, encoding them as a serialized or JSON string
may be more complex than the original structure. Casting as Comma-Separated Values (CSV) is
a simpler alternative will result in a string that uses less space and is more easily read
by humans:

.. literalinclude:: entities/015.php

Stored in the database as "red,yellow,green":

.. literalinclude:: entities/016.php

.. note:: Casting as CSV uses PHP's internal ``implode`` and ``explode`` methods and assumes all values are string-safe and free of commas. For more complex data casts try ``array`` or ``json``.

Custom Casting
--------------

You can define your own conversion types for getting and setting data.

At first you need to create a handler class for your type.
Let's say the class will be located in the **app/Entities/Cast** directory:

.. literalinclude:: entities/017.php

Now you need to register it:

.. literalinclude:: entities/018.php

If you don't need to change values when getting or setting a value. Then just don't implement the appropriate method:

.. literalinclude:: entities/019.php

Parameters
----------

In some cases, one type is not enough. In this situation, you can use additional parameters.
Additional parameters are indicated in square brackets and listed with a comma
like ``type[param1, param2]``.

.. literalinclude:: entities/020.php

.. literalinclude:: entities/021.php

.. note:: If the casting type is marked as nullable ``?bool`` and the passed value is not null, then the parameter with
    the value ``nullable`` will be passed to the casting type handler.
    If casting type has predefined parameters, then ``nullable`` will be added to the end of the list.

*******************************
Checking for Changed Attributes
*******************************

You can check if an Entity attribute has changed since it was created. The only parameter is the name of the
attribute to check:

.. literalinclude:: entities/022.php

Or to check the whole entity for changed values omit the parameter:

.. literalinclude:: entities/023.php
