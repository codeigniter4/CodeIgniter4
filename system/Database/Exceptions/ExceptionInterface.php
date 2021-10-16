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

/**
 * Provides a domain-level interface for broad capture
 * of all database-related exceptions.
 *
 * catch (\CodeIgniter\Database\Exceptions\ExceptionInterface) { ... }
 */
interface ExceptionInterface extends \CodeIgniter\Exceptions\ExceptionInterface
{
}
