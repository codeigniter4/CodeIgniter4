<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Config;

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
     * @var array
     */
    protected static $dbConfig = [
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
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
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
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
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
            'DBCollat'    => 'utf8_general_ci',
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => false,
            'failover'    => [],
            'port'        => 3306,
            'foreignKeys' => true,
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
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => 1433,
        ],
        'OCI8' => [
            'DSN'      => 'localhost:1521/XEPDB1',
            'hostname' => '',
            'username' => 'ORACLE',
            'password' => 'ORACLE',
            'database' => '',
            'DBDriver' => 'OCI8',
            'DBPrefix' => 'db_',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'AL32UTF8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
        ],
    ];

    /**
     * Override database config
     *
     * @return array
     */
    public static function Database()
    {
        $config = [];

        // Under GitHub Actions, we can set an ENV var named 'DB'
        // so that we can test against multiple databases.
        if ($group = getenv('DB')) {
            if (! empty(self::$dbConfig[$group])) {
                $config['tests'] = self::$dbConfig[$group];
            }
        }

        return $config;
    }

    /**
     * Demonstrates Publisher security.
     *
     * @see PublisherRestrictionsTest::testRegistrarsNotAllowed()
     *
     * @return array
     */
    public static function Publisher()
    {
        return [
            'restrictions' => [SUPPORTPATH => '*'],
        ];
    }
}
