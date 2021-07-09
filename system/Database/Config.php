<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Config\BaseConfig;
use InvalidArgumentException;

/**
 * Class Config
 */
class Config extends BaseConfig
{
    /**
     * Cache for instance of any connections that
     * have been requested as a "shared" instance.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * The main instance used to manage all of
     * our open database connections.
     *
     * @var Database|null
     */
    protected static $factory;

    //--------------------------------------------------------------------

    /**
     * Creates the default
     *
     * @param array|string $group     The name of the connection group to use,
     *                                or an array of configuration settings.
     * @param bool         $getShared Whether to return a shared instance of the connection.
     *
     * @return BaseConnection
     */
    public static function connect($group = null, bool $getShared = true)
    {
        // If a DB connection is passed in, just pass it back
        if ($group instanceof BaseConnection) {
            return $group;
        }

        if (is_array($group)) {
            $config = $group;
            $group  = 'custom-' . md5(json_encode($config));
        }

        $config = $config ?? config('Database');

        if (empty($group)) {
            $group = ENVIRONMENT === 'testing' ? 'tests' : $config->defaultGroup;
        }

        if (is_string($group) && ! isset($config->{$group}) && strpos($group, 'custom-') !== 0) {
            throw new InvalidArgumentException($group . ' is not a valid database connection group.');
        }

        if ($getShared && isset(static::$instances[$group])) {
            return static::$instances[$group];
        }

        static::ensureFactory();

        if (isset($config->{$group})) {
            $config = $config->{$group};
        }

        $connection = static::$factory->load($config, $group);

        static::$instances[$group] = &$connection;

        return $connection;
    }

    //--------------------------------------------------------------------

    /**
     * Returns an array of all db connections currently made.
     *
     * @return array
     */
    public static function getConnections(): array
    {
        return static::$instances;
    }

    /**
     * Loads and returns an instance of the Forge for the specified
     * database group, and loads the group if it hasn't been loaded yet.
     *
     * @param array|ConnectionInterface|string|null $group
     *
     * @return Forge
     */
    public static function forge($group = null)
    {
        $db = static::connect($group);

        return static::$factory->loadForge($db);
    }

    //--------------------------------------------------------------------

    /**
     * Returns a new instance of the Database Utilities class.
     *
     * @param array|string|null $group
     *
     * @return BaseUtils
     */
    public static function utils($group = null)
    {
        $db = static::connect($group);

        return static::$factory->loadUtils($db);
    }

    //--------------------------------------------------------------------

    /**
     * Returns a new instance of the Database Seeder.
     *
     * @param string|null $group
     *
     * @return Seeder
     */
    public static function seeder(?string $group = null)
    {
        $config = config('Database');

        return new Seeder($config, static::connect($group));
    }

    //--------------------------------------------------------------------

    /**
     * Ensures the database Connection Manager/Factory is loaded and ready to use.
     */
    protected static function ensureFactory()
    {
        if (static::$factory instanceof Database) {
            return;
        }

        static::$factory = new Database();
    }

    //--------------------------------------------------------------------
}
