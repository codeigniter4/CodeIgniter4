<?php

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

class SecurityException extends FrameworkException
{
    public static function forDisallowedAction()
    {
        return new static(lang('Security.disallowedAction'), 403);
    }

    public static function forInvalidUTF8Chars(string $source, string $string)
    {
        return new static(
            'Invalid UTF-8 characters in ' . $source . ': ' . $string,
            400
        );
    }

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
     */
    public static function forInvalidSameSite(string $samesite)
    {
        return new static(lang('Security.invalidSameSite', [$samesite]));
    }
}
