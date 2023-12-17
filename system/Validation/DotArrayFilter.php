<?php

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
     * This code comes from the dot_array_search() function.
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
            // See https://regex101.com/r/44Ipql/1
            $segments = preg_split(
                '/(?<!\\\\)\./',
                rtrim($index, '* '),
                0,
                PREG_SPLIT_NO_EMPTY
            );

            $segments = array_map(
                static fn ($key) => str_replace('\.', '.', $key),
                $segments
            );

            $result = array_merge_recursive($result, self::filter($segments, $array));
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
        // If index is empty, returns empty array.
        if ($indexes === []) {
            return [];
        }

        // Grab the current index.
        $currentIndex = array_shift($indexes);

        if (! isset($array[$currentIndex]) && $currentIndex !== '*') {
            return [];
        }

        // Handle Wildcard (*)
        if ($currentIndex === '*') {
            $answer = [];

            foreach ($array as $key => $value) {
                if (! is_array($value)) {
                    continue;
                }

                $result = self::filter($indexes, $value);

                if ($result !== []) {
                    $answer[$key] = $result;
                }
            }

            return $answer;
        }

        // If this is the last index, make sure to return it now,
        // and not try to recurse through things.
        if ($indexes === []) {
            return [$currentIndex => $array[$currentIndex]];
        }

        // Do we need to recursively filter this value?
        if (is_array($array[$currentIndex]) && $array[$currentIndex] !== []) {
            $result = self::filter($indexes, $array[$currentIndex]);

            if ($result !== []) {
                return [$currentIndex => $result];
            }
        }

        // Otherwise, not found.
        return [];
    }
}
