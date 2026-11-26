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

namespace CodeIgniter\Debug\Inspection;

use Throwable;

/**
 * Walks an exception's chain of causes.
 *
 * @internal
 */
final readonly class Inspector
{
    /**
     * Start inspection from the reported exception.
     */
    public function __construct(private Throwable $exception)
    {
    }

    /**
     * List the reported exception and its causes in order.
     *
     * @return list<Throwable>
     */
    public function chain(): array
    {
        $chain = [];

        for ($exception = $this->exception; $exception instanceof Throwable; $exception = $exception->getPrevious()) {
            $chain[] = $exception;
        }

        return $chain;
    }

    /**
     * Return the deepest cause, or the reported exception when it has none.
     */
    public function rootCause(): Throwable
    {
        $chain = $this->chain();

        return $chain[array_key_last($chain)];
    }
}
