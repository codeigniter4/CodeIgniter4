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

namespace CodeIgniter\Router\Exceptions;

/**
 * Provides a domain-level interface for broad capture
 * of all Router-related exceptions.
 *
 * catch (\CodeIgniter\Router\Exceptions\ExceptionInterface) { ... }
 */
interface ExceptionInterface extends \CodeIgniter\Exceptions\ExceptionInterface
{
}
