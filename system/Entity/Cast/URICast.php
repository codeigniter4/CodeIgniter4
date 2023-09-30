<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity\Cast;

use CodeIgniter\HTTP\URI;

/**
 * Class URICast
 */
class URICast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function set($value, array $params = []): URI
    {
        if ($value instanceof URI) {
            return $value;
        }

        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        return new URI($value);
    }
}
