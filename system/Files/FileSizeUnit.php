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

namespace CodeIgniter\Files;

use CodeIgniter\Exceptions\InvalidArgumentException;

enum FileSizeUnit: int
{
    case B  = 0;
    case KB = 1;
    case MB = 2;
    case GB = 3;
    case TB = 4;

    /**
     * Allows the creation of a FileSizeUnit from Strings like "kb" or "mb"
     *
     * @throws InvalidArgumentException
     */
    public static function fromString(string $unit): self
    {
        return match (strtolower($unit)) {
            'b'     => self::B,
            'kb'    => self::KB,
            'mb'    => self::MB,
            'gb'    => self::GB,
            'tb'    => self::TB,
            default => throw new InvalidArgumentException("Invalid unit: {$unit}"),
        };
    }
}
