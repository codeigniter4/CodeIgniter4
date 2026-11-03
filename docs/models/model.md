# Using CodeIgniter's Model

## Models

CodeIgniter's Model provides convenience features and additional functionality that people commonly use to make working with a **single table** in your database more convenient.

It comes out of the box with helper methods for many of the standard ways you would need to interact with a database table, including finding records, updating records, deleting records, and more.

## Accessing Models

Models are typically stored in the **app/Models** directory. They should have a namespace that matches their location within the directory, like `namespace App\Models`.

You can access models within your classes by creating a new instance or using the `model()` helper function.

```php
<?php

// Create a new class manually.
$userModel = new \App\Models\UserModel();

// Create a shared instance of the model.
$userModel = model('UserModel');
// or
$userModel = model('App\Models\UserModel');
// or
$userModel = model(\App\Models\UserModel::class);

// Create a new class with the model() function.
$userModel = model('UserModel', false);

// Create shared instance with a supplied database connection.
$db        = db_connect('custom');
$userModel = model('UserModel', true, $db);
```

The `model()` function uses `Factories::models()` internally. See [Loading Classes](../concepts/factories.md#loading-classes) for details on the first parameter.

## CodeIgniter's Model

CodeIgniter does provide a model class that has a few nice features, including:

- automatic database connection
- basic CRUD methods
- [in-model validation](#in-model-validation)
- [automatic pagination](../libraries/pagination.md#paginating-with-models)
- and more

This class provides a solid base from which to build your own models, allowing you to rapidly build out your application's model layer.

## Creating Your Model

To take advantage of CodeIgniter's model, you would simply create a new model class that extends `CodeIgniter\Model`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // ...
}
```

This empty class provides convenient access to the database connection, the Query Builder, and a number of additional convenience methods.

### initialize()

Should you need additional setup in your model, you may extend the `initialize()` method, which will be run immediately after the Model's constructor. This allows you to perform extra steps without repeating the constructor parameters, for example extending other models:

```php
<?php

namespace App\Models;

use Modules\Authentication\Models\UserAuthModel;

class UserModel extends UserAuthModel
{
    // ...

    /**
     * Called during initialization. Appends
     * our custom field to the module's model.
     */
    protected function initialize()
    {
        $this->allowedFields[] = 'middlename';
    }
}
```

### Connecting to the Database

When the class is first instantiated, if no database connection instance is passed to the constructor, and if you don't set the `$DBGroup` property on your model class, it will automatically connect to the default database group, as set in the database configuration.

You can modify which group is used on a per-model basis by adding the `$DBGroup` property to your class. This ensures that within the model any references to `$this->db` are made through the appropriate connection.

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup = 'group_name';

    // ...
}
```

You would replace "group_name" with the name of a defined database group from the database configuration file.

### Configuring Your Model

The model class has some configuration options that can be set to allow the class' methods to work seamlessly for you. The first two are used by all of the CRUD methods to determine what table to use and how we can find the required records:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['name', 'email'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
```

#### $table

Specifies the database table that this model primarily works with. This only applies to the built-in CRUD methods. You are not restricted to using only this table in your own queries.

#### $primaryKey

This is the name of the column that uniquely identifies the records in this table. This does not necessarily have to match the primary key that is specified in the database, but is used with methods like `find()` to know what column to match the specified value to.

!!! note "Note"
    All Models must have a primaryKey specified to allow all of the features to work as expected.

#### $useAutoIncrement

Specifies if the table uses an auto-increment feature for [$primaryKey](#primarykey). If set to `false` then you are responsible for providing a primary key value for every record in the table. This feature may be handy when we want to implement a 1:1 relation or use UUIDs for our model. The default value is `true`.

!!! note "Note"
    If you set [$useAutoIncrement](#useautoincrement) to `false`, then make sure to set your primary key in the database to `unique`. This way you will make sure that all of the Model's features will still work the same as before.

#### $returnType

The Model's **find\*()** methods will take a step of work away from you and automatically return the resulting data, instead of the Result object.

This setting allows you to define the type of data that is returned. Valid values are '**array**' (the default), '**object**', or the **fully qualified name of a class** that can be used with the Result object's `getCustomResultObject()` method.

Using the special `::class` constant of the class will allow most IDEs to auto-complete the name and allow functions like refactoring to better understand your code.

#### $useSoftDeletes

If true, then any `delete()` method calls will set `deleted_at` in the database, instead of actually deleting the row. This can preserve data when it might be referenced elsewhere, or can maintain a "recycle bin" of objects that can be restored, or even simply preserve it as part of a security trail. If true, the **find\*()** methods will only return non-deleted rows, unless the `withDeleted()` method is called prior to calling the **find\*()** method.

This requires either a DATETIME or INTEGER field in the database as per the model's [$dateFormat](#dateformat) setting. The default field name is `deleted_at`, but this name can be configured to any name of your choice by using the [$deletedField](#deletedfield) property.

!!! important "Important"
    The `deleted_at` field in the database must be nullable.

#### $allowedFields

This array should be updated with the field names that can be set during `save()`, `insert()`, `update()`, or `replace()` methods. Any field names other than these will be discarded. This helps to protect against just taking input from a form and throwing it all at the model, resulting in potential mass assignment vulnerabilities.

!!! note "Note"
    The [$primaryKey](#primarykey) field should never be an allowed field.

#### $allowEmptyInserts

!!! success "Available from version 4.3.0"

Whether to allow inserting empty data. The default value is `false`, meaning that if you try to insert empty data, a `DataException` with "There is no data to insert." will be raised.

You may also change this setting with the [allowEmptyInserts()](#allowemptyinserts_1) method.

#### $updateOnlyChanged

!!! success "Available from version 4.5.0"

Whether to update only the changed fields of an [Entity](entities.md). The default value is `true`, meaning that only changed field data is used when updating the database. So if you try to update an Entity without changes, a `DataException` with "There is no data to update." will be raised.

Setting this property to `false` will ensure that all allowed fields of an Entity are submitted to the database and updated at any time.

#### $casts

!!! success "Available from version 4.5.0"

This allows you to convert data retrieved from a database into the appropriate PHP type. This option should be an array where the key is the name of the field, and the value is the data type. See [Model Field Casting](#model-field-casting) for details.

#### Dates

##### $useTimestamps

This boolean value determines whether the current date is automatically added to all inserts and updates. If `true`, it will set the current time in the format specified by [$dateFormat](#dateformat). This requires that the table have columns named **created_at**, **updated_at** and **deleted_at** in the appropriate data type. See also [$createdField](#createdfield), [$updatedField](#updatedfield), and [$deletedField](#deletedfield).

##### $dateFormat

This value works with [$useTimestamps](#usetimestamps) and [$useSoftDeletes](#usesoftdeletes) to ensure that the correct type of date value gets inserted into the database. By default, this creates DATETIME values, but valid options are: `'datetime'`, `'date'`, or `'int'` (a UNIX timestamp). Using [$useSoftDeletes](#usesoftdeletes) or [$useTimestamps](#usetimestamps) with an invalid or missing [$dateFormat](#dateformat) will cause an exception.

##### $createdField

Specifies which database field to use for the data record's create timestamp. Set to an empty string (`''`) to avoid updating it (even if [$useTimestamps](#usetimestamps) is enabled).

##### $updatedField

Specifies which database field to use for the data record's update timestamp. Set to an empty string (`''`) to avoid updating it (even if [$useTimestamps](#usetimestamps) is enabled).

##### $deletedField

Specifies which database field to use for soft deletions. See [$useSoftDeletes](#usesoftdeletes).

#### Validation

##### $validationRules

Contains either an array of validation rules as described in [How to Save Your Rules](../libraries/validation.md#how-to-save-your-rules) or a string containing the name of a validation group, as described in the same section. See also [Setting Validation Rules](#setting-validation-rules).

##### $validationMessages

Contains an array of custom error messages that should be used during validation, as described in [Setting Custom Error Messages](../libraries/validation.md#setting-custom-error-messages). See also [Setting Validation Rules](#setting-validation-rules).

##### $skipValidation

Whether validation should be skipped during all **inserts** and **updates**. The default value is `false`, meaning that validation will always be attempted. This is primarily used by the `skipValidation()` method, but may be changed to `true` so this model will never validate.

##### $cleanValidationRules

Whether validation rules for fields missing from the passed data should be removed. This is used in **updates**. The default value is `true`, meaning that validation rules for the fields that are not present in the passed data will be (temporarily) removed before the validation. This is to avoid validation errors when updating only some fields.

You can also change the value with the `cleanRules()` method.

!!! note "Note"
    Prior to v4.2.7, `$cleanValidationRules` did not work due to a bug.

#### Callbacks

##### $allowCallbacks

Whether the callbacks defined below should be used. See [Model Events](#model-events).

##### $beforeInsert

##### $afterInsert

##### $beforeUpdate

##### $afterUpdate

##### $beforeFind

##### $afterFind

##### $beforeDelete

##### $afterDelete

##### $beforeInsertBatch

##### $afterInsertBatch

##### $beforeUpdateBatch

##### $afterUpdateBatch

These arrays allow you to specify callback methods that will be run on the data at the time specified in the property name. See [Model Events](#model-events).

## Model Field Casting

!!! success "Available from version 4.5.0"

When retrieving data from a database, data of integer type may be converted to string type in PHP. You may also want to convert date/time data into a Time object in PHP.

Model Field Casting allows you to convert data retrieved from a database into the appropriate PHP type.

!!! important "Important"
    If you use this feature with the [Entity](entities.md), do not use [Entity Property Casting](entities.md#property-casting). Using both at the same time does not work.

    Entity Property Casting works at (1)(4), but this casting works at (2)(3):

    ```
    [App Code] --- (1) --> [Entity] --- (2) --> [Database]
    [App Code] <-- (4) --- [Entity] <-- (3) --- [Database]
    ```

    When using this casting, the Entity will have correctly typed PHP values in the attributes. This behavior is completely different from the previous behavior. Do not expect the attributes to hold raw data from the database.

### Defining Data Types

The `$casts` property sets its definition. This option should be an array where the key is the name of the field, and the value is the data type:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // ...
    protected array $casts = [
        'id'        => 'int',
        'birthdate' => '?datetime',
        'hobbies'   => 'json-array',
        'active'    => 'int-bool',
    ];
    // ...
}
```

### Data Types

The following types are provided by default. Add a question mark at the beginning of the type to mark the field as nullable, i.e., `?int`, `?datetime`.

| Type         | PHP Value Type | DB Column Type            |
|--------------|----------------|---------------------------|
| `int`        | int            | int type                  |
| `float`      | float          | float (numeric) type      |
| `bool`       | bool           | bool/int/string type      |
| `int-bool`   | bool           | int type (1 or 0)         |
| `array`      | array          | string type (serialized)  |
| `csv`        | array          | string type (CSV)         |
| `json`       | stdClass       | json/string type          |
| `json-array` | array          | json/string type          |
| `datetime`   | Time           | datetime type             |
| `timestamp`  | Time           | int type (UNIX timestamp) |
| `uri`        | URI            | string type               |
| `enum`       | Enum           | string/int type           |

#### csv

Casting as `csv` uses PHP's internal `implode()` and `explode()` functions and assumes all values are string-safe and free of commas. For more complex data casts try `array` or `json`.

#### datetime

You can pass a parameter like `datetime[ms]` for date/time with milliseconds, or `datetime[us]` for date/time with microseconds.

The datetime format is set in the `dateFormat` array of the [database configuration](../database/configuration.md#description-of-values) in the **app/Config/Database.php** file.

!!! note "Note"
    When you set `ms` or `us` as a parameter, **Model** takes care of the fractional seconds of the Time. But **Query Builder** does not. So you still need to use the `format()` method when you pass the Time to Query Builder's methods like `where()`:

    ```php
    $model = model('SomeModel');

    $now = \CodeIgniter\I18n\Time::now();

    // The following code passes the microseconds to Query Builder.
    $model->where('my_dt_field', $now->format('Y-m-d H:i:s.u'))->findAll();
    // Generates: SELECT * FROM `my_table` WHERE `my_dt_field` = '2024-07-28 18:57:58.900326'

    // But the following code loses the microseconds.
    $model->where('my_dt_field', $now)->findAll();
    // Generates: SELECT * FROM `my_table` WHERE `my_dt_field` = '2024-07-28 18:57:58'
    ```

!!! note "Note"
    Prior to v4.6.0, you could not use `ms` or `us` as a parameter, because the fractional seconds of Time were lost due to bugs.

#### timestamp

The timezone of the `Time` instance created will be the default timezone (app's timezone), not UTC.

#### enum

!!! success "Available from version 4.7.0"

You can cast fields to PHP enums. You must specify the enum class name as a parameter, like `enum[App\Enums\StatusEnum]`.

Enum casting supports:

- **Backed enums** (string or int) - The backing value is stored in the database
- **Unit enums** - The case name is stored in the database as a string

### Custom Casting

You can define your own conversion types.

#### Creating Custom Handlers

First, you need to create a handler class for your type. Let's say the class will be located in the **app/Models/Cast** directory:

```php
<?php

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;
use InvalidArgumentException;

// The class must inherit the CodeIgniter\DataCaster\Cast\BaseCast class
class CastBase64 extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): string {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Cannot decode: ' . $value);
        }

        return $decoded;
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): string {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return base64_encode($value);
    }
}
```

If you don't need to change values when getting or setting a value, just don't implement the appropriate method:

```php
<?php

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;
use InvalidArgumentException;

class CastBase64 extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): string {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Cannot decode: ' . $value);
        }

        return $decoded;
    }
}
```

#### Registering Custom Handlers

Now you need to register it:

```php
<?php

namespace App\Models;

use App\Models\Cast\CastBase64;
use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    // Specify the type for the field
    protected array $casts = [
        'column1' => 'base64',
    ];

    // Bind the type to the handler
    protected array $castHandlers = [
        'base64' => CastBase64::class,
    ];

    // ...
}
```

#### Parameters

In some cases, one type is not enough. In this situation, you can use additional parameters. Additional parameters are indicated in square brackets and listed with a comma like `type[param1, param2]`.

```php
<?php

namespace App\Models;

use App\Models\Cast\SomeHandler;
use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    // Define a type with parameters
    protected array $casts = [
        'column1' => 'class[App\SomeClass, param2, param3]',
    ];

    // Bind the type to the handler
    protected array $castHandlers = [
        'class' => SomeHandler::class,
    ];

    // ...
}
```

```php
<?php

namespace App\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;

class SomeHandler extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): mixed {
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

## Working with Data

### Finding Data

Several functions are provided for doing basic CRUD work on your tables, including `find()`, `insert()`, `update()`, `delete()` and more.

#### find()

Returns a single row where the primary key matches the value passed in as the first parameter:

```php
<?php

$user = $userModel->find($userId);
```

The value is returned in the format specified in [$returnType](#returntype).

You can specify more than one row to return by passing an array of primaryKey values instead of just one:

```php
<?php

$users = $userModel->find([1, 2, 3]);
```

!!! note "Note"
    If `find()` is called without parameters or with `null`, it will return all rows in that model's table, effectively acting like `findAll()`, though less explicit.

#### findColumn()

Returns null or an indexed array of column values:

```php
<?php

$user = $userModel->findColumn($columnName);
```

`$columnName` should be the name of a single column. Otherwise, you will get a `DataException`.

#### findAll()

Returns all results:

```php
<?php

$users = $userModel->findAll();
```

This query may be modified by interjecting Query Builder commands as needed prior to calling this method:

```php
<?php

$users = $userModel->where('active', 1)->findAll();
```

You can pass in limit and offset values as the first and second parameters, respectively:

```php
<?php

$users = $userModel->findAll($limit, $offset);
```

#### first()

Returns the first row in the result set. This is best used in combination with the query builder.

```php
<?php

$user = $userModel->where('deleted', 0)->first();
```

#### withDeleted()

If [$useSoftDeletes](#usesoftdeletes) is true, then the **find\*()** methods will not return any rows where `deleted_at IS NOT NULL`. To temporarily override this, you can use the `withDeleted()` method prior to calling the **find\*()** method.

```php
<?php

// Only gets non-deleted rows (deleted = 0)
$activeUsers = $userModel->findAll();

// Gets all rows
$allUsers = $userModel->withDeleted()->findAll();
```

#### onlyDeleted()

Whereas `withDeleted()` will return both deleted and not-deleted rows, this method modifies the next **find\*()** methods to return only soft deleted rows:

```php
<?php

$deletedUsers = $userModel->onlyDeleted()->findAll();
```

### Saving Data

#### insert()

The first parameter is an associative array of data to create a new row of data in the database. If an object is passed instead of an array, it will attempt to convert it to an array.

The array's keys must match the name of the columns in the [$table](#table), while the array's values are the values to save for that key.

The optional second parameter is of type boolean, and if it is set to false, the method will return a boolean value, which indicates the success or failure of the query.

You can retrieve the last inserted row's primary key using the `getInsertID()` method.

```php
<?php

$data = [
    'username' => 'darth',
    'email'    => 'd.vader@theempire.com',
];

// Inserts data and returns inserted row's primary key
$userModel->insert($data);

// Inserts data and returns true on success and false on failure
$userModel->insert($data, false);

// Returns inserted row's primary key
$userModel->getInsertID();
```

#### allowEmptyInserts()

!!! success "Available from version 4.3.0"

You can use the `allowEmptyInserts()` method to insert empty data. By default, the Model throws an exception when you try to insert empty data. But if you call this method, the check will no longer be performed.

```php
<?php

$userModel->allowEmptyInserts()->insert([]);
```

You may also change this setting with the [$allowEmptyInserts](#allowemptyinserts) property.

You can enable the check again by calling `allowEmptyInserts(false)`.

#### update()

Updates an existing record in the database. The first parameter is the [$primaryKey](#primarykey) of the record to update. An associative array of data is passed into this method as the second parameter. The array's keys must match the name of the columns in the [$table](#table), while the array's values are the values to save for that key:

```php
<?php

$data = [
    'username' => 'darth',
    'email'    => 'd.vader@theempire.com',
];

$userModel->update($id, $data);
```

!!! important "Important"
    Since v4.3.0, this method raises a `DatabaseException` if it generates an SQL statement without a WHERE clause. In previous versions, if it was called without [$primaryKey](#primarykey) specified and an SQL statement was generated without a WHERE clause, the query would still execute and all records in the table would be updated.

Multiple records may be updated with a single call by passing an array of primary keys as the first parameter:

```php
<?php

$data = [
    'active' => 1,
];

$userModel->update([1, 2, 3], $data);
```

When you need a more flexible solution, you can leave the parameters empty, and it functions like the Query Builder's update command, with the added benefit of validation, events, etc:

```php
<?php

$userModel
    ->whereIn('id', [1, 2, 3])
    ->set(['active' => 1])
    ->update();
```

#### replace()

Pass an associative array to `replace($data)` to perform the Query Builder's `CodeIgniter\Database\BaseBuilder::replace()` operation. The model validates the supplied data and handles timestamps, but does not trigger model callbacks.

Since v4.7.6, fields outside [$allowedFields](#allowedfields) are discarded when field protection is enabled, except for a supplied [$primaryKey](#primarykey). Supplied timestamp values not listed in [$allowedFields](#allowedfields) are discarded and, when timestamps are enabled, replaced by generated ones. Pass `true` as the second argument to return the SQL instead of executing it.

If field protection removes all data from a non-empty input, or leaves only its primary key after removing other fields, `replace()` throws a `DataException` before generating timestamps. Explicitly passing `null` or an empty array still allows the method to use data previously set on the Query Builder.

!!! warning "Warning"
    Field protection does not authorize access to a record. A supplied primary key is retained even for auto-incrementing keys, so untrusted input can select the record to replace. Use a trusted identifier and verify that the caller is authorized to modify the target record; do not rely on `$allowedFields` alone when passing request data to `replace()`.

!!! important "Important"
    Field protection filters input; it does not guarantee that existing values of omitted columns are preserved. Depending on the database driver, replacement may delete the existing row and insert a new one, using defaults for omitted columns. Use `update()` to change selected fields of an existing row.

Fields used to match existing rows must be in `$allowedFields` or be the primary key. Filtering a unique column can change whether a replacement matches an existing row. With PostgreSQL, the first remaining field is used for matching; ensure that it is a primary or unique key to avoid matching multiple rows.

#### save()

This is a wrapper around the `insert()` and `update()` methods that handles inserting or updating the record automatically, based on whether it finds an array key matching the **primary key** value:

```php
<?php

// Defined as a model property
$primaryKey = 'id';

// Does an insert()
$data = [
    'username' => 'darth',
    'email'    => 'd.vader@theempire.com',
];

$userModel->save($data);

// Performs an update, since the primary key, 'id', is found.
$data = [
    'id'       => 3,
    'username' => 'darth',
    'email'    => 'd.vader@theempire.com',
];
$userModel->save($data);
```

The save method can also make working with custom class result objects much simpler by recognizing a non-simple object and grabbing its public and protected values into an array, which is then passed to the appropriate insert or update method. This allows you to work with Entity classes in a very clean way. Entity classes are simple classes that represent a single instance of an object type, like a user, a blog post, a job, etc. This class is responsible for maintaining the business logic surrounding the object itself, like formatting elements in a certain way, etc. They shouldn't have any idea about how they are saved to the database. At their simplest, they might look like this:

```php
<?php

namespace App\Entities;

class Job
{
    protected $id;
    protected $name;
    protected $description;

    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }
    }

    public function __set($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
        }
    }
}
```

A very simple model to work with this might look like:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table         = 'jobs';
    protected $returnType    = \App\Entities\Job::class;
    protected $allowedFields = [
        'name', 'description',
    ];
}
```

This model works with data from the `jobs` table, and returns all results as an instance of `App\Entities\Job`. When you need to persist that record to the database, you will need to either write custom methods, or use the model's `save()` method to inspect the class, grab any public and protected properties, and save them to the database:

```php
<?php

// Retrieve a Job instance
$job = $model->find(15);

// Make some changes
$job->name = 'Foobar';

// Save the changes
$model->save($job);
```

!!! note "Note"
    If you find yourself working with Entities a lot, CodeIgniter provides a built-in [Entity class](entities.md) that provides several handy features that make developing Entities simpler.

#### Saving Dates

!!! success "Available from version 4.5.0"

When saving data, if you pass [Time](../libraries/time.md) instances, they are converted to strings with the format defined in `dateFormat['datetime']` and `dateFormat['date']` in the [database configuration](../database/configuration.md#description-of-values).

!!! note "Note"
    Prior to v4.5.0, the date/time formats were hard coded as `Y-m-d H:i:s` and `Y-m-d` in the Model class.

#### Primary Key Validation

!!! success "Available from version 4.7.0"

The `insert()`, `insertBatch()` (when [$useAutoIncrement](#useautoincrement) is `false`), `update()`, and `delete()` methods validate primary key values before executing database queries. Invalid values such as `null`, `0`, `'0'`, empty strings, booleans, empty arrays, or nested arrays will throw an `InvalidArgumentException` with a specific error message.

If you need to customize this behavior (e.g., to allow `0` as a valid primary key for legacy systems), you can override the `validateID()` method in your model.

### Deleting Data

#### delete()

Takes a primary key value as the first parameter and deletes the matching record from the model's table:

```php
<?php

$userModel->delete(12);
```

If the model's [$useSoftDeletes](#usesoftdeletes) value is true, this will update the row to set `deleted_at` to the current date and time. You can force a permanent delete by setting the second parameter as true.

An array of primary keys can be passed in as the first parameter to delete multiple records at once:

```php
<?php

$userModel->delete([1, 2, 3]);
```

If no parameters are passed in, it will act like the Query Builder's delete method, requiring a prior where call:

```php
<?php

$userModel->where('id', 12)->delete();
```

#### purgeDeleted()

Cleans out the database table by permanently removing all rows that have 'deleted_at IS NOT NULL'.

```php
<?php

$userModel->purgeDeleted();
```

### In-Model Validation

!!! warning "Warning"
    In-Model validation is performed just before data is stored in the database. Prior to that point, the data has not yet been validated. Processing user-input data prior to validation may introduce vulnerabilities.

#### Validating Data

The Model class provides a way to automatically have all data validated prior to saving to the database with the `insert()`, `update()`, or `save()` methods.

!!! important "Important"
    When you update data, by default, the validation in the model class only validates provided fields. This is to avoid validation errors when updating only some fields.

    However, this means that not all validation rules you set will be checked during updates. Thus, incomplete data may pass the validation.

    For example, `required*` rules or the `is_unique` rule, which require the values of other fields, may not work as expected.

    To avoid such glitches, this behavior can be changed by configuration. See [$cleanValidationRules](#cleanvalidationrules) for details.

#### Setting Validation Rules

The first step is to fill out the [$validationRules](#validationrules) class property with the fields and rules that should be applied.

!!! note "Note"
    You can see the list of built-in Validation rules in [Available Rules](../libraries/validation.md#available-rules).

If you have custom error messages that you want to use, place them in the [$validationMessages](#validationmessages) array:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // ...

    protected $validationRules = [
        'username'     => 'required|max_length[30]|alpha_numeric_space|min_length[3]',
        'email'        => 'required|max_length[254]|valid_email|is_unique[users.email]',
        'password'     => 'required|max_length[255]|min_length[8]',
        'pass_confirm' => 'required_with[password]|max_length[255]|matches[password]',
    ];
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Sorry. That email has already been taken. Please choose another.',
        ],
    ];
}
```

If you'd rather organize your rules and error messages within the [Validation Config File](../libraries/validation.md#saving-sets-of-validation-rules-to-the-config-file), you can do that and simply set [$validationRules](#validationrules) to the name of the validation rule group you created:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // ...

    protected $validationRules = 'users';
}
```

The other way to set the validation rules for fields is by using functions:

## CodeIgniter

### Model

#### setValidationRule($field, $fieldRules)

- **Parameters**  
    - **$field** `string`
    - **$fieldRules** `array`

This function will set the field validation rules.

Usage example:

```php
<?php

$fieldName  = 'username';
$fieldRules = 'required|max_length[30]|alpha_numeric_space|min_length[3]';

$model->setValidationRule($fieldName, $fieldRules);
```

#### setValidationRules($validationRules)

- **Parameters**  
    - **$validationRules** `array`

This function will set the validation rules.

Usage example:

```php
<?php

$validationRules = [
    'username' => 'required|max_length[30]|alpha_numeric_space|min_length[3]',
    'email'    => [
        'rules'  => 'required|max_length[254]|valid_email|is_unique[users.email]',
        'errors' => [
            'required' => 'We really need your email.',
        ],
    ],
];
$model->setValidationRules($validationRules);
```

The other way to set the validation messages for fields is by using functions:

#### setValidationMessage($field, $fieldMessages)

- **Parameters**  
    - **$field** `string`
    - **$fieldMessages** `array`

This function will set the field-wise error messages.

Usage example:

```php
<?php

$fieldName              = 'name';
$fieldValidationMessage = [
    'required' => 'Your name is required here',
];
$model->setValidationMessage($fieldName, $fieldValidationMessage);
```

#### setValidationMessages($validationMessages)

- **Parameters**  
    - **$validationMessages** `array`

This function will set the field messages.

Usage example:

```php
<?php

$fieldValidationMessage = [
    'name' => [
        'required'   => 'Your baby name is missing.',
        'min_length' => 'Too short, man!',
    ],
];
$model->setValidationMessages($fieldValidationMessage);
```

#### Getting Validation Result

Now, whenever you call the `insert()`, `update()`, or `save()` methods, the data will be validated. If it fails, the model will return boolean **false**.

#### Getting Validation Errors

You can use the `errors()` method to retrieve the validation errors:

```php
<?php

if ($model->save($data) === false) {
    return view('updateUser', ['errors' => $model->errors()]);
}
```

This returns an array with the field names and their associated errors that can be used to either show all of the errors at the top of the form, or to display them individually:

```php
<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
    <?php foreach ($errors as $field => $error): ?>
        <p><?= esc($error) ?></p>
    <?php endforeach ?>
    </div>
<?php endif ?>
```

#### Retrieving Validation Rules

You can retrieve a model's validation rules by accessing its `validationRules` property:

```php
<?php

$rules = $model->validationRules;
```

You can also retrieve just a subset of those rules by calling the accessor method directly, with options:

```php
<?php

$rules = $model->getValidationRules($options);
```

The `$options` parameter is an associative array with one element, whose key is either `'except'` or `'only'`, and which has as its value an array of field names of interest:

```php
<?php

// get the rules for all but the "username" field
$rules = $model->getValidationRules(['except' => ['username']]);
// get the rules for only the "city" and "state" fields
$rules = $model->getValidationRules(['only' => ['city', 'state']]);
```

#### Validation Placeholders

The model provides a simple method to replace parts of your rules based on data that's being passed into it. This sounds fairly obscure but can be especially handy with the `is_unique` validation rule. Placeholders are simply the name of the field (or array key) that was passed in as `$data` surrounded by curly brackets. It will be replaced by the **value** of the matched incoming field. An example should clarify this:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    protected $validationRules = [
        'id'    => 'max_length[19]|is_natural_no_zero',
        'email' => 'required|max_length[254]|valid_email|is_unique[users.email,id,{id}]',
    ];
}
```

!!! note "Note"
    Since v4.3.5, you must set the validation rules for the placeholder field (`id`).

This set of rules states that the email address should be unique in the database, except for the row that has an id matching the placeholder's value. Assuming that the form POST data had the following:

```php
<?php

$_POST = [
    'id'    => 4,
    'email' => 'foo@example.com',
];
```

then the `{id}` placeholder would be replaced with the number **4**, giving this revised rule:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    protected $validationRules = [
        'id'    => 'max_length[19]|is_natural_no_zero',
        'email' => 'required|max_length[254]|valid_email|is_unique[users.email,id,4]',
    ];
}
```

So it will ignore the row in the database that has `id=4` when it verifies the email is unique.

!!! note "Note"
    Since v4.3.5, if the placeholder (`id`) value does not pass the validation, the placeholder will not be replaced.

This can also be used to create more dynamic rules at runtime, as long as you take care that any dynamic keys passed in don't conflict with your form data.

### Protecting Fields

To help protect against Mass Assignment Attacks, the Model class **requires** that you list all of the field names that can be changed during inserts, updates, and replacements in the [$allowedFields](#allowedfields) class property. Any data provided in addition to these will be removed prior to hitting the database. This limits which fields can be assigned through model methods.

For `replace()`, a supplied [$primaryKey](#primarykey) is retained to identify the record to replace.

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    protected $allowedFields = ['name', 'email', 'address'];
}
```

Occasionally, you will find times where you need to be able to change these elements. This is often during testing, migrations, or seeds. In these cases, you can turn the protection on or off:

```php
<?php

$model->protect(false)
    ->insert($data)
    ->protect(true);
```

### Runtime Return Type Changes

You can specify the format that data should be returned as when using the **find\*()** methods as the class property, [$returnType](#returntype). There may be times that you would like the data back in a different format, though. The Model provides methods that allow you to do just that.

!!! note "Note"
    These methods only change the return type for the next **find\*()** method call. After that, it is reset to its default value.

#### asArray()

Returns data from the next **find\*()** method as associative arrays:

```php
<?php

$users = $userModel->asArray()->where('status', 'active')->findAll();
```

#### asObject()

Returns data from the next **find\*()** method as standard objects or custom class instances:

```php
<?php

// Return as standard objects
$users = $userModel->asObject()->where('status', 'active')->findAll();

// Return as custom class instances
$users = $userModel->asObject('User')->where('status', 'active')->findAll();
```

### Processing Large Amounts of Data

Sometimes, you need to process large amounts of data and would run the risk of running out of memory. To make this simpler, you may use the `chunk()` method to get smaller chunks of data that you can then do your work on. The first parameter is the number of rows to retrieve in a single chunk. The second parameter is a Closure that will be called for each row of data.

This is best used during cron jobs, data exports, or other large tasks.

```php
<?php

$userModel->chunk(100, static function ($data) {
    // do something.
    // $data is a single row of data.
});
```

## Working with Query Builder

### Getting Query Builder for the Model's Table

CodeIgniter Model has one instance of the Query Builder for that model's database connection. You can get access to the **shared** instance of the Query Builder any time you need it:

```php
<?php

$builder = $userModel->builder();
```

This builder is already set up with the model's [$table](#table).

!!! note "Note"
    Once you get the Query Builder instance, you can call methods of the [Query Builder](../database/query_builder.md). However, since Query Builder is not a Model, you cannot call methods of the Model.

### Getting Query Builder for Another Table

If you need access to another table, you can get another instance of the Query Builder. Pass the table name in as a parameter, but be aware that this will **not** return a shared instance:

```php
<?php

$groupBuilder = $userModel->builder('groups');
```

### Mixing Methods of Query Builder and Model

You can also use Query Builder methods and the Model's CRUD methods in the same chained call, allowing for very elegant use:

```php
<?php

$users = $userModel->where('status', 'active')
    ->orderBy('last_login', 'asc')
    ->findAll();
```

In this case, it operates on the shared instance of the Query Builder held by the model.

!!! important "Important"
    The Model does not provide a perfect interface to the Query Builder. The Model and the Query Builder are separate classes with different purposes. They should not be expected to return the same data.

If the Query Builder returns a result, it is returned as is. In that case, the result may be different from the one returned by the model's method and may not be what was expected. The model's events are not triggered.

To prevent unexpected behavior, do not use Query Builder methods that return results and specify the model's method at the end of the method chaining.

!!! note "Note"
    You can also access the model's database connection seamlessly:

    ```php
    <?php

    $userName = $userModel->escape($name);
    ```

## Model Events

There are several points within the model's execution at which you can specify multiple callback methods to run. These methods can be used to normalize data, hash passwords, save related entities, and much more.

The following points in the model's execution can be affected, each through a class property:

- [$beforeInsert](#beforeinsert), [$afterInsert](#afterinsert)
- [$beforeUpdate](#beforeupdate), [$afterUpdate](#afterupdate)
- [$beforeFind](#beforefind), [$afterFind](#afterfind)
- [$beforeDelete](#beforedelete), [$afterDelete](#afterdelete)
- [$beforeInsertBatch](#beforeinsertbatch), [$afterInsertBatch](#afterinsertbatch)
- [$beforeUpdateBatch](#beforeupdatebatch), [$afterUpdateBatch](#afterupdatebatch)

!!! note "Note"
    `$beforeInsertBatch`, `$afterInsertBatch`, `$beforeUpdateBatch` and `$afterUpdateBatch` can be used since v4.3.0.

### Defining Callbacks

You specify the callbacks by first creating a new class method in your model to use.

This class method will always receive a `$data` array as its only parameter.

The exact contents of the `$data` array will vary between events, but will always contain a key named `data` that contains the primary data passed to the original method. In the case of the **insert\*()** or **update\*()** methods, that will be the key/value pairs that are being inserted into the database. The main `$data` array will also contain the other values passed to the method, as detailed in [Event Parameters](#event-parameters).

The callback method must return the original `$data` array so other callbacks have the full information.

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    protected function hashPassword(array $data)
    {
        if (! isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password']);

        return $data;
    }
}
```

### Specifying Callbacks To Run

You specify when to run the callbacks by adding the method name to the appropriate class property ([$beforeInsert](#beforeinsert), [$afterUpdate](#afterupdate), etc.). Multiple callbacks can be added to a single event, and they will be processed one after the other. You can use the same callback in multiple events:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    // ...
}
```

Additionally, each model may allow (default) or deny callbacks class-wide by setting its [$allowCallbacks](#allowcallbacks) property:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    protected $allowCallbacks = false;

    // ...
}
```

You may also change this setting temporarily for a single model call using the `allowCallbacks()` method:

```php
<?php

$model->allowCallbacks(false)->find(1); // No callbacks triggered
$model->find(1); // Callbacks subject to original property value
```

### Event Parameters

Since the exact data passed to each callback varies a bit, here are the details on what is in the `$data` parameter passed to each event:

| Event | $data contents |
|----|----|
| beforeInsert | **data** = the key/value pairs that are being inserted. If an object or Entity class is passed to the `insert()` method, it is first converted to an array. |
| afterInsert | **id** = the primary key of the new row, or 0 on failure. **data** = the key/value pairs being inserted. **result** = the results of the `insert()` method used through the Query Builder. |
| beforeUpdate | **id** = the array of primary keys of the rows being passed to the `update()` method. **data** = the key/value pairs that are being updated. If an object or Entity class is passed to the `update()` method, it is first converted to an array. |
| afterUpdate | **id** = the array of primary keys of the rows being passed to the `update()` method. **data** = the key/value pairs being updated. **result** = the results of the `update()` method used through the Query Builder. |
| beforeFind | The name of the calling **method**, whether a **singleton** was requested, and these additional fields: none for `first()`, **id** = the primary key of the row being searched for, for `find()`, and **limit** = the number of rows to find, and **offset** = the number of rows to skip during the search, for `findAll()`. |
| afterFind | Same as **beforeFind** but including the resulting row(s) of data, or null if no result found. |
| beforeDelete | **id** = an array of primary key rows being passed to the `delete()` method. **purge** = boolean whether soft-deleted rows should be hard deleted. |
| afterDelete | **id** = an array of primary key rows being passed to the `delete()` method. **purge** = boolean whether soft-deleted rows should be hard deleted. **result** = the result of the `delete()` call on the Query Builder. **data** = unused. |
| beforeInsertBatch | **data** = associative array of values that are being inserted. If an object or Entity class is passed to the `insertBatch()` method, it is first converted to an array. |
| afterInsertBatch | **data** = the associative array of values being inserted. **result** = the results of the `insertBatch()` method used through the Query Builder. |
| beforeUpdateBatch | **data** = associative array of values that are being updated. If an object or Entity class is passed to the `updateBatch()` method, it is first converted to an array. |
| afterUpdateBatch | **data** = the key/value pairs being updated. **result** = the results of the `updateBatch()` method used through the Query Builder. |

!!! note "Note"
    When using the `paginate()` method in combination with the `beforeFind` event to modify the query, the results may not behave as expected.

    This is because the `beforeFind` event only affects the actual retrieval of the results (`findAll()`), but **not** the query used to count the total number of rows for pagination.

    As a result, the total row count used for generating pagination links may not reflect the modified query conditions, leading to inconsistencies in pagination.

### Modifying Find\* Data

The `beforeFind` and `afterFind` methods can both return a modified set of data to override the normal response from the model. For `afterFind`, any changes made to `data` in the return array will automatically be passed back to the calling context. In order for `beforeFind` to intercept the find workflow, it must also return an additional boolean, `returnData`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    // ...

    protected $beforeFind = ['checkCache'];

    // ...

    protected function checkCache(array $data)
    {
        // Check if the requested item is already in our cache
        if (isset($data['id']) && $item = $this->getCachedItem($data['id'])) {
            $data['data']       = $item;
            $data['returnData'] = true;

            return $data;
        }

        // ...
    }
}
```

## Manual Model Creation

You do not need to extend any special class to create a model for your application. All you need is to get an instance of the database connection and you're good to go. This allows you to bypass the features CodeIgniter's Model gives you out of the box, and create a fully custom experience.

```php
<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class UserModel
{
    protected $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }
}
```
