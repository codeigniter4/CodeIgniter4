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

namespace CodeIgniter\Helpers\Array;

use InvalidArgumentException;

/**
 * @interal This is internal implementation for the framework.
 *
 * If there are any methods that should be provided, make them
 * public APIs via helper functions.
 *
 * @see \CodeIgniter\Helpers\Array\ArrayHelperDotKeyExistsTest
 * @see \CodeIgniter\Helpers\Array\ArrayHelperRecursiveDiffTest
 * @see \CodeIgniter\Helpers\Array\ArrayHelperSortValuesByNaturalTest
 */
final class ArrayHelper
{
    /**
     * Searches an array through dot syntax. Supports wildcard searches,
     * like `foo.*.bar`.
     *
     * @used-by dot_array_search()
     *
     * @param string $index The index as dot array syntax.
     *
     * @return array|bool|int|object|string|null
     */
    public static function dotSearch(string $index, array $array)
    {
        return self::arraySearchDot(self::convertToArray($index), $array);
    }

    /**
     * @param string $index The index as dot array syntax.
     *
     * @return list<string> The index as an array.
     */
    private static function convertToArray(string $index): array
    {
        // See https://regex101.com/r/44Ipql/1
        $segments = preg_split(
            '/(?<!\\\\)\./',
            rtrim($index, '* '),
            0,
            PREG_SPLIT_NO_EMPTY
        );

        return array_map(
            static fn ($key) => str_replace('\.', '.', $key),
            $segments
        );
    }

    /**
     * Recursively search the array with wildcards.
     *
     * @used-by dotSearch()
     *
     * @return array|bool|float|int|object|string|null
     */
    private static function arraySearchDot(array $indexes, array $array)
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

                $answer[] = self::arraySearchDot($indexes, $value);
            }

            $answer = array_filter($answer, static fn ($value) => $value !== null);

            if ($answer !== []) {
                // If array only has one element, we return that element for BC.
                return count($answer) === 1 ? current($answer) : $answer;
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
            return self::arraySearchDot($indexes, $array[$currentIndex]);
        }

        // Otherwise, not found.
        return null;
    }

    /**
     * array_key_exists() with dot array syntax.
     *
     * If wildcard `*` is used, all items for the key after it must have the key.
     */
    public static function dotKeyExists(string $index, array $array): bool
    {
        if (str_ends_with($index, '*') || str_contains($index, '*.*')) {
            throw new InvalidArgumentException(
                'You must set key right after "*". Invalid index: "' . $index . '"'
            );
        }

        $indexes = self::convertToArray($index);

        // If indexes is empty, returns false.
        if ($indexes === []) {
            return false;
        }

        $currentArray = $array;

        // Grab the current index
        while ($currentIndex = array_shift($indexes)) {
            if ($currentIndex === '*') {
                $currentIndex = array_shift($indexes);

                foreach ($currentArray as $item) {
                    if (! array_key_exists($currentIndex, $item)) {
                        return false;
                    }
                }

                // If indexes is empty, all elements are checked.
                if ($indexes === []) {
                    return true;
                }

                $currentArray = self::dotSearch('*.' . $currentIndex, $currentArray);

                continue;
            }

            if (! array_key_exists($currentIndex, $currentArray)) {
                return false;
            }

            $currentArray = $currentArray[$currentIndex];
        }

        return true;
    }

    /**
     * Groups all rows by their index values. Result's depth equals number of indexes
     *
     * @used-by array_group_by()
     *
     * @param array $array        Data array (i.e. from query result)
     * @param array $indexes      Indexes to group by. Dot syntax used. Returns $array if empty
     * @param bool  $includeEmpty If true, null and '' are also added as valid keys to group
     *
     * @return array Result array where rows are grouped together by indexes values.
     */
    public static function groupBy(array $array, array $indexes, bool $includeEmpty = false): array
    {
        if ($indexes === []) {
            return $array;
        }

        $result = [];

        foreach ($array as $row) {
            $result = self::arrayAttachIndexedValue($result, $row, $indexes, $includeEmpty);
        }

        return $result;
    }

    /**
     * Recursively attach $row to the $indexes path of values found by
     * `dot_array_search()`.
     *
     * @used-by groupBy()
     */
    private static function arrayAttachIndexedValue(
        array $result,
        array $row,
        array $indexes,
        bool $includeEmpty
    ): array {
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

        $result[$value] = self::arrayAttachIndexedValue($result[$value], $row, $indexes, $includeEmpty);

        return $result;
    }

    /**
     * Compare recursively two associative arrays and return difference as new array.
     * Returns keys that exist in `$original` but not in `$compareWith`.
     */
    public static function recursiveDiff(array $original, array $compareWith): array
    {
        $difference = [];

        if ($original === []) {
            return [];
        }

        if ($compareWith === []) {
            return $original;
        }

        foreach ($original as $originalKey => $originalValue) {
            if ($originalValue === []) {
                continue;
            }

            if (is_array($originalValue)) {
                $diffArrays = [];

                if (isset($compareWith[$originalKey]) && is_array($compareWith[$originalKey])) {
                    $diffArrays = self::recursiveDiff($originalValue, $compareWith[$originalKey]);
                } else {
                    $difference[$originalKey] = $originalValue;
                }

                if ($diffArrays !== []) {
                    $difference[$originalKey] = $diffArrays;
                }
            } elseif (is_string($originalValue) && ! array_key_exists($originalKey, $compareWith)) {
                $difference[$originalKey] = $originalValue;
            }
        }

        return $difference;
    }

    /**
     * Recursively count all keys.
     */
    public static function recursiveCount(array $array, int $counter = 0): int
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                $counter = self::recursiveCount($value, $counter);
            }

            $counter++;
        }

        return $counter;
    }

    /**
     * Sorts array values in natural order
     * If the value is an array, you need to specify the $sortByIndex of the key to sort
     *
     * @param list<int|list<int|string>|string> $array
     * @param int|string|null                   $sortByIndex
     */
    public static function sortValuesByNatural(array &$array, $sortByIndex = null): bool
    {
        return usort($array, static function ($currentValue, $nextValue) use ($sortByIndex): int {
            if ($sortByIndex !== null) {
                return strnatcmp((string) $currentValue[$sortByIndex], (string) $nextValue[$sortByIndex]);
            }

            return strnatcmp((string) $currentValue, (string) $nextValue);
        });
    }
}
