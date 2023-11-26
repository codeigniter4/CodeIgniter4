<?php

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
use Error;

class DatabaseException extends Error implements ExceptionInterface, HasExitCodeInterface
{
    public function getExitCode(): int
    {
        return EXIT_DATABASE;
    }
}
