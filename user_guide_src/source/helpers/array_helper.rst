############
Array Helper
############

The array helper provides several functions to simplify more complex usages of arrays. It is not intended to duplicate
any of the existing functionality that PHP provides - unless it is to vastly simplify their usage.

.. contents::
    :local:

Loading this Helper
===================

This helper is loaded using the following code::

    helper('array');

Available Functions
===================

The following functions are available:

..  php:function:: dot_array_search(string $search, array $values)

    :param  string  $search: The dot-notation string describing how to search the array
    :param  array   $values: The array to search
    :returns: The value found within the array, or null
    :rtype: mixed

    This method allows you to use dot-notation to search through an array for a specific-key,
    and allows the use of a the '*' wildcard. Given the following array::

        $data = [
            'foo' => [
                'buzz' => [
                    'fizz' => 11
                ],
                'bar' => [
                    'baz' => 23
                ]
            ]
        ]

    We can locate the value of 'fizz' by using the search string "foo.buzz.fizz". Likewise, the value
    of baz can be found with "foo.bar.baz"::

        // Returns: 11
        $fizz = dot_array_search('foo.buzz.fizz', $data);

        // Returns: 23
        $baz = dot_array_search('foo.bar.baz', $data);

    You can use the asterisk as a wildcard to replace any of the segments. When found, it will search through all
    of the child nodes until it finds it. This is handy if you don't know the values, or if your values
    have a numeric index::

        // Returns: 23
        $baz = dot_array_search('foo.*.baz', $data);

..  php:function:: array_deep_search($key, array $array)

    :param  mixed  $key: The target key
    :param  array  $array: The array to search
    :returns: The value found within the array, or null
    :rtype: mixed

    Returns the value of an element with a key value in an array of uncertain depth

..  php:function:: array_sort_by_multiple_keys(array &$array, array $sortColumns)

    :param  array  $array:       The array to be sorted (passed by reference).
    :param  array  $sortColumns: The array keys to sort after and the respective PHP
                                 sort flags as an associative array.
    :returns: Whether sorting was successful or not.
    :rtype: boolean

    This method sorts the elements of a multidimensional array by the values of one or
    more keys in a hierarchical way. Take the following array, that might be returned
    from, e.g., the ``find()`` function of a model::

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

    Now sort this array by two keys. Note that the method supports the dot-notation
    to access values in deeper array levels, but does not support wildcards::

        array_sort_by_multiple_keys($players,
            [
                'team.order' => SORT_ASC,
                'position'   => SORT_ASC,
            ]
        );

    The ``$players`` array is now sorted by the 'order' value in each players'
    'team' subarray. If this value is equal for several players, these players
    will be ordered by their 'position'. The resulting array is::

        $players = [
            0 => [
                'name'     => 'Frank',
                'team_id'  => 5,
                'position' => 1,
                'team'     => [
                    'id' => 5,
                    'order' => 1,
                ],
            ],
            1 => [
                'name'     => 'Maria',
                'team_id'  => 5,
                'position' => 4,
                'team'     => [
                    'id' => 5,
                    'order' => 1,
                ],
            ],
            2 => [
                'name'     => 'John',
                'team_id'  => 2,
                'position' => 3,
                'team'     => [
                    'id' => 1,
                    'order' => 2,
                ],
            ],
        ];

    In the same way, the method can also handle an array of objects. In the example
    above it is further possible that each 'player' is represented by an array,
    while the 'teams' are objects. The method will detect the type of elements in
    each nesting level and handle it accordingly.