# Using Entity Classes

CodeIgniter supports Entity classes as a first-class citizen, while keeping them completely optional to use. They are commonly used as part of the Repository pattern, but can be used directly with the [Model](model.md) if that fits your needs better.

## Entity Usage

At its core, an Entity class is simply a class that represents a single database row. It has class properties to represent the database columns, and provides any additional methods to implement the business logic for that row.

!!! note "Note"
    For ease of understanding, the explanation here is based on the case of using a database. However, Entity can also be used for data that does not come from a database.

The core feature, though, is that it doesn't know anything about how to persist itself. That's the responsibility of the model or the repository class. That way, if anything changes in how you need to save the object, you don't have to change how that object is used throughout the application.

This makes it possible to use JSON or XML files to store the objects during a rapid prototyping stage, and then easily switch to a database when you've proven the concept works.

Let's walk through a very simple User Entity and how we'd work with it to help make things clear.

Assume you have a database table named `users` that has the following schema:

```
id          - integer
username    - string
email       - string
password    - string
created_at  - datetime
```

### Create the Entity Class

Now create a new Entity class. Since there's no default location to store these classes, and it doesn't fit in with the existing directory structure, create a new directory at **app/Entities**. Create the Entity itself at **app/Entities/User.php**.

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    // ...
}
```

At its simplest, this is all you need to do, though we'll make it more useful in a minute.

### Create the Model

Create the model first at **app/Models/UserModel.php** so that we can interact with it:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $allowedFields = [
        'username', 'email', 'password',
    ];
    protected $returnType    = \App\Entities\User::class;
    protected $useTimestamps = true;
}
```

The model uses the `users` table in the database for all of its activities.

We've set the `$allowedFields` property to include all of the fields that we want outside classes to change. The `id`, `created_at`, and `updated_at` fields are handled automatically by the class or the database, so we don't want to change those.

Finally, we've set our Entity class as the `$returnType`. This ensures that all built-in methods on the model that return rows from the database will return instances of our User Entity class instead of an object or array like normal.

!!! note "Note"
    Of course, if you add a custom method to your model, you must implement it so that instances of `$returnType` are returned.

### Working with the Entity Class

Now that all of the pieces are in place, you would work with the Entity class as you would any other class:

```php
<?php

$user = $userModel->find($id);

// Display
echo $user->username;
echo $user->email;

// Updating
unset($user->username);

if (! isset($user->username)) {
    $user->username = 'something new';
}

$userModel->save($user);

// Create
$user           = new \App\Entities\User();
$user->username = 'foo';
$user->email    = 'foo@example.com';
$userModel->save($user);
```

You may have noticed that the `User` class has not set any properties for the columns, but you can still access them as if they were public properties. The base class, `CodeIgniter\Entity\Entity`, takes care of this for you, as well as providing the ability to check the properties with `isset()`, `unset()` them, and keep track of what columns have changed since the object was created or pulled from the database.

!!! note "Note"
    The Entity class stores the data in the class property `$attributes` internally.

When the User is passed to the model's `save()` method, it automatically takes care of reading the properties and saving any changes to columns listed in the model's `$allowedFields` property. It also knows whether to create a new row, or update an existing one.

!!! note "Note"
    When we call the `insert()` method, all the values from the Entity are passed to the method, but when we call the `update()` method, only the values that have changed are passed.

### Filling Properties Quickly

The Entity class also provides a method, `fill()`, that allows you to shove an array of key/value pairs into the class and populate the class properties. Any property in the array will be set on the Entity. However, when saving through the model, only the fields in `$allowedFields` will actually be saved to the database, so you can store additional data on your entities without worrying much about stray fields getting saved incorrectly.

```php
<?php

$data = $this->request->getPost();

$user = new \App\Entities\User();
$user->fill($data);
$userModel->save($user);
```

You can also pass the data in the constructor and the data will be passed through the `fill()` method during instantiation.

```php
<?php

$data = $this->request->getPost();

$user = new \App\Entities\User($data);
$userModel->save($user);
```

### Bulk Accessing Properties

The Entity class has two methods to extract all available properties into an array: `toArray()` and `toRawArray()`. Using the raw version will bypass magic "getter" methods and casts. Both methods can take a boolean first parameter to specify whether returned values should be filtered by those that have changed, and a boolean final parameter to make the method recursive, in case of nested Entities.

### Hidden properties

An Entity may have hidden attributes - their names start with an underscore (`_`). By default, these hidden properties are not included when you call `toArray()`. However, you can still access them directly if needed.

