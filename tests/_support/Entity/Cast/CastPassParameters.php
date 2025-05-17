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

namespace Tests\Support\Entity\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class CastPassParameters extends BaseCast
{
    /**
     * Set
     *
     * @param mixed $value  Data
     * @param array $params Additional param
     */
    public static function set($value, array $params = []): string
    {
        return $value . ':' . json_encode($params);
    }
}
