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
     * @param TWhen                        $condition
     * @param callable(self, TWhen): mixed $callback
     * @param (callable(self): mixed)|null $defaultCallback
     * @param mixed                        $condition
     *
     * @return $this
     */
    public function when($condition, callable $callback, ?callable $defaultCallback = null): self
    {
        if ((bool) $condition) {
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
     * @param TWhenNot                        $condition
     * @param callable(self, TWhenNot): mixed $callback
     * @param (callable(self): mixed)|null    $defaultCallback
     * @param mixed                           $condition
     *
     * @return $this
     */
    public function whenNot($condition, callable $callback, ?callable $defaultCallback = null): self
    {
        if (! (bool) $condition) {
            $callback($this, $condition);
        } elseif ($defaultCallback !== null) {
            $defaultCallback($this);
        }

        return $this;
    }
}
