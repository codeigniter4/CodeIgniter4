# Array Helper

The array helper provides several functions to simplify more complex usages of arrays. It is not intended to duplicate any of the existing functionality that PHP provides, unless it is to vastly simplify its usage.

## Loading this Helper

This helper is loaded using the following code:

```php
helper('array');
```

## Available Functions

The following functions are available:

#### dot_array_search(string $search, array $values)

- **Parameters**  
    - **$search** `string` The dot-notation string describing how to search the array
    - **$values** `array` The array to search

- **Returns**: The value found within the array, or null

- **Return type**: `mixed`

This method allows you to use dot-notation to search through an array for a specific key, and allows the use of the `*` wildcard. Given the following array:

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

#### array_deep_search($key, array $array)

- **Parameters**  
    - **$key** `mixed` The target key
    - **$array** `array` The array to search

- **Returns**: The value found within the array, or null

- **Return type**: `mixed`

Returns the value of an element with a key value in an array of uncertain depth.

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

This function flattens a multidimensional array to a single key-value array by using dots as separators for the keys.

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

This function allows you to group data rows together by index values. The depth of the returned array equals the number of indexes passed as a parameter.

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
