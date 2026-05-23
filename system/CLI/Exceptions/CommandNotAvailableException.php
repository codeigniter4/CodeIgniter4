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

namespace CodeIgniter\CLI\Exceptions;

use CodeIgniter\Exceptions\RuntimeException;

/**
 * Exception thrown when a command is found but is not currently available for execution.
 */
final class CommandNotAvailableException extends RuntimeException
{
}
