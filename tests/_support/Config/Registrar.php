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

namespace Tests\Support\Config;

use mysqli;
use PDO;
use Throwable;

/**
 * Class Registrar
 *
 * Provides a basic registrar class for testing BaseConfig registration functions.
 */
class Registrar
{
    /**
     * DB config array for testing purposes.
     *
     * @var array<string, array<string, array<string, bool|int|string>|bool|int|string>>
     */
    protected static array $dbConfig = [
        'MySQLi' => [
            'DSN'      => '',
            'hostname' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'test',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => 'db_',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => true,
            'failover' => [],
            'port'     => 3306,
        ],
        'Postgre' => [
            'DSN'      => '',
            'hostname' => 'localhost',
            'username' => 'postgres',
            'password' => 'postgres',
            'database' => 'test',
            'DBDriver' => 'Postgre',
            'DBPrefix' => 'db_',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => '',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => true, // @todo 4.7.0 to remove in v4.8.0
            'failover' => [],
            'port'     => 5432,
        ],
        'SQLite3' => [
            'DSN'         => '',
            'hostname'    => 'localhost',
            'username'    => '',
            'password'    => '',
            'database'    => 'database.db',
            'DBDriver'    => 'SQLite3',
            'DBPrefix'    => 'db_',
            'pConnect'    => false,
            'DBDebug'     => true,
            'charset'     => 'utf8',
            'DBCollat'    => '',
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => true, // @todo 4.7.0 to remove in v4.8.0
            'failover'    => [],
            'port'        => 3306,
            'foreignKeys' => true,
            'synchronous' => 0,
        ],
        'SQLSRV' => [
            'DSN'      => '',
            'hostname' => 'localhost',
            'username' => 'sa',
            'password' => '1Secure*Password1',
            'database' => 'test',
            'DBDriver' => 'SQLSRV',
            'DBPrefix' => 'db_',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => '',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => true, // @todo 4.7.0 to remove in v4.8.0
            'failover' => [],
            'port'     => 1433,
        ],
        'OCI8' => [
            'DSN'      => 'localhost:1521/FREEPDB1',
            'hostname' => '',
            'username' => 'ORACLE',
            'password' => 'ORACLE',
            'database' => '',
            'DBDriver' => 'OCI8',
            'DBPrefix' => 'db_',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'AL32UTF8',
            'DBCollat' => '',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => true, // @todo 4.7.0 to remove in v4.8.0
            'failover' => [],
        ],
    ];

    /**
     * Override database config
     *
     * @return array<string, array<string, bool|int|string>|bool|int|string>
     */
    public static function Database(): array
    {
        $config = [];

        // Under GitHub Actions, we can set an ENV var named 'DB'
        // so that we can test against multiple databases.
        $group = env('DB', 'SQLite3');

        if ($group === 'Oracle') {
            $group = 'OCI8';
        }

        $dbParams = self::$dbConfig[$group] ?? [];

        if (! empty($dbParams) && $group !== 'SQLite3') {
            $componentName = '';

            foreach ($_SERVER['argv'] ?? [] as $arg) {
                if (str_contains($arg, 'tests/system/')) {
                    $parts = explode('tests/system/', $arg);
                    if (isset($parts[1])) {
                        $componentName = explode('/', $parts[1])[0];
                        break;
                    }
                }
            }

            if ($componentName !== '') {
                if ($group === 'OCI8') {
                    $compUser = strtoupper('t_' . substr(preg_replace('/[^a-zA-Z0-9]/', '', $componentName), 0, 20));
                    $tns      = '//' . $dbParams['hostname'] . ':' . $dbParams['port'] . '/' . $dbParams['database'];

                    try {
                        $conn = @oci_connect($dbParams['username'], $dbParams['password'], $tns);
                        if ($conn !== false) {
                            $stmt = @oci_parse($conn, 'SELECT USERNAME FROM ALL_USERS WHERE USERNAME = :usr');
                            @oci_bind_by_name($stmt, ':usr', $compUser);
                            @oci_execute($stmt);

                            if (@oci_fetch_array($stmt, OCI_ASSOC) === false) {
                                $stmt2 = @oci_parse($conn, 'CREATE USER ' . $compUser . ' IDENTIFIED BY ' . $compUser);
                                @oci_execute($stmt2);
                                $stmt3 = @oci_parse($conn, 'GRANT CONNECT, RESOURCE, DBA TO ' . $compUser);
                                @oci_execute($stmt3);
                                $stmt4 = @oci_parse($conn, 'GRANT UNLIMITED TABLESPACE TO ' . $compUser);
                                @oci_execute($stmt4);
                            }

                            @oci_close($conn);

                            $dbParams['username'] = $compUser;
                            $dbParams['password'] = $compUser;
                        }
                    } catch (Throwable) {
                        // Ignore error and fall back to default user
                    }
                } else {
                    $dbParams['database'] = 'test_' . strtolower($componentName);

                try {
                    if ($group === 'MySQLi') {
                        $conn = new mysqli(
                            $dbParams['hostname'],
                            $dbParams['username'],
                            $dbParams['password'],
                            '',
                            (int) $dbParams['port'],
                        );
                        if (! $conn->connect_error) {
                            $conn->query('CREATE DATABASE IF NOT EXISTS ' . $conn->real_escape_string($dbParams['database']));
                            $conn->close();
                        }
                    } elseif ($group === 'Postgre') {
                        $dsn = 'pgsql:host=' . $dbParams['hostname'] . ';port=' . $dbParams['port'] . ';user=' . $dbParams['username'] . ';password=' . $dbParams['password'];
                        $pdo = new PDO($dsn);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
                        $stmt->execute([$dbParams['database']]);
                        if (! $stmt->fetchColumn()) {
                            $dbName = str_replace('"', '""', $dbParams['database']);
                            $pdo->exec('CREATE DATABASE "' . $dbName . '"');
                        }
                    } elseif ($group === 'SQLSRV') {
                        $dsn = 'sqlsrv:Server=' . $dbParams['hostname'] . ',' . $dbParams['port'] . ';Encrypt=False;TrustServerCertificate=True';
                        $pdo = new PDO($dsn, $dbParams['username'], $dbParams['password']);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $stmt = $pdo->prepare('SELECT 1 FROM sys.databases WHERE name = ?');
                        $stmt->execute([$dbParams['database']]);
                        if (! $stmt->fetchColumn()) {
                            $pdo->exec('CREATE DATABASE [' . str_replace(']', ']]', $dbParams['database']) . '] COLLATE Latin1_General_100_CS_AS_SC_UTF8');
                        }
                    }
                } catch (Throwable) {
                    // Ignore any error and let the connection fail naturally
                }
            }
        }
        }

        $config['tests'] = $dbParams;

        return $config;
    }

    /**
     * Demonstrates Publisher security.
     *
     * @see PublisherRestrictionsTest::testRegistrarsNotAllowed()
     *
     * @return array<string, array<string, string>>
     */
    public static function Publisher(): array
    {
        return [
            'restrictions' => [SUPPORTPATH => '*'],
        ];
    }
}
