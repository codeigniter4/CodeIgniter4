# Array Helper

The array helper provides several functions to simplify more complex usages of arrays. It is not intended to duplicate any of the existing functionality that PHP provides, unless it is to vastly simplify its usage.

## Loading this Helper

This helper is loaded using the following code:

```php
helper('array');
```

## Available Functions

The following functions are available:

!!! note "Note"
    Since v4.8.0, the dot-path helpers can read values from arrays or objects, including `Entity` objects. This applies to `dot_array_search()`, `dot_array_has()`, `dot_array_only()`, `dot_array_except()`, and `array_group_by()`.

    `dot_array_set()` and `dot_array_unset()` still modify arrays only. `dot_array_only()` and `dot_array_except()` always return arrays. See their descriptions for how object values are handled.

#### dot_array_search(string $search, array|object $values)

- **Parameters**  
    - **$search** `string` The dot-notation string describing how to search the array
    - **$values** `array|object` The array or object to search

- **Returns**: The value found within the array, or null

- **Return type**: `mixed`

This method allows you to use dot-notation to search through arrays and objects for a specific key or property, and allows the use of the `*` wildcard. Given the following array:

```php
$data = [
    'foo' => [
        'buzz' => [
            'fizz' => 11,
        ],
        'bar' => [
            'baz' => 23,
        ],
    ],
];
```

We can locate the value of `fizz` by using the search string `foo.buzz.fizz`. Likewise, the value of `baz` can be found with `foo.bar.baz`:

```php
// Returns: 11
$fizz = dot_array_search('foo.buzz.fizz', $data);

// Returns: 23
$baz = dot_array_search('foo.bar.baz', $data);
```

You can use the asterisk (`*`) as a wildcard to replace any of the segments. When it reaches one, it will search through all of the child nodes until it finds the value. This is handy if you don't know the values, or if your values have a numeric index:

```php
// Returns: 23
$baz = dot_array_search('foo.*.baz', $data);
```

