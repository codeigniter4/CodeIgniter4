<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Helpers\Array\ArrayHelper;

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
        return ArrayHelper::dotSearch($index, $array);
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
        return ArrayHelper::groupBy($array, $indexes, $includeEmpty);
    }
}
