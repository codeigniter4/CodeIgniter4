<?php

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
     * @param array|bool|float|int|object|resource|string|null $condition
     */
    public function when($condition, callable $callback, ?callable $defaultCallback = null): self
    {
        if ($condition) {
            $callback($this, $condition);
        } elseif ($defaultCallback) {
            $defaultCallback($this);
        }

        return $this;
    }

    /**
     * Only runs the query when $condition evaluates to false
     *
     * @param array|bool|float|int|object|resource|string|null $condition
     */
    public function whenNot($condition, callable $callback, ?callable $defaultCallback = null): self
    {
        if (! $condition) {
            $callback($this, $condition);
        } elseif ($defaultCallback) {
            $defaultCallback($this);
        }

        return $this;
    }
}
