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

namespace CodeIgniter\Exceptions;

/**
 * Exception thrown if a function is called in the wrong way, or the function
 * does not exist.
 */
class BadFunctionCallException extends \BadFunctionCallException implements ExceptionInterface
{
}
