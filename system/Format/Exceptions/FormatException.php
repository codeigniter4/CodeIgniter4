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

namespace CodeIgniter\Format\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\RuntimeException;

/**
 * FormatException
 */
class FormatException extends RuntimeException
{
    use DebugTraceableTrait;

    /**
     * Thrown when the instantiated class does not exist.
     *
     * @return static
     */
    public static function forInvalidFormatter(string $class)
    {
        return new static(lang('Format.invalidFormatter', [$class]));
    }

    /**
     * Thrown in JSONFormatter when the json_encode produces
     * an error code other than JSON_ERROR_NONE and JSON_ERROR_RECURSION.
     *
     * @param string $error The error message
     *
     * @return static
     */
    public static function forInvalidJSON(?string $error = null)
    {
        return new static(lang('Format.invalidJSON', [$error]));
    }

    /**
     * Thrown when the supplied MIME type has no
     * defined Formatter class.
     *
     * @return static
     */
    public static function forInvalidMime(string $mime)
    {
        return new static(lang('Format.invalidMime', [$mime]));
    }

    /**
     * Thrown on XMLFormatter when the `simplexml` extension
     * is not installed.
     *
     * @return static
     *
     * @codeCoverageIgnore
     */
    public static function forMissingExtension()
    {
        return new static(lang('Format.missingExtension'));
    }
}