If the array key contains a dot (`.`), then the key can be escaped with a backslash (`\`):

```php
$data = [
    'foo' => [
        'bar.baz' => 23,
    ],
    'foo.bar' => [
        'baz' => 43,
    ],
];

// Returns: 23
$barBaz = dot_array_search('foo.bar\.baz', $data);
// Returns: 43
$fooBar = dot_array_search('foo\.bar.baz', $data);
```

!!! note "Note"
    Prior to v4.2.0, `dot_array_search('foo.bar.baz', ['foo' => ['bar' => 23]])` returned `23` due to a bug. v4.2.0 and later return `null`.

#### dot_array_has(string $search, array|object $values): bool

- **Parameters**  
    - **$search** `string` The dot-notation string describing how to search the array
    - **$values** `array|object` The array or object to check

- **Returns**: `true` if the key exists, otherwise `false`

- **Return type**: `bool`

!!! success "Available from version 4.8.0"

Checks if an array key exists using dot syntax. This method supports wildcard `*` in the same way as `dot_array_search()`.

```php
$data = [
    'users' => [
        ['id' => 1, 'name' => 'Jane'],
        ['id' => 2, 'name' => 'John'],
    ],
];

// Returns: true (all matched users have an "id" key)
$hasIds = dot_array_has('users.*.id', $data);

// If any user is missing "id", this would return false.

// Returns: false
$hasEmails = dot_array_has('users.*.email', $data);
```

#### dot_array_set(array &$array, string $search, mixed $value): void

- **Parameters**  
    - **$array** `array` The array to modify (passed by reference)
    - **$search** `string` The dot-notation string describing where to set the value
    - **$value** `mixed` The value to set

- **Return type**: `void`

!!! success "Available from version 4.8.0"

Sets an array value using dot syntax. Missing path segments are created automatically. Wildcard `*` is supported with the same rule as `dot_array_has()`: you must specify a key right after `*`.

```php
$data = [];

dot_array_set($data, 'user.profile.id', 123);
dot_array_set($data, 'user.profile.name', 'John');

$users = [
    ['name' => 'Jane'],
    ['name' => 'John'],
];

dot_array_set($users, '*.active', true);

/*
$data is now:
[
    'user' => [
        'profile' => [
            'id'   => 123,
            'name' => 'John',
        ],
    ],
]
*/

/*
$users is now:
[
    ['name' => 'Jane', 'active' => true],
    ['name' => 'John', 'active' => true],
]
*/
```

#### dot_array_unset(array &$array, string $search): bool

- **Parameters**  
    - **$array** `array` The array to modify (passed by reference)
    - **$search** `string` The dot-notation string describing which key to remove

- **Returns**: `true` if a key was removed, otherwise `false`

- **Return type**: `bool`

!!! success "Available from version 4.8.0"

Removes array values using dot syntax. Wildcard `*` is supported. You can target specific keys like `users.*.id` or clear all keys under a path with `user.*`.

```php
$data = [
    'user' => [
        'profile' => [
            'id'   => 123,
            'name' => 'John',
        ],
    ],
];

// Returns: true
$removed = dot_array_unset($data, 'user.profile.id');

// Returns: false (path does not exist)
$removedAgain = dot_array_unset($data, 'user.profile.id');

$users = [
    ['id' => 1, 'name' => 'Jane'],
    ['id' => 2, 'name' => 'John'],
];

// Returns: true (removes "id" from all user rows)
$removedIds = dot_array_unset($users, '*.id');

// Returns: true (clears all keys under "user")
$clearedUser = dot_array_unset($data, 'user.*');
```

#### dot_array_only(array|object $array, array|string $indexes): array

- **Parameters**  
    - **$array** `array|object` The source array or object
    - **$indexes** `array|string` One key or a list of keys using dot notation

- **Returns**: Nested array containing only the requested keys

- **Return type**: `array`

!!! success "Available from version 4.8.0"

Gets only the specified keys using dot syntax while preserving nested structure.

Wildcard `*` is supported. Unlike `dot_array_set()` and `dot_array_unset()`, this method also allows wildcard at the end (for example `user.*`).

The result is always an array. If a selected value is an object, that object is kept as the value. If you select a value inside an object, the returned path is built with arrays:

```php
$user = (object) ['id' => 123, 'name' => 'John'];

// Selecting the object itself keeps the object as the value.
$whole = dot_array_only(['user' => $user], 'user');
// ['user' => $user]

// Selecting a value inside the object builds the path with arrays.
$partial = dot_array_only(['user' => $user], 'user.id');
// ['user' => ['id' => 123]]
```

```php
$data = [
    'user' => [
        'id'    => 123,
        'name'  => 'John',
        'email' => 'john@example.com',
    ],
    'meta' => [
        'request_id' => 'abc',
    ],
];

$only = dot_array_only($data, ['user.id', 'meta.request_id']);
/*
$only:
[
    'user' => ['id' => 123],
    'meta' => ['request_id' => 'abc'],
]
*/

$userOnly = dot_array_only($data, 'user.*');
/*
$userOnly:
[
    'user' => [
        'id'    => 123,
        'name'  => 'John',
        'email' => 'john@example.com',
    ],
]
*/
```

#### dot_array_except(array|object $array, array|string $indexes): array

- **Parameters**  
    - **$array** `array|object` The source array or object
    - **$indexes** `array|string` One key or a list of keys using dot notation

- **Returns**: Nested array with the specified keys removed

- **Return type**: `array`

!!! success "Available from version 4.8.0"

Gets all keys except the specified ones using dot syntax.

Wildcard `*` is supported. Unlike `dot_array_set()` and `dot_array_unset()`, this method also allows wildcard at the end (for example `user.*`).

The result is always an array. Object values that are not changed are kept as they are. If a key is removed from inside an object, that part of the result is returned as an array:

```php
$user = (object) ['id' => 123, 'name' => 'John'];

// An untouched object is kept as it is.
$untouched = dot_array_except(['user' => $user], 'meta.id');
// ['user' => $user]

// Removing a key from inside an object returns that part as an array.
$partial = dot_array_except(['user' => $user], 'user.id');
// ['user' => ['name' => 'John']]
```

```php
$data = [
    'user' => [
        'id'    => 123,
        'name'  => 'John',
        'email' => 'john@example.com',
    ],
    'meta' => [
        'request_id' => 'abc',
    ],
];

$except = dot_array_except($data, ['user.email', 'meta.request_id']);
/*
$except:
[
    'user' => [
        'id'   => 123,
        'name' => 'John',
    ],
    'meta' => [],
]
*/

$clearUser = dot_array_except($data, 'user.*');
/*
$clearUser:
[
    'user' => [],
    'meta' => ['request_id' => 'abc'],
]
*/
```

#### array_deep_search($key, array $array)

- **Parameters**  
    - **$key** `mixed` The target key
    - **$array** `array` The array to search

- **Returns**: The value found within the array, or null

- **Return type**: `mixed`

Returns the value of an element with a key value in an array of uncertain depth. Only nested arrays are searched, and object values are not traversed.

#### array_sort_by_multiple_keys(array &$array, array $sortColumns)

- **Parameters**  
    - **$array** `array` The array to be sorted (passed by reference).
    - **$sortColumns** `array` The array keys to sort by and the respective PHP sort flags as an associative array.

- **Returns**: Whether sorting was successful or not.

- **Return type**: `bool`

This method sorts the elements of a multidimensional array by the values of one or more keys in a hierarchical way. Take the following array, which might be returned from, e.g., the `find()` function of a model:

```php
$players = [
    0 => [
        'name'     => 'John',
        'team_id'  => 2,
        'position' => 3,
        'team'     => [
            'id'    => 1,
            'order' => 2,
        ],
    ],
    1 => [
        'name'     => 'Maria',
        'team_id'  => 5,
        'position' => 4,
        'team'     => [
            'id'    => 5,
            'order' => 1,
        ],
    ],
    2 => [
        'name'     => 'Frank',
        'team_id'  => 5,
        'position' => 1,
        'team'     => [
            'id'    => 5,
            'order' => 1,
        ],
    ],
];
```

Now sort this array by two keys. Note that the method supports dot-notation to access values in deeper array levels, but does not support wildcards:

```php
array_sort_by_multiple_keys($players, [
    'team.order' => SORT_ASC,
    'position'   => SORT_ASC,
]);
```

The `$players` array is now sorted by the `order` value in each player's `team` subarray. If this value is equal for several players, these players will be ordered by their `position`. The resulting array is:

```php
$players = [
    0 => [
        'name'     => 'Frank',
        'team_id'  => 5,
        'position' => 1,
        'team'     => [
            'id'    => 5,
            'order' => 1,
        ],
    ],
    1 => [
        'name'     => 'Maria',
        'team_id'  => 5,
        'position' => 4,
        'team'     => [
            'id'    => 5,
            'order' => 1,
        ],
    ],
    2 => [
        'name'     => 'John',
        'team_id'  => 2,
        'position' => 3,
        'team'     => [
            'id'    => 1,
            'order' => 2,
        ],
    ],
];
```

In the same way, the method can also handle an array of objects. In the example above, it is further possible that each `player` is represented by an array, while the `teams` are objects. The method will detect the type of elements in each nesting level and handle it accordingly.

#### array_flatten_with_dots(iterable $array[, string $id = '']): array

- **Parameters**  
    - **$array** `iterable` The multidimensional array to flatten
    - **$id** `string` Optional ID to prepend to the outer keys. Used internally for flattening keys.

- **Return type**: `array`

- **Returns**: The flattened array

This function flattens a multidimensional array to a single key-value array by using dots as separators for the keys. The source may be any `iterable`. Only nested arrays are flattened, and object values are kept as-is, as leaf values.

```php
$arrayToFlatten = [
    'personal' => [
        'first_name' => 'john',
        'last_name'  => 'smith',
        'age'        => '26',
        'address'    => 'US',
    ],
    'other_details' => 'marines officer',
];

$flattened = array_flatten_with_dots($arrayToFlatten);
```

On inspection, `$flattened` is equal to:

```php
[
    'personal.first_name' => 'john',
    'personal.last_name'  => 'smith',
    'personal.age'        => '26',
    'personal.address'    => 'US',
    'other_details'       => 'marines officer',
];
```

Users may use the `$id` parameter on their own, but are not required to do so. The function uses this parameter internally to track the flattened keys. If users supply an initial `$id`, it will be prepended to all keys.

```php
// using the same data from above
$flattened = array_flatten_with_dots($arrayToFlatten, 'foo_');
/*
 * $flattened is now:
 * [
 *     'foo_personal.first_name' => 'john',
 *     'foo_personal.last_name'  => 'smith',
 *     'foo_personal.age'        => '26',
 *     'foo_personal.address'    => 'US',
 *     'foo_other_details'       => 'marines officer',
 * ]
 */
```

#### array_group_by(array $array, array $indexes[, bool $includeEmpty = false]): array

- **Parameters**  
    - **$array** `array` Data rows (most likely from query results)
    - **$indexes** `array` Indexes to group values by. Follows dot syntax
    - **$includeEmpty** `bool` If true, `null` and `''` values are not filtered out

- **Return type**: `array`

- **Returns**: An array grouped by index values

This function allows you to group data rows together by index values. The depth of the returned array equals the number of indexes passed as a parameter. Data rows may be arrays or objects, and dot syntax can read nested array keys or object properties.

The example shows some data (e.g., loaded from an API) with nested arrays.

```php
$employees = [
    [
        'id'         => 1,
        'first_name' => 'Urbano',
        'gender'     => null,
        'hr'         => [
            'country'    => 'Canada',
            'department' => 'Engineering',
        ],
    ],
    [
        'id'         => 2,
        'first_name' => 'Case',
        'gender'     => 'Male',
        'hr'         => [
            'country'    => null,
            'department' => 'Marketing',
        ],
    ],
    [
        'id'         => 3,
        'first_name' => 'Emera',
        'gender'     => 'Female',
        'hr'         => [
            'country'    => 'France',
            'department' => 'Engineering',
        ],
    ],
    [
        'id'         => 4,
        'first_name' => 'Richy',
        'gender'     => null,
        'hr'         => [
            'country'    => null,
            'department' => 'Sales',
        ],
    ],
    [
        'id'         => 5,
        'first_name' => 'Mandy',
        'gender'     => null,
        'hr'         => [
            'country'    => 'France',
            'department' => 'Sales',
        ],
    ],
    [
        'id'         => 6,
        'first_name' => 'Risa',
        'gender'     => 'Female',
        'hr'         => [
            'country'    => null,
            'department' => 'Engineering',
        ],
    ],
    [
        'id'         => 7,
        'first_name' => 'Alfred',
        'gender'     => 'Male',
        'hr'         => [
            'country'    => 'France',
            'department' => 'Engineering',
        ],
    ],
    [
        'id'         => 8,
        'first_name' => 'Tabby',
        'gender'     => 'Male',
        'hr'         => [
            'country'    => 'France',
            'department' => 'Marketing',
        ],
    ],
    [
        'id'         => 9,
        'first_name' => 'Ario',
        'gender'     => 'Male',
        'hr'         => [
            'country'    => null,
            'department' => 'Sales',
        ],
    ],
    [
        'id'         => 10,
        'first_name' => 'Somerset',
        'gender'     => 'Male',
        'hr'         => [
            'country'    => 'Germany',
            'department' => 'Marketing',
        ],
    ],
];
```

We want to group them first by `gender`, then by `hr.department` (max depth = 2). First, here is the result when excluding empty values:

```php
$result = array_group_by($employees, ['gender', 'hr.department']);

$result = [
    'Male' => [
        'Marketing' => [
            [
                'id'         => 2,
                'first_name' => 'Case',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Marketing',
                ],
            ],
            [
                'id'         => 8,
                'first_name' => 'Tabby',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Marketing',
                ],
            ],
            [
                'id'         => 10,
                'first_name' => 'Somerset',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'Germany',
                    'department' => 'Marketing',
                ],
            ],
        ],
        'Engineering' => [
            [
                'id'         => 7,
                'first_name' => 'Alfred',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Engineering',
                ],
            ],
        ],
        'Sales' => [
            [
                'id'         => 9,
                'first_name' => 'Ario',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Sales',
                ],
            ],
        ],
    ],
    'Female' => [
        'Engineering' => [
            [
                'id'         => 3,
                'first_name' => 'Emera',
                'gender'     => 'Female',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Engineering',
                ],
            ],
            [
                'id'         => 6,
                'first_name' => 'Risa',
                'gender'     => 'Female',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Engineering',
                ],
            ],
        ],
    ],
];
```

And here is the same code, but this time we want to include empty values:

```php
$result = array_group_by($employees, ['gender', 'hr.department'], true);

