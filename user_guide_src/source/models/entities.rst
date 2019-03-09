#####################
Working With Entities
#####################

CodeIgniter supports Entity classes as a first-class citizen in it's database layer, while keeping
them completely optional to use. They are commonly used as part of the Repository pattern, but can
be used directly with the :doc:`Model </models/model>` if that fits your needs better.

.. contents::
    :local:
    :depth: 2

Entity Usage
============

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

Create the Entity Class
-----------------------

Now create a new Entity class. Since there's no default location to store these classes, and it doesn't fit
in with the existing directory structure, create a new directory at **app/Entities**. Create the
Entity itself at **app/Entities/User.php**.

::

    <?php namespace App\Entities;

    use CodeIgniter\Entity;

    class User extends Entity
    {
        protected $id;
        protected $username;
        protected $email;
        protected $password;
        protected $created_at;
        protected $updated_at;
    }

At its simplest, this is all you need to do, though we'll make it more useful in a minute. Note that all of the
database columns are represented in the Entity. This is required for the Model to populate the fields.

Create the Model
----------------

Create the model first at **app/Models/UserModel.php** so that we can interact with it::

    <?php namespace App\Models;

    use CodeIgniter\Model;

    class UserModel extends Model
    {
        protected $table         = 'users';
        protected $allowedFields = [
            'username', 'email', 'password'
        ];
        protected $returnType    = 'App\Entities\User';
        protected $useTimestamps = true;
    }

The model uses the ``users`` table in the database for all of its activities. We've set the ``$allowedFields`` property
to include all of the fields that we want outside classes to change. The ``id``, ``created_at``, and ``updated_at`` fields
are handled automatically by the class or the database, so we don't want to change those. Finally, we've set our Entity
class as the ``$returnType``. This ensures that all methods on the model that return rows from the database will return
instances of our User Entity class instead of an object or array like normal.

Working With the Entity Class
-----------------------------

