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

namespace CodeIgniter\Database;

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\CriticalError;
use InvalidArgumentException;

/**
 * Database Connection Factory
 *
 * Creates and returns an instance of the appropriate Database Connection.
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

    /**
     * Parses the connection binds and creates a Database Connection instance.
     *
     * @return BaseConnection
     *
     * @throws InvalidArgumentException
     */
    public function load(array $params = [], string $alias = '')
    {
        if ($alias === '') {
            throw new InvalidArgumentException('You must supply the parameter: alias.');
        }

        if (! empty($params['DSN']) && str_contains($params['DSN'], '://')) {
            $params = $this->parseDSN($params);
        }

        if (empty($params['DBDriver'])) {
            throw new InvalidArgumentException('You have not selected a database type to connect to.');
        }

        assert($this->checkDbExtension($params['DBDriver']));

        $this->connections[$alias] = $this->initDriver($params['DBDriver'], 'Connection', $params);

        return $this->connections[$alias];
    }

    /**
     * Creates a Forge instance for the current database type.
     */
    public function loadForge(ConnectionInterface $db): Forge
    {
        if (! $db->connID) {
            $db->initialize();
        }

        return $this->initDriver($db->DBDriver, 'Forge', $db);
    }

    /**
     * Creates an instance of Utils for the current database type.
     */
    public function loadUtils(ConnectionInterface $db): BaseUtils
    {
        if (! $db->connID) {
            $db->initialize();
        }

        return $this->initDriver($db->DBDriver, 'Utils', $db);
    }

    /**
     * Parses universal DSN string
     *
     * @throws InvalidArgumentException
     */
    protected function parseDSN(array $params): array
    {
        $dsn = parse_url($params['DSN']);

        if ($dsn === 0 || $dsn === '' || $dsn === '0' || $dsn === [] || $dsn === false || $dsn === null) {
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

        if (isset($dsn['query']) && ($dsn['query'] !== '')) {
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

    /**
     * Creates a database object.
     *
     * @param string                    $driver   Driver name. FQCN can be used.
     * @param string                    $class    'Connection'|'Forge'|'Utils'
     * @param array|ConnectionInterface $argument The constructor parameter or DB connection
     *
     * @return BaseConnection|BaseUtils|Forge
     */
    protected function initDriver(string $driver, string $class, $argument): object
    {
        $classname = (! str_contains($driver, '\\'))
            ? "CodeIgniter\\Database\\{$driver}\\{$class}"
            : $driver . '\\' . $class;

        return new $classname($argument);
    }

    /**
     * Check the PHP database extension is loaded.
     *
     * @param string $driver DB driver or FQCN for custom driver
     */
    private function checkDbExtension(string $driver): bool
    {
        if (str_contains($driver, '\\')) {
            // Cannot check a fully qualified classname for a custom driver.
            return true;
        }

        $extensionMap = [
            // DBDriver => PHP extension
            'MySQLi'  => 'mysqli',
            'SQLite3' => 'sqlite3',
            'Postgre' => 'pgsql',
            'SQLSRV'  => 'sqlsrv',
            'OCI8'    => 'oci8',
        ];

        $extension = $extensionMap[$driver] ?? '';

        if ($extension === '') {
            $message = 'Invalid DBDriver name: "' . $driver . '"';

            throw new ConfigException($message);
        }

        if (extension_loaded($extension)) {
            return true;
        }

        $message = 'The required PHP extension "' . $extension . '" is not loaded.'
            . ' Install and enable it to use "' . $driver . '" driver.';

        throw new CriticalError($message);
    }
}
