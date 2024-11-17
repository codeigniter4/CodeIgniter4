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
 * Class DownloadException
 */
class DownloadException extends RuntimeException
{
    use DebugTraceableTrait;

    /**
     * @return static
     */
    public static function forCannotSetFilePath(string $path)
    {
        return new static(lang('HTTP.cannotSetFilepath', [$path]));
    }

    /**
     * @return static
     */
    public static function forCannotSetBinary()
    {
        return new static(lang('HTTP.cannotSetBinary'));
    }

    /**
     * @return static
     */
    public static function forNotFoundDownloadSource()
    {
        return new static(lang('HTTP.notFoundDownloadSource'));
    }

    /**
     * @deprecated Since v4.5.6
     *
     * @return static
     */
    public static function forCannotSetCache()
    {
        return new static(lang('HTTP.cannotSetCache'));
    }

    /**
     * @return static
     */
    public static function forCannotSetStatusCode(int $code, string $reason)
    {
        return new static(lang('HTTP.cannotSetStatusCode', [$code, $reason]));
    }
}