Now that all of the pieces are in place, you would work with the Entity class as you would any other class::

    $user = $userModel->find($id);

    // Display
    echo $user->username;
    echo $user->email;

    // Updating
    unset($user->username);
    if (! isset($user->username)
    {
        $user->username = 'something new';
    }
    $userModel->save($user);

    // Create
    $user = new App\Entities\User();
    $user->username = 'foo';
    $user->email    = 'foo@example.com';
    $userModel->save($user);

You may have noticed that the User class has all of the properties as **protected** not **public**, but you can still
access them as if they were public properties. The base class, **CodeIgniter\Entity**, takes care of this for you, as
well as providing the ability to check the properties with **isset()**, or **unset()** the property.

When the User is passed to the model's **save()** method, it automatically takes care of reading the protected properties
and saving any changes to columns listed in the model's **$allowedFields** property. It also knows whether to create
a new row, or update an existing one.

Filling Properties Quickly
--------------------------

The Entity class also provides a method, ``fill()`` that allows you to shove an array of key/value pairs into the class
and populate the class properties. Only properties that already exist in the class can be populated in this way.

::

    $data = $this->request->getPost();

    $user = new App\Entities\User();
    $user->fill($data);
    $userModel->save($user);

Handling Business Logic
=======================

While the examples above are convenient, they don't help enforce any business logic. The base Entity class implements
some smart ``__get()`` and ``__set()`` methods that will check for special methods and use those instead of using
the class properties directly, allowing you to enforce any business logic or data conversion that you need.

Here's an updated User entity to provide some examples of how this could be used::

    <?php namespace App\Entities;

    use CodeIgniter\Entity;
    use CodeIgniter\I18n\Time;

    class User extends Entity
    {
        protected $id;
        protected $username;
        protected $email;
        protected $password;
        protected $created_at;
        protected $updated_at;

        public function setPassword(string $pass)
        {
            $this->password = password_hash($pass, PASSWORD_BCRYPT);

            return $this;
        }

        public function setCreatedAt(string $dateString)
        {
            $this->created_at = new Time($dateString, 'UTC');

            return $this;
        }

        public function getCreatedAt(string $format = 'Y-m-d H:i:s')
        {
            // Convert to CodeIgniter\I18n\Time object
            $this->created_at = $this->mutateDate($this->created_at);

            $timezone = $this->timezone ?? app_timezone();

            $this->created_at->setTimezone($timezone);

            return $this->created_at->format($format);
        }
    }

The first thing to notice is the name of the methods we've added. For each one, the class expects the snake_case
column name to be converted into PascalCase, and prefixed with either ``set`` or ``get``. These methods will then
be automatically called whenever you set or retrieve the class property using the direct syntax (i.e. $user->email).
The methods do not need to be public unless you want them accessed from other classes. For example, the ``created_at``
class property will be accessed through the ``setCreatedAt()`` and ``getCreatedAt()`` methods.

.. note:: This only works when trying to access the properties from outside of the track. Any methods internal to the
    class must call the ``setX()`` and ``getX()`` methods directly.

In the ``setPassword()`` method we ensure that the password is always hashed.

In ``setCreatedAt()`` we convert the string we receive from the model into a DateTime object, ensuring that our timezone
is UTC so we can easily convert the viewer's current timezone. In ``getCreatedAt()``, it converts the time to
a formatted string in the application's current timezone.

While fairly simple, these examples show that using Entity classes can provide a very flexible way to enforce
business logic and create objects that are pleasant to use.

::

    // Auto-hash the password - both do the same thing
    $user->password = 'my great password';
    $user->setPassword('my great password');

Data Mapping
============

At many points in your career, you will run into situations where the use of an application has changed and the
original column names in the database no longer make sense. Or you find that your coding style prefers camelCase
class properties, but your database schema required snake_case names. These situations can be easily handled
with the Entity class' data mapping features.

As an example, imagine you have the simplified User Entity that is used throughout your application::

    <?php namespace App\Entities;

    use CodeIgniter\Entity;

    class User extends Entity
    {
        protected $id;
        protected $name;        // Represents a username
        protected $email;
        protected $password;
        protected $created_at;
        protected $updated_at;
    }

Your boss comes to you and says that no one uses usernames anymore, so you're switching to just use emails for login.
But they do want to personalize the application a bit, so they want you to change the name field to represent a user's
full name now, not their username like it does currently. To keep things tidy and ensure things continue making sense
in the database you whip up a migration to rename the `name` field to `full_name` for clarity.

Ignoring how contrived this example is, we now have two choices on how to fix the User class. We could modify the class
property from ``$name`` to ``$full_name``, but that would require changes throughout the application. Instead, we can
simply map the ``full_name`` column in the database to the ``$name`` property, and be done with the Entity changes::

    <?php namespace App\Entities;

    use CodeIgniter\Entity;

    class User extends Entity
    {
        protected $id;
        protected $name;        // Represents a full name now
        protected $email;
        protected $password;
        protected $created_at;
        protected $updated_at;

        protected $_options = [
            'datamap' => [
                'full_name' => 'name'
            ],
            'dates' => ['created_at', 'updated_at', 'deleted_at'],
            'casts' => []
        ];
    }

By adding our new database name to the ``$datamap`` array, we can tell the class what class property the database column
should be accessible through. The key of the array is the name of the column in the database, where the value in the array
is class property to map it to.

In this example, when the model sets the ``full_name`` field on the User class, it actually assigns that value to the
class' ``$name`` property, so it can be set and retrieved through ``$user->name``. The value will still be accessible
through the original ``$user->full_name``, also, as this is needed for the model to get the data back out and save it
to the database. However, ``unset`` and ``isset`` only work on the mapped property, ``$name``, not on the original name,
``full_name``.

Mutators
========

Date Mutators
-------------

By default, the Entity class will convert fields named `created_at`, `updated_at`, or `deleted_at` into
:doc:`Time </libraries/time>` instances whenever they are set or retrieved. The Time class provides a large number
of helpful methods in an immutable, localized way.

You can define which properties are automatically converted by adding the name to the **options['dates']** array::

    <?php namespace App\Entities;

    use CodeIgniter\Entity;

    class User extends Entity
    {
        protected $id;
        protected $name;        // Represents a full name now
        protected $email;
        protected $password;
        protected $created_at;
        protected $updated_at;

        protected $_options = [
            'dates' => ['created_at', 'updated_at', 'deleted_at'],
            'casts' => [],
            'datamap' => []
        ];
    }

Now, when any of those properties are set, they will be converted to a Time instance, using the application's
current timezone, as set in **app/Config/App.php**::

    $user = new App\Entities\User();

    // Converted to Time instance
    $user->created_at = 'April 15, 2017 10:30:00';

    // Can now use any Time methods:
    echo $user->created_at->humanize();
    echo $user->created_at->setTimezone('Europe/London')->toDateString();

Property Casting
----------------

You can specify that properties in your Entity should be converted to common data types with the **casts** entry in
the **$_options** property. The **casts** option should be an array where the key is the name of the class property,
and the value is the data type it should be cast to. Casting only affects when values are read. No conversions happen
that affect the permanent value in either the entity or the database. Properties can be cast to any of the following
data types: **integer**, **float**, **double**, **string**, **boolean**, **object**, **array**, **datetime**, and
**timestamp**. Add question mark at the beginning of type to mark property as nullable, i.e. **?string**, **?integer**.

For example, if you had a User entity with an **is_banned** property, you can cast it as a boolean::

    <?php namespace App\Entities;

    use CodeIgniter\Entity;

    class User extends Entity
    {
        protected $is_banned;

        protected $_options = [
            'casts' => [
                'is_banned' => 'boolean',
                'is_banned_nullable' => '?boolean'
            ],
            'dates' => ['created_at', 'updated_at', 'deleted_at'],
            'datamap' => []
        ];
    }

Array/Json Casting
------------------

Array/Json casting is especially useful with fields that store serialized arrays or json in them. When cast as:

* an **array**, they will automatically be unserialized,
* a **json**, they will automatically be set as an value of json_decode($value, false),
* a **json-array**, they will automatically be set as an value of json_decode($value, true),

when you read the property's value.
Unlike the rest of the data types that you can cast properties into, the:

* **array** cast type will serialize,
* **json** and **json-array** cast will use json_encode function on

the value whenever the property is set::

    <?php namespace App\Entities;

    use CodeIgniter\Entity;

    class User extends Entity
    {
        protected $options;

        protected $_options = [
            'casts' => [
                'options' => 'array',
		'options_object' => 'json',
		'options_array' => 'json-array'
            ],
             'dates' => ['created_at', 'updated_at', 'deleted_at'],
            'datamap' => []
        ];
    }

    $user    = $userModel->find(15);
    $options = $user->options;

    $options['foo'] = 'bar';

    $user->options  = $options;
    $userModel->save($user);
