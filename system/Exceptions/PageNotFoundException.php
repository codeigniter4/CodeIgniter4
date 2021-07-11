<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Exceptions;

use OutOfBoundsException;

class PageNotFoundException extends OutOfBoundsException implements ExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * Error code
     *
     * @var int
     */
    protected $code = 404;

    public static function forPageNotFound(?string $message = null)
    {
        return new static($message ?? lang('HTTP.pageNotFound'));
    }

    public static function forEmptyController()
    {
        return new static(lang('HTTP.emptyController'));
    }

    public static function forControllerNotFound(string $controller, string $method)
    {
        return new static(lang('HTTP.controllerNotFound', [$controller, $method]));
    }

    public static function forMethodNotFound(string $method)
    {
        return new static(lang('HTTP.methodNotFound', [$method]));
    }
}
