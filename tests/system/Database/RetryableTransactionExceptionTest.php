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

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;
use CodeIgniter\Database\MySQLi\Connection as MySQLiConnection;
use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Database\Postgre\Connection as PostgreConnection;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use CodeIgniter\Database\SQLSRV\Connection as SQLSRVConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * @internal
 */
#[Group('Others')]
final class RetryableTransactionExceptionTest extends CIUnitTestCase
{
    #[DataProvider('provideRecognizesRetryableTransactionExceptions')]
    public function testRecognizesRetryableTransactionExceptions(BaseConnection $db, int|string $code): void
    {
        $exception = new DatabaseException('Retryable transaction failure.', $code);

        $this->assertTrue($db->isRetryableTransactionException($exception));
    }

    /**
     * @return iterable<string, array{BaseConnection, int|string}>
     */
    public static function provideRecognizesRetryableTransactionExceptions(): iterable
    {
        yield 'MySQLi deadlock' => [self::connection(MySQLiConnection::class, 'MySQLi'), 1213];

        yield 'Postgre serialization failure' => [self::connection(PostgreConnection::class, 'Postgre'), '40001'];

        yield 'Postgre deadlock' => [self::connection(PostgreConnection::class, 'Postgre'), '40P01'];

        yield 'SQLite busy' => [self::connection(SQLite3Connection::class, 'SQLite3'), 5];

        yield 'SQLSRV deadlock' => [self::connection(SQLSRVConnection::class, 'SQLSRV'), '40001/1205'];

        yield 'SQLSRV vendor deadlock' => [self::connection(SQLSRVConnection::class, 'SQLSRV'), 1205];

        yield 'SQLSRV snapshot isolation conflict' => [self::connection(SQLSRVConnection::class, 'SQLSRV'), 'HY000/3960'];

        if (defined('OCI_COMMIT_ON_SUCCESS')) {
            yield 'OCI8 deadlock' => [self::connection(OCI8Connection::class, 'OCI8'), 60];

            yield 'OCI8 serialization failure' => [self::connection(OCI8Connection::class, 'OCI8'), 8177];
        }
    }

    #[DataProvider('provideRejectsNonRetryableTransactionExceptions')]
    public function testRejectsNonRetryableTransactionExceptions(BaseConnection $db, int|string $code): void
    {
        $exception = new DatabaseException('Non-retryable transaction failure.', $code);

        $this->assertFalse($db->isRetryableTransactionException($exception));
    }

    /**
     * @return iterable<string, array{BaseConnection, int|string}>
     */
    public static function provideRejectsNonRetryableTransactionExceptions(): iterable
    {
        yield 'Base connection default' => [self::connection(MockConnection::class, 'MockDriver'), 1213];

        yield 'MySQLi lock wait timeout' => [self::connection(MySQLiConnection::class, 'MySQLi'), 1205];

        yield 'MySQLi duplicate key' => [self::connection(MySQLiConnection::class, 'MySQLi'), 1062];

        yield 'Postgre unique violation' => [self::connection(PostgreConnection::class, 'Postgre'), '23505'];

        yield 'Postgre exclusion violation' => [self::connection(PostgreConnection::class, 'Postgre'), '23P01'];

        yield 'SQLite locked' => [self::connection(SQLite3Connection::class, 'SQLite3'), 6];

        yield 'SQLite busy snapshot extended code' => [self::connection(SQLite3Connection::class, 'SQLite3'), 517];

        yield 'SQLite constraint' => [self::connection(SQLite3Connection::class, 'SQLite3'), 19];

        yield 'SQLSRV lock timeout' => [self::connection(SQLSRVConnection::class, 'SQLSRV'), 'HYT00/1222'];

        yield 'SQLSRV SQLSTATE without vendor code' => [self::connection(SQLSRVConnection::class, 'SQLSRV'), '40001'];

        yield 'SQLSRV unique constraint' => [self::connection(SQLSRVConnection::class, 'SQLSRV'), '23000/2627'];

        yield 'SQLSRV unique index' => [self::connection(SQLSRVConnection::class, 'SQLSRV'), '23000/2601'];

        if (defined('OCI_COMMIT_ON_SUCCESS')) {
            yield 'OCI8 resource busy' => [self::connection(OCI8Connection::class, 'OCI8'), 54];

            yield 'OCI8 unique constraint' => [self::connection(OCI8Connection::class, 'OCI8'), 1];
        }
    }

    public function testRejectsNonDatabaseExceptions(): void
    {
        $db = self::connection(MySQLiConnection::class, 'MySQLi');

        $this->assertFalse($db->isRetryableTransactionException(new RuntimeException('Not a database exception.')));
    }

    public function testRejectsUniqueConstraintViolationExceptions(): void
    {
        $db = self::connection(MySQLiConnection::class, 'MySQLi');

        $this->assertFalse($db->isRetryableTransactionException(
            new UniqueConstraintViolationException('Duplicate key.', 1062),
        ));
    }

    /**
     * @param class-string<BaseConnection> $connectionClass
     */
    private static function connection(string $connectionClass, string $driver): BaseConnection
    {
        return new $connectionClass([
            'DSN'      => '',
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'database' => 'test',
            'DBDriver' => $driver,
            'DBDebug'  => true,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'failover' => [],
        ]);
    }
}