$result = [
    '' => [
        'Engineering' => [
            [
                'id'         => 1,
                'first_name' => 'Urbano',
                'gender'     => null,
                'hr'         => [
                    'country'    => 'Canada',
                    'department' => 'Engineering',
                ],
            ],
        ],
        'Sales' => [
            [
                'id'         => 4,
                'first_name' => 'Richy',
                'gender'     => null,
                'hr'         => [
                    'country'    => null,
                    'department' => 'Sales',
                ],
            ],
            [
                'id'         => 5,
                'first_name' => 'Mandy',
                'gender'     => null,
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Sales',
                ],
            ],
        ],
    ],
    'Male' => [
        'Marketing' => [
            [
                'id'         => 2,
                'first_name' => 'Case',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Marketing',
                ],
            ],
            [
                'id'         => 8,
                'first_name' => 'Tabby',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Marketing',
                ],
            ],
            [
                'id'         => 10,
                'first_name' => 'Somerset',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'Germany',
                    'department' => 'Marketing',
                ],
            ],
        ],
        'Engineering' => [
            [
                'id'         => 7,
                'first_name' => 'Alfred',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Engineering',
                ],
            ],
        ],
        'Sales' => [
            [
                'id'         => 9,
                'first_name' => 'Ario',
                'gender'     => 'Male',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Sales',
                ],
            ],
        ],
    ],
    'Female' => [
        'Engineering' => [
            [
                'id'         => 3,
                'first_name' => 'Emera',
                'gender'     => 'Female',
                'hr'         => [
                    'country'    => 'France',
                    'department' => 'Engineering',
                ],
            ],
            [
                'id'         => 6,
                'first_name' => 'Risa',
                'gender'     => 'Female',
                'hr'         => [
                    'country'    => null,
                    'department' => 'Engineering',
                ],
            ],
        ],
    ],
];
```
