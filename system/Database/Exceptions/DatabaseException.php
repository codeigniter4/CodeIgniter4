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

namespace CodeIgniter\Database\Exceptions;

use CodeIgniter\Exceptions\HasExitCodeInterface;
use CodeIgniter\Exceptions\RuntimeException;
use Throwable;

class DatabaseException extends RuntimeException implements ExceptionInterface, HasExitCodeInterface
{
    /**
     * Native code returned by the database driver.
     */
    protected int|string $databaseCode = 0;

    /**
     * @param int|string $code Native database code (e.g. 1062, 23505, 23000/2601)
     */
    public function __construct(string $message = '', int|string $code = 0, ?Throwable $previous = null)
    {
        $this->databaseCode = $code;

        // Keep Throwable::getCode() behavior BC-friendly for non-int DB codes.
        parent::__construct($message, is_int($code) ? $code : 0, $previous);
    }

    /**
     * Returns the native code from the database driver.
     */
    public function getDatabaseCode(): int|string
    {
        return $this->databaseCode;
    }

    public function getExitCode(): int
    {
        return EXIT_DATABASE;
    }
}
