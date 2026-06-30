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

/**
 * Thrown when a foreign key constraint is violated.
 */
class ForeignKeyConstraintViolationException extends ConstraintViolationException
{
}
