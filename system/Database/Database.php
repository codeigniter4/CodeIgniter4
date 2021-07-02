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

use InvalidArgumentException;

/**
 * Database Connection Factory
 *
 * Creates and returns an instance of the appropriate DatabaseConnection
 */
class Database
{
    /**
     * Maintains an array of the instances of all connections that have
     * been created.
     *
     * Helps to keep track of all open connections for performance
     * monitoring, logging, etc.
     *
     * @var array
     */
    protected $connections = [];

    //--------------------------------------------------------------------

    /**
     * Parses the connection binds and returns an instance of the driver
     * ready to go.
     *
     * @param array  $params
     * @param string $alias
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     *
     * @internal param bool $useBuilder
     */
    public function load(array $params = [], string $alias = '')
    {
        if ($alias === '') {
            throw new InvalidArgumentException('You must supply the parameter: alias.');
        }

        // Handle universal DSN connection string
        if (! empty($params['DSN']) && strpos($params['DSN'], '://') !== false) {
            $params = $this->parseDSN($params);
        }

        // No DB specified? Beat them senseless...
        if (empty($params['DBDriver'])) {
            throw new InvalidArgumentException('You have not selected a database type to connect to.');
        }

        // Store the connection
        $this->connections[$alias] = $this->initDriver($params['DBDriver'], 'Connection', $params);

        return $this->connections[$alias];
    }

    //--------------------------------------------------------------------

    /**
     * Creates a Forge instance for the current database type.
     *
     * @param ConnectionInterface $db
     *
     * @return object
     */
    public function loadForge(ConnectionInterface $db): object
    {
        // Initialize database connection if not exists.
        if (! $db->connID) {
            $db->initialize();
        }

        return $this->initDriver($db->DBDriver, 'Forge', $db);
    }

    //--------------------------------------------------------------------

    /**
     * Creates a Utils instance for the current database type.
     *
     * @param ConnectionInterface $db
     *
     * @return object
     */
    public function loadUtils(ConnectionInterface $db): object
    {
        // Initialize database connection if not exists.
        if (! $db->connID) {
            $db->initialize();
        }

        return $this->initDriver($db->DBDriver, 'Utils', $db);
    }

    //--------------------------------------------------------------------

    /**
     * Parse universal DSN string
     *
     * @param array $params
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    protected function parseDSN(array $params): array
    {
        $dsn = parse_url($params['DSN']);

        if (! $dsn) {
            throw new InvalidArgumentException('Your DSN connection string is invalid.');
        }

        $dsnParams = [
            'DSN'      => '',
            'DBDriver' => $dsn['scheme'],
            'hostname' => isset($dsn['host']) ? rawurldecode($dsn['host']) : '',
            'port'     => isset($dsn['port']) ? rawurldecode((string) $dsn['port']) : '',
            'username' => isset($dsn['user']) ? rawurldecode($dsn['user']) : '',
            'password' => isset($dsn['pass']) ? rawurldecode($dsn['pass']) : '',
            'database' => isset($dsn['path']) ? rawurldecode(substr($dsn['path'], 1)) : '',
        ];

        // Do we have additional config items set?
        if (! empty($dsn['query'])) {
            parse_str($dsn['query'], $extra);

            foreach ($extra as $key => $val) {
                if (is_string($val) && in_array(strtolower($val), ['true', 'false', 'null'], true)) {
                    $val = $val === 'null' ? null : filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }

                $dsnParams[$key] = $val;
            }
        }

        return array_merge($params, $dsnParams);
    }

    //--------------------------------------------------------------------

    /**
     * Initialize database driver.
     *
     * @param string       $driver   Database driver name (e.g. 'MySQLi')
     * @param string       $class    Database class name (e.g. 'Forge')
     * @param array|object $argument
     *
     * @return object
     */
    protected function initDriver(string $driver, string $class, $argument): object
    {
        $class = $driver . '\\' . $class;

        if (strpos($driver, '\\') === false) {
            $class = "CodeIgniter\\Database\\{$class}";
        }

        return new $class($argument);
    }
}
