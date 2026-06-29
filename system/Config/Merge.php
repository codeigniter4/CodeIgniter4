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

namespace CodeIgniter\Config;

use CodeIgniter\Exceptions\InvalidArgumentException;

/**
 * Describes how a Registrar value should be merged into an existing
 * Config property. Interpreted when returned as the value of a config
 * property; nested directives are honored inside Merge::byKey().
 *
 * @see \CodeIgniter\Config\BaseConfig
 */
final readonly class Merge
{
    /**
     * Discard the existing value and use the new one.
     */
    public const REPLACE = 'replace';

    /**
     * Add absent list items to the end of the existing value.
     */
    public const APPEND = 'append';

    /**
     * Add absent list items to the front of the existing value.
     */
    public const PREPEND = 'prepend';

    /**
     * Insert list items immediately before the anchor element.
     */
    public const BEFORE = 'before';

    /**
     * Insert list items immediately after the anchor element.
     */
    public const AFTER = 'after';

    /**
     * Deep-merge by key: string keys recurse, integer keys append, scalars replace.
     */
    public const BY_KEY = 'byKey';

    /**
     * @param self::AFTER|self::APPEND|self::BEFORE|self::BY_KEY|self::PREPEND|self::REPLACE $strategy
     * @param mixed                                                                          $value    Any value for REPLACE; array for the list strategies and BY_KEY.
     * @param mixed                                                                          $anchor   The element BEFORE/AFTER position against (matched strictly).
     */
    private function __construct(
        public string $strategy,
        public mixed $value,
        public mixed $anchor = null,
    ) {
    }

    /**
     * Replace the existing value entirely (terminal: the payload is used
     * verbatim). Accepts any type, so it works for scalars too:
     * Merge::replace(false), Merge::replace('driver'), Merge::replace(null),
     * or arrays (e.g. ['a','b'] + ['c'] => ['c']).
     */
    public static function replace(mixed $value): self
    {
        return new self(self::REPLACE, $value);
    }

    /**
     * Append absent list items to the end of the existing value
     * (e.g. ['a','b'] + ['b','c'] => ['a','b','c']). Values already present are
     * left where they are. The payload is literal - for nested control, use
     * byKey() rather than nesting directives in an append() payload. List keys
     * are not preserved: the value is treated as a list.
     *
     * @param list<mixed> $value
     */
    public static function append(array $value): self
    {
        return new self(self::APPEND, $value);
    }

    /**
     * Prepend absent list items to the front of the existing value
     * (e.g. ['a','b'] + ['c'] => ['c','a','b']). Values already present are left
     * where they are. List keys are not preserved: the value is treated as a list.
     *
     * @param list<mixed> $value
     */
    public static function prepend(array $value): self
    {
        return new self(self::PREPEND, $value);
    }

    /**
     * Insert list items immediately before the first element equal (===) to
     * $anchor. An already-present value is moved to this position. If the anchor
     * is not in the list this falls back to prepend() and does not relocate
     * already-present values. List keys are not preserved.
     *
     * @param list<mixed> $value
     *
     * @throws InvalidArgumentException if $anchor is also one of the inserted values.
     */
    public static function before(mixed $anchor, array $value): self
    {
        self::assertAnchorNotInPayload($anchor, $value, self::BEFORE);

        return new self(self::BEFORE, $value, $anchor);
    }

    /**
     * Insert list items immediately after the first element equal (===) to
     * $anchor. An already-present value is moved to this position. If the anchor
     * is not in the list this falls back to append() and does not relocate
     * already-present values. List keys are not preserved.
     *
     * @param list<mixed> $value
     *
     * @throws InvalidArgumentException if $anchor is also one of the inserted values.
     */
    public static function after(mixed $anchor, array $value): self
    {
        self::assertAnchorNotInPayload($anchor, $value, self::AFTER);

        return new self(self::AFTER, $value, $anchor);
    }

    /**
     * Guards against anchoring a before()/after() insert on a value that is also
     * being inserted. That request is contradictory - the anchor would be removed
     * by de-duplication before it could be located - so it is rejected outright.
     *
     * @param list<mixed> $value
     */
    private static function assertAnchorNotInPayload(mixed $anchor, array $value, string $strategy): void
    {
        if (in_array($anchor, $value, true)) {
            throw new InvalidArgumentException(
                'Merge::' . $strategy . '() cannot use a value that is also being inserted as its anchor.',
            );
        }
    }

    /**
     * Deep-merge into the existing value by key: associative (string) keys are
     * merged/recursed, list (integer) keys append, scalar leaves are replaced.
     * Nested Merge directives ARE honored within the payload. Named byKey() to
     * distance it from PHP's array_merge_recursive(), which collects scalars
     * into arrays.
     *
     * @param array<array-key, mixed> $value
     */
    public static function byKey(array $value): self
    {
        return new self(self::BY_KEY, $value);
    }
}
