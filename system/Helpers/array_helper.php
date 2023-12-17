<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// CodeIgniter Array Helpers

if (! function_exists('dot_array_search')) {
    /**
     * Searches an array through dot syntax. Supports
     * wildcard searches, like foo.*.bar
     *
     * @return array|bool|int|object|string|null
     */
    function dot_array_search(string $index, array $array)
    {
        // See https://regex101.com/r/44Ipql/1
        $segments = preg_split(
            '/(?<!\\\\)\./',
            rtrim($index, '* '),
            0,
            PREG_SPLIT_NO_EMPTY
        );

        $segments = array_map(static fn ($key) => str_replace('\.', '.', $key), $segments);

        return _array_search_dot($segments, $array);
    }
}

if (! function_exists('_array_search_dot')) {
    /**
     * Used by `dot_array_search` to recursively search the
     * array with wildcards.
     *
     * @internal This should not be used on its own.
     *
     * @return array|bool|float|int|object|string|null
     */
    function _array_search_dot(array $indexes, array $array)
    {
        // If index is empty, returns null.
        if ($indexes === []) {
            return null;
        }

        // Grab the current index
        $currentIndex = array_shift($indexes);

        if (! isset($array[$currentIndex]) && $currentIndex !== '*') {
            return null;
        }

        // Handle Wildcard (*)
        if ($currentIndex === '*') {
            $answer = [];

            foreach ($array as $value) {
                if (! is_array($value)) {
                    return null;
                }

                $answer[] = _array_search_dot($indexes, $value);
            }

            $answer = array_filter($answer, static fn ($value) => $value !== null);

            if ($answer !== []) {
                if (count($answer) === 1) {
                    // If array only has one element, we return that element for BC.
                    return current($answer);
                }

                return $answer;
            }

            return null;
        }

        // If this is the last index, make sure to return it now,
        // and not try to recurse through things.
        if ($indexes === []) {
            return $array[$currentIndex];
        }

        // Do we need to recursively search this value?
        if (is_array($array[$currentIndex]) && $array[$currentIndex] !== []) {
            return _array_search_dot($indexes, $array[$currentIndex]);
        }

        // Otherwise, not found.
        return null;
    }
}

if (! function_exists('array_deep_search')) {
    /**
     * Returns the value of an element at a key in an array of uncertain depth.
     *
     * @param int|string $key
     *
     * @return array|bool|float|int|object|string|null
     */
    function array_deep_search($key, array $array)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach ($array as $value) {
            if (is_array($value) && ($result = array_deep_search($key, $value))) {
                return $result;
            }
        }

        return null;
    }
}

if (! function_exists('array_sort_by_multiple_keys')) {
    /**
     * Sorts a multidimensional array by its elements values. The array
     * columns to be used for sorting are passed as an associative
     * array of key names and sorting flags.
     *
     * Both arrays of objects and arrays of array can be sorted.
     *
     * Example:
     *     array_sort_by_multiple_keys($players, [
     *         'team.hierarchy' => SORT_ASC,
     *         'position'       => SORT_ASC,
     *         'name'           => SORT_STRING,
     *     ]);
     *
     * The '.' dot operator in the column name indicates a deeper array or
     * object level. In principle, any number of sublevels could be used,
     * as long as the level and column exist in every array element.
     *
     * For information on multi-level array sorting, refer to Example #3 here:
     * https://www.php.net/manual/de/function.array-multisort.php
     *
     * @param array $array       the reference of the array to be sorted
     * @param array $sortColumns an associative array of columns to sort
     *                           after and their sorting flags
     */
    function array_sort_by_multiple_keys(array &$array, array $sortColumns): bool
    {
        // Check if there really are columns to sort after
        if ($sortColumns === [] || $array === []) {
            return false;
        }

        // Group sorting indexes and data
        $tempArray = [];

        foreach ($sortColumns as $key => $sortFlag) {
            // Get sorting values
            $carry = $array;

            // The '.' operator separates nested elements
            foreach (explode('.', $key) as $keySegment) {
                // Loop elements if they are objects
                if (is_object(reset($carry))) {
                    // Extract the object attribute
                    foreach ($carry as $index => $object) {
                        $carry[$index] = $object->{$keySegment};
                    }

                    continue;
                }

                // Extract the target column if elements are arrays
                $carry = array_column($carry, $keySegment);
            }

            // Store the collected sorting parameters
            $tempArray[] = $carry;
            $tempArray[] = $sortFlag;
        }

        // Append the array as reference
        $tempArray[] = &$array;

        // Pass sorting arrays and flags as an argument list.
        return array_multisort(...$tempArray);
    }
}

if (! function_exists('array_flatten_with_dots')) {
    /**
     * Flatten a multidimensional array using dots as separators.
     *
     * @param iterable $array The multi-dimensional array
     * @param string   $id    Something to initially prepend to the flattened keys
     *
     * @return array The flattened array
     */
    function array_flatten_with_dots(iterable $array, string $id = ''): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            $newKey = $id . $key;

            if (is_array($value) && $value !== []) {
                $flattened = array_merge($flattened, array_flatten_with_dots($value, $newKey . '.'));
            } else {
                $flattened[$newKey] = $value;
            }
        }

        return $flattened;
    }
}

if (! function_exists('array_group_by')) {
    /**
     * Groups all rows by their index values. Result's depth equals number of indexes
     *
     * @param array $array        Data array (i.e. from query result)
     * @param array $indexes      Indexes to group by. Dot syntax used. Returns $array if empty
     * @param bool  $includeEmpty If true, null and '' are also added as valid keys to group
     *
     * @return array Result array where rows are grouped together by indexes values.
     */
    function array_group_by(array $array, array $indexes, bool $includeEmpty = false): array
    {
        if ($indexes === []) {
            return $array;
        }

        $result = [];

        foreach ($array as $row) {
            $result = _array_attach_indexed_value($result, $row, $indexes, $includeEmpty);
        }

        return $result;
    }
}

if (! function_exists('_array_attach_indexed_value')) {
    /**
     * Used by `array_group_by` to recursively attach $row to the $indexes path of values found by
     * `dot_array_search`
     *
     * @internal This should not be used on its own
     */
    function _array_attach_indexed_value(array $result, array $row, array $indexes, bool $includeEmpty): array
    {
        if (($index = array_shift($indexes)) === null) {
            $result[] = $row;

            return $result;
        }

        $value = dot_array_search($index, $row);

        if (! is_scalar($value)) {
            $value = '';
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        if (! $includeEmpty && $value === '') {
            return $result;
        }

        if (! array_key_exists($value, $result)) {
            $result[$value] = [];
        }

        $result[$value] = _array_attach_indexed_value($result[$value], $row, $indexes, $includeEmpty);

        return $result;
    }
}
