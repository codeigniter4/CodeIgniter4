<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Router\Exceptions;

use Exception;

/**
 * RedirectException
 */
class RedirectException extends Exception
{
    /**
     * Status code for redirects
     *
     * @var int
     */
    protected $code = 302;
}
