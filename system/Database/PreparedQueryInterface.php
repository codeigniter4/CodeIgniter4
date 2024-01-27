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

use CodeIgniter\Exceptions\BadMethodCallException;

/**
 * @template TConnection
 * @template TStatement
 * @template TResult
 */
interface PreparedQueryInterface
{
    /**
     * Takes a new set of data and runs it against the currently
     * prepared query. Upon success, will return a Results object.
     *
     * @return         bool|ResultInterface
     * @phpstan-return bool|ResultInterface<TConnection, TResult>
     */
    public function execute(...$data);

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * @return $this
     */
    public function prepare(string $sql, array $options = []);

    /**
     * Explicity closes the statement.
     *
     * @throws BadMethodCallException
     */
    public function close(): bool;

    /**
     * Returns the SQL that has been prepared.
     */
    public function getQueryString(): string;

    /**
     * Returns the error code created while executing this statement.
     */
    public function getErrorCode(): int;

    /**
     * Returns the error message created while executing this statement.
     */
    public function getErrorMessage(): string;
}
