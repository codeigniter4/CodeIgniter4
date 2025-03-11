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

namespace CodeIgniter\HTTP\Parameters;

use Countable;
use IteratorAggregate;

/**
 * @template TKey of string
 * @template TValue
 *
 * @extends IteratorAggregate<TKey, TValue>
 */
interface ParametersInterface extends IteratorAggregate, Countable
{
    /**
     * @param array<TKey, TValue> $parameters
     */
    public function __construct(array $parameters = []);

    /**
     * @param array<TKey, TValue> $parameters
     */
    public function override(array $parameters = []): void;

    /**
     * @param TKey $key
     */
    public function has(string $key): bool;

    /**
     * @param TKey   $key
     * @param TValue $default
     *
     * @return TValue|null
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * @param TKey   $key
     * @param TValue $value
     */
    public function set(string $key, mixed $value): void;

    /**
     * @param TKey $key
     *
     * @return array<TKey, TValue>
     */
    public function all(?string $key = null): array;

    /**
     * @return list<TKey>
     */
    public function keys(): array;
}
