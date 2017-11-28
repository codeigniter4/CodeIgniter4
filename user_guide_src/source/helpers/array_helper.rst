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
