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

use ArrayAccess;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Exceptions\InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * @internal This is internal implementation for the framework.
 *
 * If there are any methods that should be provided, make them
 * public APIs via helper functions.
 *
 * @see \CodeIgniter\Helpers\Array\ArrayHelperDotHasTest
 * @see \CodeIgniter\Helpers\Array\ArrayHelperDotModifyTest
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
     * @param string                         $index The index as dot array syntax.
     * @param array<array-key, mixed>|object $array
     *
     * @return mixed
     */
    public static function dotSearch(string $index, array|object $array)
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
        $trimmed = rtrim($index, '* ');

        if ($trimmed === '') {
            return [];
        }

        // Fast path: no escaped dots, skip the regex entirely.
        if (! str_contains($trimmed, '\\.')) {
            return array_values(array_filter(
                explode('.', $trimmed),
                static fn ($s): bool => $s !== '',
            ));
        }

        // See https://regex101.com/r/44Ipql/1
        $segments = preg_split('/(?<!\\\\)\./', $trimmed, 0, PREG_SPLIT_NO_EMPTY);

        return array_map(
            static fn ($key): string => str_replace('\.', '.', $key),
            $segments,
        );
    }

    /**
     * Recursively search the array with wildcards.
     *
     * @used-by dotSearch()
     *
     * @param list<string>                   $indexes
     * @param array<array-key, mixed>|object $array
     *
     * @return mixed
     */
    private static function arraySearchDot(array $indexes, array|object $array)
    {
        // If index is empty, returns null.
        if ($indexes === []) {
            return null;
        }

        // Grab the current index
        $currentIndex = array_shift($indexes);

        // Handle Wildcard (*)
        if ($currentIndex === '*') {
            $answer = [];

            foreach (self::entries($array) as $value) {
                if (! self::isNavigable($value)) {
                    return null;
                }

                $answer[] = self::arraySearchDot($indexes, $value);
            }

            $answer = array_filter($answer, static fn ($value): bool => $value !== null);

            if ($answer !== []) {
                // If array only has one element, we return that element for BC.
                return count($answer) === 1 ? current($answer) : $answer;
            }

            return null;
        }

        [$found, $value] = self::resolve($array, $currentIndex);

        if (! $found) {
            return null;
        }

        // If this is the last index, make sure to return it now,
        // and not try to recurse through things.
        if ($indexes === []) {
            return $value;
        }

        // Do we need to recursively search this value?
        if ((is_array($value) && $value !== []) || is_object($value)) {
            return self::arraySearchDot($indexes, $value);
        }

        // Otherwise, not found.
        return null;
    }

    /**
     * array_key_exists() with dot array syntax.
     *
     * If wildcard `*` is used, all items for the key after it must have the key.
     *
     * @param array<array-key, mixed>|object $array
     */
    public static function dotHas(string $index, array|object $array): bool
    {
        self::ensureValidWildcardPattern($index);

        $indexes = self::convertToArray($index);

        if ($indexes === []) {
            return false;
        }

        return self::hasByDotPath($array, $indexes);
    }

    /**
     * Recursively check key existence by dot path, including wildcard support.
     *
     * @param array<array-key, mixed>|object $array
     * @param list<string>                   $indexes
     */
    private static function hasByDotPath(array|object $array, array $indexes): bool
    {
        if ($indexes === []) {
            return true;
        }

        $currentIndex = array_shift($indexes);

        if ($currentIndex === '*') {
            foreach (self::entries($array) as $item) {
                if (! self::isNavigable($item) || ! self::hasByDotPath($item, $indexes)) {
                    return false;
                }
            }

            return true;
        }

        [$found, $value] = self::resolve($array, $currentIndex);

        if (! $found) {
            return false;
        }

        if ($indexes === []) {
            return true;
        }

        if (! self::isNavigable($value)) {
            return false;
        }

        return self::hasByDotPath($value, $indexes);
    }

    /**
     * Sets a value by dot array syntax.
     *
     * @param array<array-key, mixed> $array
     */
    public static function dotSet(array &$array, string $index, mixed $value): void
    {
        self::ensureValidWildcardPattern($index);

        $indexes = self::convertToArray($index);

        if ($indexes === []) {
            return;
        }

        self::setByDotPath($array, $indexes, $value);
    }

    /**
     * Removes a value by dot array syntax.
     *
     * @param array<array-key, mixed> $array
     */
    public static function dotUnset(array &$array, string $index): bool
    {
        self::ensureValidWildcardPattern($index, true);

        if ($index === '*') {
            return self::clearByDotPath($array, []) > 0;
        }

        $indexes = self::convertToArray($index);

        if ($indexes === []) {
            return false;
        }

        if (str_ends_with($index, '*')) {
            return self::clearByDotPath($array, $indexes) > 0;
        }

        return self::unsetByDotPath($array, $indexes) > 0;
    }

    /**
     * Gets only the specified keys using dot syntax.
     *
     * @param array<array-key, mixed>|object $array
     * @param list<string>|string            $indexes
     *
     * @return array<array-key, mixed>
     */
    public static function dotOnly(array|object $array, array|string $indexes): array
    {
        $indexes = is_string($indexes) ? [$indexes] : $indexes;
        $result  = [];

        foreach ($indexes as $index) {
            self::ensureValidWildcardPattern($index, true);

            if ($index === '*') {
                $result = [...$result, ...(is_object($array) ? self::toIterable($array) : $array)];

                continue;
            }

            $segments = self::convertToArray($index);
            if ($segments === []) {
                continue;
            }

            self::projectByDotPath($array, $segments, $result);
        }

        return $result;
    }

    /**
     * Gets all keys except the specified ones using dot syntax.
     *
     * @param array<array-key, mixed>|object $array
     * @param list<string>|string            $indexes
     *
     * @return array<array-key, mixed>
     */
    public static function dotExcept(array|object $array, array|string $indexes): array
    {
        $indexes = is_string($indexes) ? [$indexes] : $indexes;

        // Open only the root into an array view; nested values (including
        // objects) are preserved until a path actually descends into them.
        $result = self::entries($array);

        foreach ($indexes as $index) {
            self::ensureValidWildcardPattern($index, true);

            if ($index === '*') {
                $result = [];

                continue;
            }

            $segments = self::convertToArray($index);
            if ($segments === []) {
                continue;
            }

            if (str_ends_with($index, '*')) {
                self::excludeChildrenByDotPath($result, $segments);

                continue;
            }

            self::excludeByDotPath($result, $segments);
        }

        return $result;
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
     * dot syntax.
     *
     * @used-by groupBy()
     *
     * @param array<array-key, mixed>|object $row
     * @param list<string>                   $indexes
     */
    private static function arrayAttachIndexedValue(
        array $result,
        array|object $row,
        array $indexes,
        bool $includeEmpty,
    ): array {
        if (($index = array_shift($indexes)) === null) {
            $result[] = $row;

            return $result;
        }

        $value = self::dotSearch($index, $row);

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

    /**
     * Resolve a key against an array or object node, walking the access chain
     * (Entity, ArrayAccess, public properties, magic `__isset`/`__get`) once.
     *
     * @param array<array-key, mixed>|object $node
     *
     * @return array{bool, mixed} The pair [found, value].
     */
    private static function resolve(array|object $node, string $key): array
    {
        if (is_array($node)) {
            return array_key_exists($key, $node) ? [true, $node[$key]] : [false, null];
        }

        $array = self::entityToArray($node);

        if ($array !== null) {
            return array_key_exists($key, $array) ? [true, $array[$key]] : [false, null];
        }

        if ($node instanceof ArrayAccess && $node->offsetExists($key)) {
            return [true, $node->offsetGet($key)];
        }

        $properties = get_object_vars($node);

        if (array_key_exists($key, $properties)) {
            return [true, $properties[$key]];
        }

        return isset($node->{$key}) ? [true, $node->{$key}] : [false, null];
    }

    /**
     * Whether keys can be resolved from this value, i.e. it is an array or an
     * object that exposes a key surface: an expandable container, an
     * `ArrayAccess`, or one relying on magic `__get`. Pure value-objects
     * (e.g. `DateTimeImmutable`) are not navigable.
     *
     * Direct key lookup can support more object types than wildcard traversal:
     * `ArrayAccess` and magic-only objects can resolve `user.id`, but cannot be
     * enumerated for `user.*` unless they are also expandable.
     */
    private static function isNavigable(mixed $value): bool
    {
        if (is_array($value)) {
            return true;
        }

        return is_object($value)
            && (self::isExpandable($value)
                || $value instanceof ArrayAccess
                || method_exists($value, '__get'));
    }

    /**
     * Entries of an array or object node for wildcard traversal.
     *
     * @param array<array-key, mixed>|object $node
     *
     * @return array<array-key, mixed>
     */
    private static function entries(array|object $node): array
    {
        return is_object($node) ? self::toIterable($node) : $node;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private static function entityToArray(object $data): ?array
    {
        if ($data instanceof Entity) {
            return $data->toArray();
        }

        return null;
    }

    /**
     * Normalize an object to an array safe to iterate with foreach.
     *
     * Entities are converted via toArray() so internal properties like
     * `_options` or `_cast` are not exposed. Other Traversable objects are
     * converted to an array with their keys preserved; plain objects fall back
     * to their public properties.
     *
     * @return array<array-key, mixed>
     */
    private static function toIterable(object $data): array
    {
        $array = self::entityToArray($data);

        if ($array !== null) {
            return $array;
        }

        if ($data instanceof Traversable) {
            return iterator_to_array($data);
        }

        return get_object_vars($data);
    }

    /**
     * Whether an object should be expanded into an array when building output.
     *
     * Only enumerable containers are expanded: entities, `stdClass`, other
     * `Traversable` objects, and plain objects exposing public properties.
     * Opaque objects with no enumerable key surface (value-objects such as
     * `DateTimeImmutable`, magic-only or pure `ArrayAccess` objects) are
     * preserved as-is, since they cannot be faithfully rebuilt as an array.
     */
    private static function isExpandable(object $value): bool
    {
        return $value instanceof Entity
            || $value instanceof stdClass
            || $value instanceof Traversable
            || get_object_vars($value) !== [];
    }

    /**
     * Ensure a value can be descended into for a partial exclusion/projection.
     *
     * Arrays pass through; expandable objects are converted to an array view
     * in place (this is the only point where output structure is fabricated).
     * Anything else (scalars, value-objects, magic-only or pure `ArrayAccess`
     * objects) is left untouched and reported as non-descendable.
     */
    private static function expandForDescent(mixed &$value): bool
    {
        if (is_array($value)) {
            return true;
        }

        if (is_object($value) && self::isExpandable($value)) {
            $value = self::entries($value);

            return true;
        }

        return false;
    }

    /**
     * Throws exception for invalid wildcard patterns.
     */
    private static function ensureValidWildcardPattern(string $index, bool $allowTrailingWildcard = false): void
    {
        if ((! $allowTrailingWildcard && str_ends_with($index, '*')) || str_contains($index, '*.*')) {
            throw new InvalidArgumentException(
                'You must set key right after "*". Invalid index: "' . $index . '"',
            );
        }
    }

    /**
     * Set value recursively by dot path, including wildcard support.
     *
     * @param array<array-key, mixed> $array
     * @param list<string>            $indexes
     */
    private static function setByDotPath(array &$array, array $indexes, mixed $value): void
    {
        if ($indexes === []) {
            return;
        }

        $currentIndex = array_shift($indexes);

        if ($currentIndex === '*') {
            foreach ($array as &$item) {
                if (! is_array($item)) {
                    continue;
                }

                self::setByDotPath($item, $indexes, $value);
            }
            unset($item);

            return;
        }

        if ($indexes === []) {
            $array[$currentIndex] = $value;

            return;
        }

        if (! isset($array[$currentIndex]) || ! is_array($array[$currentIndex])) {
            $array[$currentIndex] = [];
        }

        self::setByDotPath($array[$currentIndex], $indexes, $value);
    }

    /**
     * Unset value recursively by dot path, including wildcard support.
     *
     * @param array<array-key, mixed> $array
     * @param list<string>            $indexes
     */
    private static function unsetByDotPath(array &$array, array $indexes): int
    {
        if ($indexes === []) {
            return 0;
        }

        $currentIndex = array_shift($indexes);

        if ($currentIndex === '*') {
            $removed = 0;

            foreach ($array as &$item) {
                if (! is_array($item)) {
                    continue;
                }

                $removed += self::unsetByDotPath($item, $indexes);
            }
            unset($item);

            return $removed;
        }

        if ($indexes === []) {
            if (! array_key_exists($currentIndex, $array)) {
                return 0;
            }

            unset($array[$currentIndex]);

            return 1;
        }

        if (! isset($array[$currentIndex]) || ! is_array($array[$currentIndex])) {
            return 0;
        }

        return self::unsetByDotPath($array[$currentIndex], $indexes);
    }

    /**
     * Clears all children under the specified path.
     *
     * @param array<array-key, mixed> $array
     * @param list<string>            $indexes
     */
    private static function clearByDotPath(array &$array, array $indexes): int
    {
        if ($indexes === []) {
            $count = count($array);
            $array = [];

            return $count;
        }

        $currentIndex = array_shift($indexes);

        if ($currentIndex === '*') {
            $cleared = 0;

            foreach ($array as &$item) {
                if (! is_array($item)) {
                    continue;
                }

                $cleared += self::clearByDotPath($item, $indexes);
            }
            unset($item);

            return $cleared;
        }

        if (! array_key_exists($currentIndex, $array) || ! is_array($array[$currentIndex])) {
            return 0;
        }

        return self::clearByDotPath($array[$currentIndex], $indexes);
    }

    /**
     * Removes a value by dot path for dotExcept(). Objects are expanded to an
     * array view only when the path descends into them, so untouched branches
     * keep their original values (including objects).
     *
     * @param array<array-key, mixed> $array
     * @param list<string>            $indexes
     */
    private static function excludeByDotPath(array &$array, array $indexes): int
    {
        if ($indexes === []) {
            return 0;
        }

        $currentIndex = array_shift($indexes);

        if ($currentIndex === '*') {
            $removed = 0;

            foreach ($array as &$item) {
                if (self::expandForDescent($item)) {
                    $removed += self::excludeByDotPath($item, $indexes);
                }
            }
            unset($item);

            return $removed;
        }

        if ($indexes === []) {
            if (! array_key_exists($currentIndex, $array)) {
                return 0;
            }

            unset($array[$currentIndex]);

            return 1;
        }

        if (! array_key_exists($currentIndex, $array) || ! self::expandForDescent($array[$currentIndex])) {
            return 0;
        }

        return self::excludeByDotPath($array[$currentIndex], $indexes);
    }

    /**
     * Clears all children under the specified path for dotExcept(), expanding
     * objects to an array view only along the descended path.
     *
     * @param array<array-key, mixed> $array
     * @param list<string>            $indexes
     */
    private static function excludeChildrenByDotPath(array &$array, array $indexes): int
    {
        if ($indexes === []) {
            $count = count($array);
            $array = [];

            return $count;
        }

        $currentIndex = array_shift($indexes);

        if ($currentIndex === '*') {
            $cleared = 0;

            foreach ($array as &$item) {
                if (self::expandForDescent($item)) {
                    $cleared += self::excludeChildrenByDotPath($item, $indexes);
                }
            }
            unset($item);

            return $cleared;
        }

        if (! array_key_exists($currentIndex, $array) || ! self::expandForDescent($array[$currentIndex])) {
            return 0;
        }

        return self::excludeChildrenByDotPath($array[$currentIndex], $indexes);
    }

    /**
     * Projects matching paths from source into result with preserved structure.
     *
     * @param array<array-key, mixed>|object $source
     * @param list<string>                   $indexes
     * @param list<string>                   $prefix
     * @param array<array-key, mixed>        $result
     */
    private static function projectByDotPath(
        array|object $source,
        array $indexes,
        array &$result,
        array $prefix = [],
    ): void {
        if ($indexes === []) {
            // The whole node was selected: preserve it as-is. Output structure
            // is only fabricated for the projection skeleton above this leaf.
            self::setByDotPath($result, $prefix, $source);

            return;
        }

        $currentIndex = array_shift($indexes);

        if ($currentIndex === '*') {
            foreach (self::entries($source) as $key => $value) {
                if (! self::isNavigable($value)) {
                    if ($indexes === []) {
                        self::setByDotPath($result, [...$prefix, (string) $key], $value);
                    }

                    continue;
                }

                self::projectByDotPath($value, $indexes, $result, [...$prefix, (string) $key]);
            }

            return;
        }

        [$found, $value] = self::resolve($source, $currentIndex);

        if (! $found) {
            return;
        }

        if (! self::isNavigable($value)) {
            if ($indexes === []) {
                self::setByDotPath($result, [...$prefix, $currentIndex], $value);
            }

            return;
        }

        self::projectByDotPath($value, $indexes, $result, [...$prefix, $currentIndex]);
    }
}