If you want hidden properties to appear in the `toArray()` output, you'll need to use the `datamap` feature - it makes those properties "visible".

Keep in mind that the smart `__get()` and `__set()` methods (described in the next section) ignore leading underscores in property names. This means that two attributes with the same name (one starting with `_` and one without) will both use the same getter and setter, so you have to choose which one to handle by default.

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $datamap = [
        '_role' => '_role',
    ];

    protected $attributes = [
        '__secure' => 'On',
        '_role'    => 'user',
        'about'    => '',
    ];
}

$user = new User(['__secure' => 'Off', 'about' => 'Hi, I am John!', '_role' => 'admin']);

echo 'Secure: ' . $user->__secure;
print_r($user->toArray());
print_r($user->toRawArray());

/**
 * Output:
 *
 * Secure: Off
 * Array
 * (
 *     [about] => Hi, I am John!
 *     [_role] => admin
 * )
 * Array
 * (
 *     [__secure] => Off
 *     [_role] => admin
 *     [about] => Hi, I am John!
 * )
 */
```

## Handling Business Logic

While the examples above are convenient, they don't help enforce any business logic. The base Entity class implements some smart `__get()` and `__set()` methods that will check for special methods and use those instead of using the attributes directly, allowing you to enforce any business logic or data conversion that you need.

Here's an updated User entity to provide some examples of how this could be used:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class User extends Entity
{
    public function setPassword(string $pass)
    {
        $this->attributes['password'] = password_hash($pass, PASSWORD_BCRYPT);

        return $this;
    }

    public function setCreatedAt(string $dateString)
    {
        $this->attributes['created_at'] = new Time($dateString, 'UTC');

        return $this;
    }

    public function getCreatedAt(string $format = 'Y-m-d H:i:s')
    {
        // Convert to CodeIgniter\I18n\Time object
        $this->attributes['created_at'] = $this->mutateDate($this->attributes['created_at']);

        $timezone = $this->timezone ?? app_timezone();

        $this->attributes['created_at']->setTimezone($timezone);

        return $this->attributes['created_at']->format($format);
    }
}
```

The first thing to notice is the name of the methods we've added. For each one, the class expects the snake_case column name to be converted into PascalCase, and prefixed with either `set` or `get`. These methods will then be automatically called whenever you set or retrieve the class property using the direct syntax (i.e., `$user->email`). The methods do not need to be public unless you want them accessed from other classes. For example, the `created_at` class property will be accessed through the `setCreatedAt()` and `getCreatedAt()` methods.

!!! note "Note"
    This only works when trying to access the properties from outside of the class. Any methods internal to the class must call the `setX()` and `getX()` methods directly.

In the `setPassword()` method we ensure that the password is always hashed.

In `setCreatedAt()` we convert the string we receive from the model into a DateTime object, ensuring that our timezone is UTC so we can easily convert to the viewer's current timezone. In `getCreatedAt()`, it converts the time to a formatted string in the application's current timezone.

While fairly simple, these examples show that using Entity classes can provide a very flexible way to enforce business logic and create objects that are pleasant to use.

```php
<?php

// Auto-hash the password - both do the same thing
$user->password = 'my great password';
$user->setPassword('my great password');
```

### Special Getter/Setter

!!! success "Available from version 4.4.0"

For example, if your Entity's parent class already has a `getParent()` method defined, and your Entity also has a column named `parent`, when you try to add business logic to the `getParent()` method in your Entity class, the method is already defined.

In such a case, you can use the special getter/setter. Instead of `getX()`/`setX()`, set `_getX()`/`_setX()`.

In the above example, if your Entity has the `_getParent()` method, the method will be used when you get `$entity->parent`, and the `_setParent()` method will be used when you set `$entity->parent`.

## Data Mapping

At many points in your career, you will run into situations where the use of an application has changed and the original column names in the database no longer make sense. Or you find that your coding style prefers camelCase class properties, but your database schema requires snake_case names. These situations can be easily handled with the Entity class' data mapping features.

