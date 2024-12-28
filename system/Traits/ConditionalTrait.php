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

namespace CodeIgniter\Traits;

trait ConditionalTrait
{
    /**
     * Only runs the query when $condition evaluates to true
     *
     * @template TWhen of mixed
     *
     * @phpstan-param TWhen                                            $condition
     * @phpstan-param callable(self, TWhen): mixed                     $callback
     * @phpstan-param (callable(self): mixed)|null                     $defaultCallback
     * @param         array|bool|float|int|object|resource|string|null $condition
     *
     * @return $this
     */
    public function when($condition, callable $callback, ?callable $defaultCallback = null): self
    {
        if ($condition !== '' && $condition !== false && $condition !== null) {
            $callback($this, $condition);
        } elseif ($defaultCallback !== null) {
            $defaultCallback($this);
        }

        return $this;
    }

    /**
     * Only runs the query when $condition evaluates to false
     *
     * @template TWhenNot of mixed
     *
     * @phpstan-param TWhenNot                                         $condition
     * @phpstan-param callable(self, TWhenNot): mixed                  $callback
     * @phpstan-param (callable(self): mixed)|null                     $defaultCallback
     * @param         array|bool|float|int|object|resource|string|null $condition
     *
     * @return $this
     */
    public function whenNot($condition, callable $callback, ?callable $defaultCallback = null): self
    {
        if ($condition === '' || $condition === null || $condition === false || $condition === '0') {
            $callback($this, $condition);
        } elseif ($defaultCallback !== null) {
            $defaultCallback($this);
        }

        return $this;
    }
}
