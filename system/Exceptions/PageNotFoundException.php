<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Exceptions;

use Config\Services;
use OutOfBoundsException;

class PageNotFoundException extends OutOfBoundsException implements ExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * HTTP status code
     *
     * @var int
     */
    protected $code = 404;

    public static function forPageNotFound(?string $message = null)
    {
        return new static($message ?? self::lang('HTTP.pageNotFound'));
    }

    public static function forEmptyController()
    {
        return new static(self::lang('HTTP.emptyController'));
    }

    public static function forControllerNotFound(string $controller, string $method)
    {
        return new static(self::lang('HTTP.controllerNotFound', [$controller, $method]));
    }

    public static function forMethodNotFound(string $method)
    {
        return new static(self::lang('HTTP.methodNotFound', [$method]));
    }

    /**
     * Get translated system message
     *
     * Use a non-shared Language instance in the Services.
     * If a shared instance is created, the Language will
     * have the current locale, so even if users call
     * `$this->request->setLocale()` in the controller afterwards,
     * the Language locale will not be changed.
     */
    private static function lang(string $line, array $args = []): string
    {
        $lang = Services::language(null, false);

        return $lang->getLine($line, $args);
    }
}