As an example, imagine you have the simplified User Entity that is used throughout your application:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        'id'         => null,
        'name'       => null, // Represents a username
        'email'      => null,
        'password'   => null,
        'created_at' => null,
        'updated_at' => null,
    ];
}
```

Your boss comes to you and says that no one uses usernames anymore, so you're switching to just use emails for login. But they do want to personalize the application a bit, so they want you to change the `name` field to represent a user's full name now, not their username like it does currently. To keep things tidy and ensure things continue making sense in the database, you whip up a migration to rename the `name` field to `full_name` for clarity.

Ignoring how contrived this example is, we now have two choices on how to fix the User class. We could modify the class property from `$name` to `$full_name`, but that would require changes throughout the application. Instead, we can simply map the `full_name` column in the database to the `$name` property, and be done with the Entity changes:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        'id'         => null,
        'full_name'  => null, // In the $attributes, the key is the db column name
        'email'      => null,
        'password'   => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $datamap = [
        // property_name => db_column_name
        'name' => 'full_name',
    ];
}
```

By adding our new database name to the `$datamap` array, we can tell the class what class property the database column should be accessible through. The key of the array is the class property to map it to, and the value in the array is the name of the column in the database.

In this example, when the model sets the `full_name` field on the User class, it actually assigns that value to the class' `$name` property, so it can be set and retrieved through `$user->name`. The value will also still be accessible through the original `$user->full_name`, as this is needed for the model to get the data back out and save it to the database. However, `unset()` and `isset()` only work on the mapped property, `$user->name`, not on the database column name, `$user->full_name`.

!!! note "Note"
    When you use Data Mapping, you must define `set*()` and `get*()` methods for the database column name. In this example, you must define `setFullName()` and `getFullName()`.

## Mutators

### Date Mutators

By default, the Entity class will convert fields named `created_at`, `updated_at`, or `deleted_at` into [Time](../libraries/time.md) instances whenever they are set or retrieved. The Time class provides a large number of helpful methods in an immutable, localized way.

You can define which properties are automatically converted by adding the name to the `$dates` property:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
```

Now, when any of those properties are set, they will be converted to a Time instance, using the application's current timezone, as set in **app/Config/App.php**:

```php
<?php

$user = new \App\Entities\User();

// Converted to Time instance
$user->created_at = 'April 15, 2017 10:30:00';

// Can now use any Time methods:
echo $user->created_at->humanize();
echo $user->created_at->setTimezone('Europe/London')->toDateString();
```

### Property Casting

You can specify that properties in your Entity should be converted to common data types with the `$casts` property. This option should be an array where the key is the name of the class property, and the value is the data type it should be cast to.

Property casting affects both read (get) and write (set), but some types affect only read (get).

#### Scalar Type Casting

Properties can be cast to any of the following data types: **integer**, **float**, **double**, **string**, **boolean**, **object**, **array**, **datetime**, **timestamp**, **uri**, **int-bool** and **enum**. Add a question mark at the beginning of the type to mark the property as nullable, i.e., **?string**, **?integer**.

!!! note "Note"
    **int-bool** can be used since v4.3.0.

!!! note "Note"
    **enum** can be used since v4.7.0.

For example, if you had a User entity with an `is_banned` property, you can cast it as a boolean:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $casts = [
        'is_banned'          => 'boolean',
        'is_banned_nullable' => '?boolean',
    ];
}
```

#### Array/Json Casting

Array/Json casting is especially useful with fields that store serialized arrays or JSON. When you read the property's value:

- with the **array** cast, the value is automatically unserialized.
- with the **json** cast, the value is automatically converted with `json_decode($value, false)`.
- with the **json-array** cast, the value is automatically converted with `json_decode($value, true)`.

Unlike the rest of the data types that you can cast properties into, these cast types also convert the value whenever the property is set:

- the **array** cast serializes the value.
- the **json** and **json-array** casts use `json_encode()` on the value.

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $casts = [
        'options'        => 'array',
        'options_object' => 'json',
        'options_array'  => 'json-array',
    ];
}
```

```php
<?php

$user    = $userModel->find(15);
$options = $user->options;

$options['foo'] = 'bar';

$user->options = $options;
$userModel->save($user);
```

!!! warning "Warning"
    The `array` cast uses PHP serialization. Only read values previously written through the same Entity cast. Never store untrusted serialized strings directly in an `array`-cast field.

#### CSV Casting

If you know you have a flat array of simple values, encoding them as a serialized or JSON string may be more complex than the original structure. Casting as Comma-Separated Values (CSV) is a simpler alternative that will result in a string that uses less space and is more easily read by humans:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Widget extends Entity
{
    protected $casts = [
        'colors' => 'csv',
    ];
}
```

Stored in the database as "red,yellow,green":

```php
<?php

$widget->colors = ['red', 'yellow', 'green'];
```

