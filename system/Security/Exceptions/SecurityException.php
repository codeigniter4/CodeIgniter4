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

namespace CodeIgniter\Security\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Exceptions\HTTPExceptionInterface;

class SecurityException extends FrameworkException implements HTTPExceptionInterface
{
    /**
     * Throws when some specific action is not allowed.
     *
     * @return static
     */
    public static function forDisallowedAction()
    {
        return new static(lang('Security.disallowedAction'), 403);
    }

    /**
     * Throws when the source string contains invalid UTF-8 characters.
     *
     * @param string $source The source string
     * @param string $string The invalid string
     *
     * @return static
     */
    public static function forInvalidUTF8Chars(string $source, string $string)
    {
        return new static(
            'Invalid UTF-8 characters in ' . $source . ': ' . $string,
            400
        );
    }

    /**
     * Throws when the source string contains invalid control characters.
     *
     * @param string $source The source string
     * @param string $string The invalid string
     *
     * @return static
     */
    public static function forInvalidControlChars(string $source, string $string)
    {
        return new static(
            'Invalid Control characters in ' . $source . ': ' . $string,
            400
        );
    }

    /**
     * @deprecated Use `CookieException::forInvalidSameSite()` instead.
     *
     * @codeCoverageIgnore
     *
     * @return static
     */
    public static function forInvalidSameSite(string $samesite)
    {
        return new static(lang('Security.invalidSameSite', [$samesite]));
    }
}
