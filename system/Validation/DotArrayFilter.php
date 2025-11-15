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

namespace CodeIgniter\Validation;

/**
 * @see \CodeIgniter\Validation\DotArrayFilterTest
 */
final class DotArrayFilter
{
    /**
     * Creates a new array with only the elements specified in dot array syntax.
     *
     * @param array $indexes The dot array syntax pattern to use for filtering.
     * @param array $array   The array to filter.
     *
     * @return array The filtered array.
     */
    public static function run(array $indexes, array $array): array
    {
        $result = [];

        foreach ($indexes as $index) {
            $segments = preg_split('/(?<!\\\\)\./', $index, -1, PREG_SPLIT_NO_EMPTY);
            $segments = array_map(static fn ($key): string => str_replace('\.', '.', $key), $segments);

            $filteredArray = self::filter($segments, $array);

            if ($filteredArray !== []) {
                $result = array_replace_recursive($result, $filteredArray);
            }
        }

        return $result;
    }

    /**
     * Used by `run()` to recursively filter the array with wildcards.
     *
     * @param array $indexes The dot array syntax pattern to use for filtering.
     * @param array $array   The array to filter.
     *
     * @return array The filtered array.
     */
    private static function filter(array $indexes, array $array): array
    {
        // If there are no indexes left, return an empty array
        if ($indexes === []) {
            return [];
        }

        // Get the current index
        $currentIndex = array_shift($indexes);

        // If the current index doesn't exist and is not a wildcard, return an empty array
        if (! isset($array[$currentIndex]) && $currentIndex !== '*') {
            return [];
        }

        // Handle the wildcard '*' at the current level
        if ($currentIndex === '*') {
            $result = [];

            // Iterate over all keys at this level
            foreach ($array as $key => $value) {
                if ($indexes === []) {
                    // If no indexes are left, capture the entire value
                    $result[$key] = $value;
                } elseif (is_array($value)) {
                    // If there are still indexes left, continue filtering recursively
                    $filtered = self::filter($indexes, $value);
                    if ($filtered !== []) {
                        $result[$key] = $filtered;
                    }
                }
            }

            return $result;
        }

        // If this is the last index, return the value
        if ($indexes === []) {
            return [$currentIndex => $array[$currentIndex] ?? []];
        }

        // If the current value is an array, recursively filter it
        if (is_array($array[$currentIndex])) {
            $filtered = self::filter($indexes, $array[$currentIndex]);

            if ($filtered !== []) {
                return [$currentIndex => $filtered];
            }
        }

        return [];
    }
}
