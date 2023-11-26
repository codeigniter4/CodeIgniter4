<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Config\Factories;

/**
 * Returns new or shared Model instances
 *
 * @deprecated Use CodeIgniter\Config\Factories::models()
 *
 * @codeCoverageIgnore
 * @see \CodeIgniter\Database\ModelFactoryTest
 */
class ModelFactory
{
    /**
     * Creates new Model instances or returns a shared instance
     *
     * @return mixed
     */
    public static function get(string $name, bool $getShared = true, ?ConnectionInterface $connection = null)
    {
        return Factories::models($name, ['getShared' => $getShared], $connection);
    }

    /**
     * Helper method for injecting mock instances while testing.
     *
     * @param object $instance
     */
    public static function injectMock(string $name, $instance)
    {
        Factories::injectMock('models', $name, $instance);
    }

    /**
     * Resets the static arrays
     */
    public static function reset()
    {
        Factories::reset('models');
    }
}
