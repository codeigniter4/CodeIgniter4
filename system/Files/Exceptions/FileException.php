<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Files\Exceptions;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class FileException extends RuntimeException implements ExceptionInterface
{
    use DebugTraceableTrait;

    public static function forUnableToMove(?string $from = null, ?string $to = null, ?string $error = null)
    {
        return new static(lang('Files.cannotMove', [$from, $to, $error]));
    }

    /**
     * Throws when an item is expected to be a directory but is not or is missing.
     *
     * @param string $caller The method causing the exception
     */
    public static function forExpectedDirectory(string $caller)
    {
        return new static(lang('Files.expectedDirectory', [$caller]));
    }

    /**
     * Throws when an item is expected to be a file but is not or is missing.
     *
     * @param string $caller The method causing the exception
     */
    public static function forExpectedFile(string $caller)
    {
        return new static(lang('Files.expectedFile', [$caller]));
    }
}
