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

use stdClass;

/**
 * Class ObjectCast
 */
class ObjectCast extends BaseCast
{
    /**
     * {@inheritDoc}
     */
    public static function set($value, array $params = []): object
    {
        if (! is_array($value)) {
            self::invalidTypeValueError($value);
        }

        return (object) $value;
    }

    /**
     * {@inheritDoc}
     */
    public static function toDatabase($value, array $params = [])
    {
        if (! $value instanceof stdClass) {
            self::invalidTypeValueError($value);
        }

        // @TODO How to implement?
        return serialize($value);
    }

    /**
     * {@inheritDoc}
     */
    public static function fromDatabase($value, array $params = []): array
    {
        if (! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        // @TODO How to implement?
        if ((strpos($value, 'a:') === 0 || strpos($value, 's:') === 0)) {
            $value = unserialize($value);
        }

        return (array) $value;
    }
}
