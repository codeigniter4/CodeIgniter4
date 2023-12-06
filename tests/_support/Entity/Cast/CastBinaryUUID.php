<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Entity\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class CastBinaryUUID extends BaseCast
{
    /**
     * Get
     *
     * @param string $binary Binary UUID
     *
     * @return string String UUID
     */
    public static function get($binary, array $params = []): string
    {
        $string = unpack('h*', $binary);

        return preg_replace('/([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})/', '$1-$2-$3-$4-$5', $string[1]);
    }

    /**
     * Set
     *
     * @param string $string String UUID
     *
     * @return string Binary UUID
     */
    public static function set($string, array $params = []): string
    {
        return pack('h*', str_replace('-', '', $string));
    }
}
