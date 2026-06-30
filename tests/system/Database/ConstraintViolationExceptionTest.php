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

use CodeIgniter\Database\Exceptions\CheckConstraintViolationException;
use CodeIgniter\Database\Exceptions\ConstraintViolationException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\ForeignKeyConstraintViolationException;
use CodeIgniter\Database\Exceptions\NotNullConstraintViolationException;
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

/**
 * @internal
 */
#[Group('Others')]
final class ConstraintViolationExceptionTest extends CIUnitTestCase
{
    public function testUniqueConstraintViolationExtendsConstraintViolation(): void
    {
        $exception = new UniqueConstraintViolationException();

        $this->assertInstanceOf(ConstraintViolationException::class, $exception);
    }

    /**
     * @param class-string<ConstraintViolationException> $expectedException
     */
    #[DataProvider('provideCreatesConstraintViolationExceptions')]
    public function testCreatesConstraintViolationExceptions(
        BaseConnection $db,
        int|string $code,
        string $message,
        string $expectedException,
    ): void {
        $exception = $db->createDatabaseException($message, $code);

        $this->assertSame($expectedException, $exception::class);
        $this->assertInstanceOf(ConstraintViolationException::class, $exception);
        $this->assertSame($code, $exception->getDatabaseCode());
    }

    /**
     * @return iterable<string, array{BaseConnection, int|string, string, class-string<ConstraintViolationException>}>
     */
    public static function provideCreatesConstraintViolationExceptions(): iterable
    {
        yield 'MySQLi unique constraint' => [
            self::connection(MySQLiConnection::class, 'MySQLi'),
            1062,
            'Duplicate entry.',
            UniqueConstraintViolationException::class,
        ];

        yield 'MySQLi foreign key parent row referenced' => [
            self::connection(MySQLiConnection::class, 'MySQLi'),
            1451,
            'Cannot delete or update a parent row: a foreign key constraint fails.',
            ForeignKeyConstraintViolationException::class,
        ];

        yield 'MySQLi foreign key child row missing parent' => [
            self::connection(MySQLiConnection::class, 'MySQLi'),
            1452,
            'Cannot add or update a child row: a foreign key constraint fails.',
            ForeignKeyConstraintViolationException::class,
        ];

        yield 'MySQLi not null' => [
            self::connection(MySQLiConnection::class, 'MySQLi'),
            1048,
            "Column 'name' cannot be null",
            NotNullConstraintViolationException::class,
        ];

        yield 'MySQLi check' => [
            self::connection(MySQLiConnection::class, 'MySQLi'),
            3819,
            "Check constraint 'positive_amount' is violated.",
            CheckConstraintViolationException::class,
        ];

        yield 'Postgre foreign key' => [
            self::connection(PostgreConnection::class, 'Postgre'),
            '23503',
            'Foreign key violation.',
            ForeignKeyConstraintViolationException::class,
        ];

        yield 'Postgre unique constraint' => [
            self::connection(PostgreConnection::class, 'Postgre'),
            '23505',
            'Unique violation.',
            UniqueConstraintViolationException::class,
        ];

        yield 'Postgre not null' => [
            self::connection(PostgreConnection::class, 'Postgre'),
            '23502',
            'Not-null violation.',
            NotNullConstraintViolationException::class,
        ];

        yield 'Postgre check' => [
            self::connection(PostgreConnection::class, 'Postgre'),
            '23514',
            'Check violation.',
            CheckConstraintViolationException::class,
        ];

        yield 'Postgre generic constraint' => [
            self::connection(PostgreConnection::class, 'Postgre'),
            '23P01',
            'Exclusion violation.',
            ConstraintViolationException::class,
        ];

        yield 'SQLite foreign key' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'FOREIGN KEY constraint failed',
            ForeignKeyConstraintViolationException::class,
        ];

