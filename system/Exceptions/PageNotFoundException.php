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

class PageNotFoundException extends RuntimeException implements HTTPExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * HTTP status code
     *
     * @var int
     */
    protected $code = 404;

    /**
     * @return static
     */
    public static function forPageNotFound(?string $message = null)
    {
        return new static($message ?? self::lang('HTTP.pageNotFound'));
    }

    /**
     * @return static
     */
    public static function forEmptyController()
    {
        return new static(self::lang('HTTP.emptyController'));
    }

    /**
     * @return static
     */
    public static function forControllerNotFound(string $controller, string $method)
    {
        return new static(self::lang('HTTP.controllerNotFound', [$controller, $method]));
    }

    /**
     * @return static
     */
    public static function forMethodNotFound(string $method)
    {
        return new static(self::lang('HTTP.methodNotFound', [$method]));
    }

    /**
     * @return static
     */
    public static function forLocaleNotSupported(string $locale)
    {
        return new static(self::lang('HTTP.localeNotSupported', [$locale]));
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
        $lang = service('language', null, false);

        return $lang->getLine($line, $args);
    }
}
