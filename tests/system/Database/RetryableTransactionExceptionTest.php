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
use CodeIgniter\Database\Exceptions\RetryableTransactionException;
use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;
use CodeIgniter\Database\MySQLi\Connection as MySQLiConnection;
use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Database\Postgre\Connection as PostgreConnection;
use CodeIgniter\Database\SQLite3\Connection as SQLite3Connection;
use CodeIgniter\Database\SQLSRV\Connection as SQLSRVConnection;
use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use ErrorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Mock\MockPreparedQuery;

/**
 * @internal
 */
#[Group('Others')]
final class RetryableTransactionExceptionTest extends CIUnitTestCase
{
    #[DataProvider('provideCreatesUniqueConstraintViolationExceptions')]
    public function testCreatesUniqueConstraintViolationExceptions(
        BaseConnection $db,
        int|string $code,
        string $message,
    ): void {
        $exception = self::createDatabaseException($db, $message, $code);

        $this->assertInstanceOf(UniqueConstraintViolationException::class, $exception);
        $this->assertSame($code, $exception->getDatabaseCode());
    }

    /**
     * @return iterable<string, array{BaseConnection, int|string, string}>
     */
    public static function provideCreatesUniqueConstraintViolationExceptions(): iterable
    {
        yield 'MySQLi duplicate key' => [
            self::connection(MySQLiConnection::class, 'MySQLi'),
            1062,
            'Duplicate entry.',
        ];

        yield 'Postgre unique violation' => [
            self::connection(PostgreConnection::class, 'Postgre'),
            '23505',
            'Unique violation.',
        ];

        yield 'SQLite unique constraint' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'UNIQUE constraint failed: table.column',
        ];

        yield 'SQLite legacy unique constraint' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'column email is not unique',
        ];

        yield 'SQLSRV unique constraint' => [
            self::connection(SQLSRVConnection::class, 'SQLSRV'),
            '23000/2627',
            'Violation of UNIQUE KEY constraint.',
        ];

        yield 'SQLSRV unique index' => [
            self::connection(SQLSRVConnection::class, 'SQLSRV'),
            '23000/2601',
            'Cannot insert duplicate key row.',
        ];

        if (defined('OCI_COMMIT_ON_SUCCESS')) {
            yield 'OCI8 unique constraint' => [
                self::connection(OCI8Connection::class, 'OCI8'),
                1,
                'Unique constraint violated.',
            ];
        }
    }

    #[DataProvider('provideCreatesRetryableTransactionExceptions')]
    public function testCreatesRetryableTransactionExceptions(BaseConnection $db, int|string $code): void
    {
        $exception = self::createDatabaseException($db, 'Retryable transaction failure.', $code);

        $this->assertInstanceOf(RetryableTransactionException::class, $exception);
        $this->assertSame($code, $exception->getDatabaseCode());
    }

    /**
     * @return iterable<string, array{BaseConnection, int|string}>
     */
    public static function provideCreatesRetryableTransactionExceptions(): iterable
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

    #[DataProvider('provideCreatesBaseDatabaseExceptionsForNonRetryableErrors')]
    public function testCreatesBaseDatabaseExceptionsForNonRetryableErrors(BaseConnection $db, int|string $code): void
    {
        $exception = self::createDatabaseException($db, 'Non-retryable transaction failure.', $code);

        $this->assertNotInstanceOf(RetryableTransactionException::class, $exception);
    }

    /**
     * @return iterable<string, array{BaseConnection, int|string}>
     */
    public static function provideCreatesBaseDatabaseExceptionsForNonRetryableErrors(): iterable
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

    public function testQueryThrowsRetryableTransactionExceptionFromDriverExecutionPath(): void
    {
        $db = $this->getMockBuilder(MySQLiConnection::class)
            ->setConstructorArgs([self::config('MySQLi')])
            ->onlyMethods(['connect', 'execute'])
            ->getMock();

        $db->method('connect')->willReturn(mysqli_init());
        $db->method('execute')->willThrowException(
            self::createDatabaseException($db, 'Deadlock found when trying to get lock.', 1213),
        );

        $this->expectException(RetryableTransactionException::class);

        $db->query('SELECT * FROM test');
    }

    public function testPreparedQueryThrowsRetryableTransactionExceptionFromBaseExecutionPath(): void
    {
        $preparedQuery                  = new MockPreparedQuery(self::connection(MySQLiConnection::class, 'MySQLi'));
        $preparedQuery->thrownException = new ErrorException('Deadlock found when trying to get lock.', 1213);

        $preparedQuery->prepare('SELECT 1');

        $this->expectException(RetryableTransactionException::class);

        $preparedQuery->execute();
    }

    public function testPreparedQueryRoutesDriverDatabaseExceptionThroughBaseExecutionPath(): void
    {
        $db                             = self::connection(MySQLiConnection::class, 'MySQLi');
        $preparedQuery                  = new MockPreparedQuery($db);
        $preparedQuery->thrownException = self::createDatabaseException($db, 'Deadlock found when trying to get lock.', 1213);
        $queryCount                     = 0;
        $listener                       = static function () use (&$queryCount): void {
            $queryCount++;
        };

        $preparedQuery->prepare('SELECT 1');
        Events::on('DBQuery', $listener);

        try {
            $preparedQuery->execute();
            $this->fail('Expected retryable transaction exception was not thrown.');
        } catch (RetryableTransactionException $e) {
            $this->assertSame($preparedQuery->thrownException, $e);
        } finally {
            Events::removeListener('DBQuery', $listener);
        }

        $this->assertSame(1, $queryCount);
    }

    public function testPreparedQueryStoresRetryableTransactionExceptionWithDebugDisabled(): void
    {
        $db = new MySQLiConnection(self::config('MySQLi', false));

        $preparedQuery                  = new MockPreparedQuery($db);
        $preparedQuery->thrownException = new ErrorException('Deadlock found when trying to get lock.', 1213);

        $preparedQuery->prepare('SELECT 1');

        $this->assertFalse($preparedQuery->execute());
        $this->assertInstanceOf(RetryableTransactionException::class, $db->getLastException());
    }

    /**
     * @param class-string<BaseConnection> $connectionClass
     */
    private static function connection(string $connectionClass, string $driver): BaseConnection
    {
        return new $connectionClass(self::config($driver));
    }

    /**
     * @return array<string, mixed>
     */
    private static function config(string $driver, bool $debug = true): array
    {
        return [
            'DSN'      => '',
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'database' => 'test',
            'DBDriver' => $driver,
            'DBDebug'  => $debug,
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'failover' => [],
        ];
    }

    private static function createDatabaseException(BaseConnection $db, string $message, int|string $code): DatabaseException
    {
        return $db->createDatabaseException($message, $code);
    }
}