        yield 'SQLite unique constraint' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'UNIQUE constraint failed: table.column',
            UniqueConstraintViolationException::class,
        ];

        yield 'SQLite legacy unique constraint' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'column email is not unique',
            UniqueConstraintViolationException::class,
        ];

        yield 'SQLite not null' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'NOT NULL constraint failed: user.name',
            NotNullConstraintViolationException::class,
        ];

        yield 'SQLite check' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'CHECK constraint failed: positive_amount',
            CheckConstraintViolationException::class,
        ];

        yield 'SQLite generic constraint' => [
            self::connection(SQLite3Connection::class, 'SQLite3'),
            19,
            'constraint failed',
            ConstraintViolationException::class,
        ];

        yield 'SQLSRV not null' => [
            self::connection(SQLSRVConnection::class, 'SQLSRV'),
            '23000/515',
            'Cannot insert the value NULL into column.',
            NotNullConstraintViolationException::class,
        ];

        yield 'SQLSRV unique constraint' => [
            self::connection(SQLSRVConnection::class, 'SQLSRV'),
            '23000/2627',
            'Violation of UNIQUE KEY constraint.',
            UniqueConstraintViolationException::class,
        ];

        yield 'SQLSRV unique index' => [
            self::connection(SQLSRVConnection::class, 'SQLSRV'),
            '23000/2601',
            'Cannot insert duplicate key row.',
            UniqueConstraintViolationException::class,
        ];

        yield 'SQLSRV generic constraint' => [
            self::connection(SQLSRVConnection::class, 'SQLSRV'),
            '23000/547',
            'The INSERT statement conflicted with the constraint.',
            ConstraintViolationException::class,
        ];

        yield 'SQLSRV generic constraint SQLSTATE' => [
            self::connection(SQLSRVConnection::class, 'SQLSRV'),
            '23000',
            'Integrity constraint violation.',
            ConstraintViolationException::class,
        ];

        if (defined('OCI_COMMIT_ON_SUCCESS')) {
            yield 'OCI8 unique constraint' => [
                self::connection(OCI8Connection::class, 'OCI8'),
                1,
                'Unique constraint violated.',
                UniqueConstraintViolationException::class,
            ];

            yield 'OCI8 unique constraint string code' => [
                self::connection(OCI8Connection::class, 'OCI8'),
                '1',
                'Unique constraint violated.',
                UniqueConstraintViolationException::class,
            ];

            yield 'OCI8 foreign key parent key not found' => [
                self::connection(OCI8Connection::class, 'OCI8'),
                2291,
                'Integrity constraint violated - parent key not found.',
                ForeignKeyConstraintViolationException::class,
            ];

            yield 'OCI8 foreign key child record found' => [
                self::connection(OCI8Connection::class, 'OCI8'),
                2292,
                'Integrity constraint violated - child record found.',
                ForeignKeyConstraintViolationException::class,
            ];

            yield 'OCI8 not null' => [
                self::connection(OCI8Connection::class, 'OCI8'),
                1400,
                'Cannot insert NULL.',
                NotNullConstraintViolationException::class,
            ];

            yield 'OCI8 check' => [
                self::connection(OCI8Connection::class, 'OCI8'),
                2290,
                'Check constraint violated.',
                CheckConstraintViolationException::class,
            ];
        }
    }

    public function testCreatesBaseDatabaseExceptionForNonConstraintError(): void
    {
        $exception = self::connection(MockConnection::class, 'MockDriver')
            ->createDatabaseException('Syntax error.', 1064);

        $this->assertInstanceOf(DatabaseException::class, $exception);
        $this->assertNotInstanceOf(ConstraintViolationException::class, $exception);
    }

    #[DataProvider('provideSqlsrvConstraintVendorCodesWithNonConstraintSqlstate')]
    public function testCreatesBaseDatabaseExceptionForSqlsrvConstraintVendorCodeWithNonConstraintSqlstate(string $code): void
    {
        $exception = self::connection(SQLSRVConnection::class, 'SQLSRV')
            ->createDatabaseException('General error.', $code);

        $this->assertInstanceOf(DatabaseException::class, $exception);
        $this->assertNotInstanceOf(ConstraintViolationException::class, $exception);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideSqlsrvConstraintVendorCodesWithNonConstraintSqlstate(): iterable
    {
        yield 'unique constraint' => ['HY000/2627'];
        yield 'unique index' => ['HY000/2601'];
        yield 'not null' => ['HY000/515'];
        yield 'generic constraint' => ['HY000/547'];
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
    private static function config(string $driver): array
    {
        return [
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
        ];
    }
}
