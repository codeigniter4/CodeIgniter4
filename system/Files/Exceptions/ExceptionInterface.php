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

namespace CodeIgniter\Files\Exceptions;

/**
 * Provides a domain-level interface for broad capture
 * of all Files-related exceptions.
 *
 * catch (\CodeIgniter\Files\Exceptions\ExceptionInterface) { ... }
 */
interface ExceptionInterface extends \CodeIgniter\Exceptions\ExceptionInterface
{
}