!!! note "Note"
    Casting as CSV uses PHP's internal `implode` and `explode` methods and assumes all values are string-safe and free of commas. For more complex data casts try `array` or `json`.

#### Enum Casting

!!! success "Available from version 4.7.0"

You can cast properties to PHP enums. You must specify the enum class name as a parameter.

Enum casting supports:

- **Backed enums** (string or int) - The backing value is stored in the database
- **Unit enums** - The case name is stored in the database as a string

For example, if you had a User entity with a `status` property using a backed enum:

```php
<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Pending  = 'pending';
}
```

You can cast it in your Entity:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $casts = [
        'status' => 'enum[App\Enums\UserStatus]',
    ];
}
```

Now, when you access the `status` property, it will automatically be converted to a `UserStatus` enum instance:

```php
<?php

use App\Enums\UserStatus;

$user = $userModel->find(1);

// Returns a UserStatus enum instance
echo $user->status->value; // 'active'

// Set using enum
$user->status = UserStatus::Inactive;

// Or set using the backing value (will be converted to enum on read)
$user->status = 'pending';

// Note: Internally, enums are always stored as their backing value (string/int)
// in the entity's $attributes array
```

For nullable enums:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $casts = [
        'status' => '?enum[App\Enums\UserStatus]',
    ];
}
```

#### Custom Casting

You can define your own conversion types for getting and setting data.

First, you need to create a handler class for your type. Let's say the class will be located in the **app/Entities/Cast** directory:

```php
<?php

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

// The class must inherit the CodeIgniter\Entity\Cast\BaseCast class
class CastBase64 extends BaseCast
{
    public static function get($value, array $params = [])
    {
        return base64_decode($value, true);
    }

    public static function set($value, array $params = [])
    {
        return base64_encode($value);
    }
}
```

Now you need to register it:

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MyEntity extends Entity
{
    // Specify the type for the field
    protected $casts = [
        'key' => 'base64',
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'base64' => Cast\CastBase64::class,
    ];
}

// ...

$entity->key = 'test'; // dGVzdA==
echo $entity->key;     // test
```

If you don't need to change values when getting or setting a value, just don't implement the appropriate method:

```php
<?php

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class CastBase64 extends BaseCast
{
    public static function get($value, array $params = [])
    {
        return base64_decode($value, true);
    }
}
```

#### Parameters

In some cases, one type is not enough. In this situation, you can use additional parameters. Additional parameters are indicated in square brackets and listed with a comma like `type[param1, param2]`.

```php
<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MyEntity extends Entity
{
    // Define a type with parameters
    protected $casts = [
        'some_attribute' => 'class[App\SomeClass, param2, param3]',
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'class' => 'SomeHandler',
    ];
}
```

```php
<?php

namespace App\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class SomeHandler extends BaseCast
{
    public static function get($value, array $params = [])
    {
        var_dump($params);
        /*
         * Output:
         * array(3) {
         *   [0]=>
         *   string(13) "App\SomeClass"
         *   [1]=>
         *   string(6) "param2"
         *   [2]=>
         *   string(6) "param3"
         * }
         */
    }
}
```

!!! note "Note"
    If the casting type is marked as nullable like `?bool` and the passed value is not null, then the parameter with the value `nullable` will be passed to the casting type handler. If the casting type has predefined parameters, then `nullable` will be added to the end of the list.

## Checking for Changed Attributes

You can check if an Entity attribute has changed since it was created. The only parameter is the name of the attribute to check:

```php
<?php

$user = new \App\Entities\User();
$user->hasChanged('name'); // false

$user->name = 'Fred';
$user->hasChanged('name'); // true
```

Or, to check the whole entity for changed values, omit the parameter:

```php
<?php

$user->hasChanged(); // true
```

### Deep Change Tracking

!!! success "Available from version 4.7.0"

The Entity class performs **deep comparison** for objects and arrays to accurately detect changes in their internal state.

#### Scalar Values

For scalar values (strings, integers, floats, booleans, null), the Entity uses direct comparison. When all attributes in an Entity are scalars, an optimized comparison is used for better performance.

#### Objects and Arrays

For objects and arrays, the Entity JSON-encodes and normalizes the values for comparison. This means that modifications to nested structures, object properties, array elements, nested entities (using `toRawArray()`), enums (`BackedEnum` and `UnitEnum`), datetime objects (`DateTimeInterface`), collections (`Traversable`), value objects with `__toString()`, and objects implementing `JsonSerializable` or `toArray()` will be properly detected.
