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

namespace CodeIgniter\Database;

/**
 * Interface QueryInterface
 *
 * Represents a single statement that can be executed against the database.
 * Statements are platform-specific and can handle binding of binds.
 */
interface QueryInterface
{
    /**
     * Sets the raw query string to use for this statement.
     *
     * @param mixed $binds
     *
     * @return $this
     */
    public function setQuery(string $sql, $binds = null, bool $setEscape = true);

    /**
     * Returns the final, processed query string after binding, etal
     * has been performed.
     *
     * @return string
     */
    public function getQuery();

    /**
     * Records the execution time of the statement using microtime(true)
     * for it's start and end values. If no end value is present, will
     * use the current time to determine total duration.
     *
     * @return $this
     */
    public function setDuration(float $start, ?float $end = null);

    /**
     * Returns the duration of this query during execution, or null if
     * the query has not been executed yet.
     *
     * @param int $decimals The accuracy of the returned time.
     */
    public function getDuration(int $decimals = 6): string;

    /**
     * Stores the error description that happened for this query.
     *
     * @return $this
     */
    public function setError(int $code, string $error);

    /**
     * Reports whether this statement created an error not.
     */
    public function hasError(): bool;

    /**
     * Returns the error code created while executing this statement.
     */
    public function getErrorCode(): int;

    /**
     * Returns the error message created while executing this statement.
     */
    public function getErrorMessage(): string;

    /**
     * Determines if the statement is a write-type query or not.
     */
    public function isWriteType(): bool;

    /**
     * Swaps out one table prefix for a new one.
     *
     * @return $this
     */
    public function swapPrefix(string $orig, string $swap);
}
