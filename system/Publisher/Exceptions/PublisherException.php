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

namespace CodeIgniter\Publisher\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * Publisher Exception Class
 *
 * Handles exceptions related to actions taken by a Publisher.
 */
class PublisherException extends FrameworkException
{
    /**
     * Throws when a file should be overwritten yet cannot.
     *
     * @param string $from The source file
     * @param string $to   The destination file
     *
     * @return static
     */
    public static function forCollision(string $from, string $to)
    {
        return new static(lang('Publisher.collision', [filetype($to), $from, $to]));
    }

    /**
     * Throws when given a destination that is not in the list of allowed directories.
     *
     * @return static
     */
    public static function forDestinationNotAllowed(string $destination)
    {
        return new static(lang('Publisher.destinationNotAllowed', [$destination]));
    }

    /**
     * Throws when a file fails to match the allowed pattern for its destination.
     *
     * @return static
     */
    public static function forFileNotAllowed(string $file, string $directory, string $pattern)
    {
        return new static(lang('Publisher.fileNotAllowed', [$file, $directory, $pattern]));
    }
}
