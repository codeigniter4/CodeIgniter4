<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Router\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use Exception;

/**
 * RedirectException
 *
 * @deprecated Use \CodeIgniter\HTTP\Exceptions\RedirectException instead
 */
class RedirectException extends Exception implements HTTPExceptionInterface
{
    /**
     * HTTP status code for redirects
     *
     * @var int
     */
    protected $code = 302;
}
