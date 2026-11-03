# Generating Test Data

Often you will need sample data for your application to run its tests. The `Fabricator` class uses [Faker](https://github.com/FakerPHP/Faker) to turn models into generators of random data. Use fabricators in your seeds or test cases to stage fake data for your unit tests.

## Supported Models

`Fabricator` supports any model that extends the framework's core model, `CodeIgniter\Model`. You may use your own custom models by ensuring they implement `CodeIgniter\Test\Interfaces\FabricatorModel`:

```php
<?php

namespace App\Models;

use CodeIgniter\Test\Interfaces\FabricatorModel;

class MyModel implements FabricatorModel
{
    public function find($id = null)
    {
        // TODO: Implement find() method.
    }

    public function insert($row = null, bool $returnID = true)
    {
        // TODO: Implement insert() method.
    }

    // ...
}
```

!!! note "Note"
    In addition to methods, the interface outlines some necessary properties for the target model. Please see the interface code for details.

## Loading Fabricators

At its most basic, a fabricator takes the model to act on:

```php
<?php

use App\Models\UserModel;
use CodeIgniter\Test\Fabricator;

$fabricator = new Fabricator(UserModel::class);
```

The parameter can be a string specifying the name of the model, or an instance of the model itself:

```php
<?php

use App\Models\UserModel;
use CodeIgniter\Test\Fabricator;

$model = new UserModel($testDbConnection);

$fabricator = new Fabricator($model);
```

## Defining Formatters

Faker generates data by requesting it from a formatter. With no formatters defined, `Fabricator` will attempt to guess at the most appropriate fit based on the field name and properties of the model it represents, falling back on `$fabricator->defaultFormatter`. This may be fine if your field names correspond with common formatters, or if you don't care much about the content of the fields, but most of the time you will want to specify the formatters to use as the second parameter to the constructor:

```php
<?php

use App\Models\UserModel;
use CodeIgniter\Test\Fabricator;

$formatters = [
    'first'  => 'firstName',
    'email'  => 'email',
    'phone'  => 'phoneNumber',
    'avatar' => 'imageUrl',
];

$fabricator = new Fabricator(UserModel::class, $formatters);
```

You can also change the formatters after a fabricator is initialized by using the `setFormatters()` method.

### Advanced Formatting

Sometimes the default return of a formatter is not enough. Faker providers allow parameters to most formatters to further limit the scope of random data. A fabricator will check its representative model for the `fake()` method where you can define exactly what the faked data should look like:

```php
<?php

namespace App\Models;

use Faker\Generator;

class UserModel
{
    // ...

    public function fake(Generator &$faker)
    {
        return [
            'first'  => $faker->firstName(),
            'email'  => $faker->email(),
            'phone'  => $faker->phoneNumber(),
            'avatar' => \Faker\Provider\Image::imageUrl(800, 400),
            'login'  => config('Auth')->allowRemembering ? date('Y-m-d') : null,
        ];

        /*
         * Or you can return a return type object.

        return new User([
            'first'  => $faker->firstName(),
            'email'  => $faker->email(),
            'phone'  => $faker->phoneNumber(),
            'avatar' => \Faker\Provider\Image::imageUrl(800, 400),
            'login'  => config('Auth')->allowRemembering ? date('Y-m-d') : null,
        ]);

        */
    }
}
```

Notice in this example how the first three values are equivalent to the formatters from before. However, for `avatar` we have requested an image size other than the default and `login` uses a conditional based on app configuration, neither of which is possible using the `$formatters` parameter.

You may want to keep your test data separate from your production models, so it is a good practice to define a child class in your test support folder:

```php
<?php

namespace Tests\Support\Models;

use App\Models\UserModel;
use Faker\Generator;

class UserFabricator extends UserModel
{
    public function fake(Generator &$faker)
    {
        // ...
    }
}
```

## Setting Modifiers

!!! success "Available from version 4.5.0"

Faker provides three special providers, `unique()`, `optional()`, and `valid()`, to be called before any provider. Fabricator fully supports these modifiers by providing dedicated methods.

```php
<?php

use App\Models\UserModel;
use CodeIgniter\Test\Fabricator;

$fabricator = new Fabricator(UserModel::class);
$fabricator->setUnique('email'); // sets generated emails to be always unique
$fabricator->setOptional('group_id'); // sets group id to be optional, with 50% chance to be `null`
$fabricator->setValid('age', static fn (int $age): bool => $age >= 18); // sets age to be 18 and above only

$users = $fabricator->make(10);
```

The arguments passed after the field name are passed directly to the modifiers as-is. You can refer to [Faker's documentation on modifiers](https://fakerphp.github.io/#modifiers) for details.

Instead of calling each method on Fabricator, you may use Faker's modifiers directly if you are using the `fake()` method on your models.

```php
<?php

namespace App\Models;

use CodeIgniter\Test\Fabricator;
use Faker\Generator;

class UserModel
{
    protected $table = 'users';

    public function fake(Generator &$faker)
    {
        return [
            'first'    => $faker->firstName(),
            'email'    => $faker->unique()->email(),
            'group_id' => $faker->optional()->passthrough(mt_rand(1, Fabricator::getCount('groups'))),
        ];
    }
}
```

## Localization

Faker supports a lot of different locales. Check their documentation to determine which providers support your locale. Specify a locale in the third parameter while initiating a fabricator:

```php
<?php

use App\Models\UserModel;
use CodeIgniter\Test\Fabricator;

$fabricator = new Fabricator(UserModel::class, null, 'fr_FR');
```

If no locale is specified, it will use the one defined in **app/Config/App.php** as `defaultLocale`. You can check the locale of an existing fabricator using its `getLocale()` method.

## Faking the Data

Once you have a properly initialized fabricator, it is easy to generate test data with the `make()` command:

```php
<?php

use CodeIgniter\Test\Fabricator;
use Tests\Support\Models\UserFabricator;

$fabricator = new Fabricator(UserFabricator::class);
$testUser   = $fabricator->make();
print_r($testUser);
```

You might get back something like this:

```php
<?php

[
    'first'  => 'Maynard',
    'email'  => 'king.alford@example.org',
    'phone'  => '201-886-0269 x3767',
    'avatar' => 'http://lorempixel.com/800/400/',
    'login'  => null,
];
```

You can also get a lot of them back by supplying a count:

```php
<?php

$users = $fabricator->make(10);
```

The return type of `make()` mimics what is defined in the representative model, but you can force a type using the methods directly:

```php
<?php

$userArray  = $fabricator->makeArray();
$userObject = $fabricator->makeObject();
$userEntity = $fabricator->makeObject('App\Entities\User');
```

The return from `make()` is ready to be used in tests or inserted into the database. Alternatively, `Fabricator` includes the `create()` command to insert it for you and return the result. Due to model callbacks, database formatting, and special keys like primary and timestamps, the return from `create()` can differ from `make()`. You might get back something like this:

```php
<?php

[
    'id'         => 1,
    'first'      => 'Rachel',
    'email'      => 'bradley72@gmail.com',
    'phone'      => '741-241-2356',
    'avatar'     => 'http://lorempixel.com/800/400/',
    'login'      => null,
    'created_at' => '2020-05-08 14:52:10',
    'updated_at' => '2020-05-08 14:52:10',
];
```

Similar to `make()`, you can supply a count to insert and return an array of objects:

```php
<?php

$users = $fabricator->create(100);
```

Finally, there may be times you want to test with the full database object but you are not actually using a database. `create()` takes a second parameter to allow mocking the object, returning the object with extra database fields above without actually touching the database:

```php
<?php

$user = $fabricator(null, true);

$this->assertIsNumeric($user->id);
$this->dontSeeInDatabase('user', ['id' => $user->id]);
```

## Specifying Test Data

Generated data is great, but sometimes you may want to supply a specific field for a test without compromising your formatters configuration. Rather than creating a new fabricator for each variant, you can use `setOverrides()` to specify the value for any fields:

```php
<?php

$fabricator->setOverrides(['first' => 'Bobby']);
$bobbyUser = $fabricator->make();
```

Now any data generated with `make()` or `create()` will always use "Bobby" for the `first` field:

```php
<?php

[
    'first'  => 'Bobby',
    'email'  => 'latta.kindel@company.org',
    'phone'  => '251-806-2169',
    'avatar' => 'http://lorempixel.com/800/400/',
    'login'  => null,
];

[
    'first'  => 'Bobby',
    'email'  => 'melissa.strike@fabricon.us',
    'phone'  => '525-214-2656 x23546',
    'avatar' => 'http://lorempixel.com/800/400/',
    'login'  => null,
];
```

`setOverrides()` can take a second parameter to indicate whether this should be a persistent override or only for a single action:

```php
<?php

$fabricator->setOverrides(['first' => 'Bobby'], $persist = false);
$bobbyUser = $fabricator->make();
$bobbyUser = $fabricator->make();
```

Notice that after the first return, the fabricator stops using the overrides:

```php
<?php

[
    'first'  => 'Bobby',
    'email'  => 'belingadon142@example.org',
    'phone'  => '741-857-1933 x1351',
    'avatar' => 'http://lorempixel.com/800/400/',
    'login'  => null,
];

[
    'first'  => 'Hans',
    'email'  => 'hoppifur@metraxalon.com',
    'phone'  => '487-235-7006',
    'avatar' => 'http://lorempixel.com/800/400/',
    'login'  => null,
];
```

If no second parameter is supplied then passed values will persist by default.

## Test Helper

Often all you will need is a one-and-done fake object for testing. The Test Helper provides the `fake($model, $overrides, $persist = true)` function to do just this:

```php
<?php

helper('test');
$user = fake('App\Models\UserModel', ['name' => 'Gerry']);
```

This is equivalent to:

```php
<?php

use CodeIgniter\Test\Fabricator;

$fabricator = new Fabricator('App\Models\UserModel');
$fabricator->setOverrides(['name' => 'Gerry']);
$user = $fabricator->create();
```

If you just need a fake object without saving it to the database, you can pass false into the persist parameter.

## Table Counts

Frequently your faked data will depend on other faked data. `Fabricator` provides a static count of the number of faked items you have created for each table. Consider the following example:

Your project has users and groups. In your test case you want to create various scenarios with groups of different sizes, so you use `Fabricator` to create a bunch of groups. Now you want to create fake users but don't want to assign them to a non-existent group ID. Your model's fake method could look like this:

```php
<?php

namespace App\Models;

use CodeIgniter\Test\Fabricator;
use Faker\Generator;

class UserModel
{
    protected $table = 'users';

    public function fake(Generator &$faker)
    {
        return [
            'first'    => $faker->firstName(),
            'email'    => $faker->email(),
            'group_id' => mt_rand(1, Fabricator::getCount('groups')),
        ];
    }
}
```

Now creating a new user will ensure it is a part of a valid group: `$user = fake(UserModel::class);`

### Methods

`Fabricator` handles the counts internally, but you can also access these static methods to assist with using them:

#### getCount(string $table): int

Return the current value for a specific table (default: 0).

#### setCount(string $table, int $count): int

Set the value for a specific table manually, for example if you create some test items without using a fabricator that you still wanted factored into the final counts.

#### upCount(string $table): int

Increment the value for a specific table by one and return the new value. (This is what is used internally with `Fabricator::create()`.)

#### downCount(string $table): int

Decrement the value for a specific table by one and return the new value, for example if you deleted a fake item but wanted to track the change.

#### resetCounts()

Resets all counts. It is a good idea to call this between test cases (though using `CIUnitTestCase::$refresh = true` does it automatically).
