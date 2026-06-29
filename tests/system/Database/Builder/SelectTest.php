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

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\MySQLi\Builder as MySQLiBuilder;
use CodeIgniter\Database\OCI8\Builder as OCI8Builder;
use CodeIgniter\Database\Postgre\Builder as PostgreBuilder;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Database\SQLite3\Builder as SQLite3Builder;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class SelectTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleSelect(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectOnlyOneColumn(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select('name');

        $expected = 'SELECT "name" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectAcceptsArray(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select(['name', 'role']);

        $expected = 'SELECT "name", "role" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    /**
     * @param list<RawSql|string> $select
     */
    #[DataProvider('provideSelectAcceptsArrayWithRawSql')]
    public function testSelectAcceptsArrayWithRawSql(array $select, string $expected): void
    {
        $builder = new BaseBuilder('employees', $this->db);

        $builder->select($select);

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    /**
     * @return iterable<array{0: list<RawSql|string>, 1: string}>
     */
    public static function provideSelectAcceptsArrayWithRawSql(): iterable
    {
        yield from [
            [
                [
                    new RawSql("IF(salary > 5000, 'High', 'Low') AS salary_level"),
                    'employee_id',
                ],
                <<<'SQL'
                    SELECT IF(salary > 5000, 'High', 'Low') AS salary_level, "employee_id" FROM "employees"
                    SQL,
            ],
            [
                [
                    'employee_id',
                    new RawSql("IF(salary > 5000, 'High', 'Low') AS salary_level"),
                ],
                <<<'SQL'
                    SELECT "employee_id", IF(salary > 5000, 'High', 'Low') AS salary_level FROM "employees"
                    SQL,
            ],
            [
                [
                    new RawSql("CONCAT(first_name, ' ', last_name) AS full_name"),
                    new RawSql("IF(salary > 5000, 'High', 'Low') AS salary_level"),
                ],
                <<<'SQL'
                    SELECT CONCAT(first_name, ' ', last_name) AS full_name, IF(salary > 5000, 'High', 'Low') AS salary_level FROM "employees"
                    SQL,
            ],
            [
                [
                    new RawSql("CONCAT(first_name, ' ', last_name) AS full_name"),
                    'employee_id',
                    new RawSql("IF(salary > 5000, 'High', 'Low') AS salary_level"),
                ],
                <<<'SQL'
                    SELECT CONCAT(first_name, ' ', last_name) AS full_name, "employee_id", IF(salary > 5000, 'High', 'Low') AS salary_level FROM "employees"
                    SQL,
            ],
        ];
    }

    public function testSelectAcceptsMultipleColumns(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select('name, role');

        $expected = 'SELECT "name", "role" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectKeepsAliases(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select('name, role as myRole');

        $expected = 'SELECT "name", "role" as "myRole" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectWorksWithComplexSelects(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select('(SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid');

        $expected = 'SELECT (SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectNullAsInString(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select('NULL as field_alias, name');

        $expected = 'SELECT NULL as field_alias, "name" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectNullAsInArray(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select(['NULL as field_alias', 'name']);

        $expected = 'SELECT NULL as field_alias, "name" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4355
     */
    public function testSelectWorksWithRawSql(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $sql = 'REGEXP_SUBSTR(ral_anno,"[0-9]{1,2}([,.][0-9]{1,3})([,.][0-9]{1,3})") AS ral';
        $builder->select(new RawSql($sql));

        $expected = 'SELECT ' . $sql . ' FROM "users"';
        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4311
     */
    public function testSelectWorksWithEscpaeFalse(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select('"numericValue1" + "numericValue2" AS "numericResult"', false);

        $expected = 'SELECT "numericValue1" + "numericValue2" AS "numericResult" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4355
     */
    public function testSelectRegularExpressionWorksWithEscpaeFalse(): void
    {
        $builder = new BaseBuilder('ob_human_resources', $this->db);

        $builder->select(
            'REGEXP_SUBSTR(ral_anno,"[0-9]{1,2}([,.][0-9]{1,3})([,.][0-9]{1,3})") AS ral',
            false,
        );

        $expected = <<<'SQL'
            SELECT REGEXP_SUBSTR(ral_anno,"[0-9]{1,2}([,.][0-9]{1,3})([,.][0-9]{1,3})") AS ral
            FROM "ob_human_resources"
            SQL;
        $this->assertSame($expected, $builder->getCompiledSelect());
    }

    public function testSelectMinWithNoAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectMin('payments');

        $expected = 'SELECT MIN("payments") AS "payments" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectMinWithAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectMin('payments', 'myAlias');

        $expected = 'SELECT MIN("payments") AS "myAlias" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectMaxWithNoAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectMax('payments');

        $expected = 'SELECT MAX("payments") AS "payments" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectMaxWithAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectMax('payments', 'myAlias');

        $expected = 'SELECT MAX("payments") AS "myAlias" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectAvgWithNoAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectAvg('payments');

        $expected = 'SELECT AVG("payments") AS "payments" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectAvgWithAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectAvg('payments', 'myAlias');

        $expected = 'SELECT AVG("payments") AS "myAlias" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectSumWithNoAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectSum('payments');

        $expected = 'SELECT SUM("payments") AS "payments" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectSumWithAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectSum('payments', 'myAlias');

        $expected = 'SELECT SUM("payments") AS "myAlias" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectCountWithNoAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectCount('payments');

        $expected = 'SELECT COUNT("payments") AS "payments" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectCountWithAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectCount('payments', 'myAlias');

        $expected = 'SELECT COUNT("payments") AS "myAlias" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectMinThrowsExceptionOnEmptyValue(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Empty statement is given for the field "Select"');

        $builder->selectSum('');
    }

    public function testSelectMaxWithDotNameAndNoAlias(): void
    {
        $builder = new BaseBuilder('invoices', $this->db);

        $builder->selectMax('db.payments');

        $expected = 'SELECT MAX("db"."payments") AS "payments" FROM "invoices"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectMinThrowsExceptionOnMultipleColumn(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('You must provide a valid "column name not separated by comma".');

        $builder->selectSum('name,role');
    }

    public function testSimpleSelectWithSQLSRV(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testLockForUpdate(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->where('id', 1)->orderBy('id', 'ASC')->limit(1)->lockForUpdate();

        $expected = 'SELECT * FROM "users" WHERE "id" = 1 ORDER BY "id" ASC  LIMIT 1 FOR UPDATE';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testLockForUpdatePersistsWhenSelectIsNotReset(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->lockForUpdate();

        $expected = 'SELECT * FROM "users" FOR UPDATE';

        $this->assertSameSql($expected, $builder->getCompiledSelect(false));
        $this->assertSameSql($expected, $builder->getCompiledSelect(false));
    }

    public function testLockForUpdateResetsWithSelect(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->lockForUpdate();

        $this->assertSameSql('SELECT * FROM "users" FOR UPDATE', $builder->getCompiledSelect());
        $this->assertSameSql('SELECT * FROM "users"', $builder->getCompiledSelect());
    }

    public function testSharedLock(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->where('id', 1)->orderBy('id', 'ASC')->limit(1)->sharedLock();

        $expected = 'SELECT * FROM "users" WHERE "id" = 1 ORDER BY "id" ASC  LIMIT 1 FOR SHARE';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSharedLockPersistsWhenSelectIsNotReset(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->sharedLock();

        $expected = 'SELECT * FROM "users" FOR SHARE';

        $this->assertSameSql($expected, $builder->getCompiledSelect(false));
        $this->assertSameSql($expected, $builder->getCompiledSelect(false));
    }

    public function testSharedLockResetsWithSelect(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->sharedLock();

        $this->assertSameSql('SELECT * FROM "users" FOR SHARE', $builder->getCompiledSelect());
        $this->assertSameSql('SELECT * FROM "users"', $builder->getCompiledSelect());
    }

    public function testLockForUpdateWithNowait(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR UPDATE NOWAIT', $builder->lockForUpdate()->nowait()->getCompiledSelect());
    }

    public function testLockForUpdateWithSkipLocked(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR UPDATE SKIP LOCKED', $builder->lockForUpdate()->skipLocked()->getCompiledSelect());
    }

    public function testSharedLockWithNowait(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR SHARE NOWAIT', $builder->sharedLock()->nowait()->getCompiledSelect());
    }

    public function testSharedLockWithSkipLocked(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR SHARE SKIP LOCKED', $builder->sharedLock()->skipLocked()->getCompiledSelect());
    }

    public function testSelectLockWaitPersistsWhenSelectIsNotReset(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->lockForUpdate()->skipLocked();

        $expected = 'SELECT * FROM "users" FOR UPDATE SKIP LOCKED';

        $this->assertSameSql($expected, $builder->getCompiledSelect(false));
        $this->assertSameSql($expected, $builder->getCompiledSelect(false));
    }

    public function testSelectLockWaitResetsWithSelect(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->lockForUpdate()->nowait();

        $this->assertSameSql('SELECT * FROM "users" FOR UPDATE NOWAIT', $builder->getCompiledSelect());
        $this->assertSameSql('SELECT * FROM "users"', $builder->getCompiledSelect());
    }

    public function testSelectLockWaitLastCallWins(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR UPDATE NOWAIT', $builder->lockForUpdate()->skipLocked()->nowait()->getCompiledSelect());

        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR UPDATE SKIP LOCKED', $builder->lockForUpdate()->nowait()->skipLocked()->getCompiledSelect());
    }

    public function testNowaitThrowsExceptionWithoutSelectLock(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Query Builder does not support nowait() without lockForUpdate() or sharedLock().');

        $builder->nowait()->getCompiledSelect();
    }

    public function testSkipLockedThrowsExceptionWithoutSelectLock(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Query Builder does not support skipLocked() without lockForUpdate() or sharedLock().');

        $builder->skipLocked()->getCompiledSelect();
    }

    public function testSelectLockLastCallWins(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR UPDATE', $builder->sharedLock()->lockForUpdate()->getCompiledSelect());

        $builder = new BaseBuilder('users', $this->db);

        $this->assertSameSql('SELECT * FROM "users" FOR SHARE', $builder->lockForUpdate()->sharedLock()->getCompiledSelect());
    }

    public function testLockForUpdateThrowsExceptionWithUnion(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Query Builder does not support lockForUpdate() with union() or unionAll().');

        $builder->union(new BaseBuilder('jobs', $this->db))->lockForUpdate()->getCompiledSelect();
    }

    public function testSharedLockThrowsExceptionWithUnion(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Query Builder does not support sharedLock() with union() or unionAll().');

        $builder->union(new BaseBuilder('jobs', $this->db))->sharedLock()->getCompiledSelect();
    }

    public function testLockForUpdateThrowsExceptionWithSQLSRVUnion(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Query Builder does not support lockForUpdate() with union() or unionAll().');

        $builder->union(new SQLSRVBuilder('jobs', $this->db))->lockForUpdate()->getCompiledSelect();
    }

    public function testSharedLockThrowsExceptionWithSQLSRVUnion(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Query Builder does not support sharedLock() with union() or unionAll().');

        $builder->union(new SQLSRVBuilder('jobs', $this->db))->sharedLock()->getCompiledSelect();
    }

    public function testLockForUpdateThrowsExceptionOnMySQLiSubquery(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'MySQLi']);

        $subquery = new MySQLiBuilder('users', $this->db);
        $builder  = new MySQLiBuilder('jobs', $this->db);

        $builder->fromSubquery($subquery, 'users_1');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('MySQLi does not support lockForUpdate() with fromSubquery().');

        $builder->lockForUpdate()->getCompiledSelect();
    }

    public function testSharedLockThrowsExceptionOnMySQLiSubquery(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'MySQLi']);

        $subquery = new MySQLiBuilder('users', $this->db);
        $builder  = new MySQLiBuilder('jobs', $this->db);

        $builder->fromSubquery($subquery, 'users_1');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('MySQLi does not support sharedLock() with fromSubquery().');

        $builder->sharedLock()->getCompiledSelect();
    }

    public function testSharedLockWithMySQLi(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'MySQLi']);

        $builder = new MySQLiBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" LOCK IN SHARE MODE';

        $this->assertSameSql($expected, $builder->sharedLock()->getCompiledSelect());
    }

    public function testLockForUpdateWithMySQLiNowait(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'MySQLi']);

        $builder = new MySQLiBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE NOWAIT';

        $this->assertSameSql($expected, $builder->lockForUpdate()->nowait()->getCompiledSelect());
    }

    public function testLockForUpdateWithMySQLiSkipLocked(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'MySQLi']);

        $builder = new MySQLiBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE SKIP LOCKED';

        $this->assertSameSql($expected, $builder->lockForUpdate()->skipLocked()->getCompiledSelect());
    }

    public function testSharedLockWithMySQLiNowaitThrowsException(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'MySQLi']);

        $builder = new MySQLiBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('MySQLi does not support sharedLock() with nowait().');

        $builder->sharedLock()->nowait()->getCompiledSelect();
    }

    public function testSharedLockWithMySQLiSkipLockedThrowsException(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'MySQLi']);

        $builder = new MySQLiBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('MySQLi does not support sharedLock() with skipLocked().');

        $builder->sharedLock()->skipLocked()->getCompiledSelect();
    }

    public function testLockForUpdateWithOCI8(): void
    {
        $builder = new OCI8Builder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE';

        $this->assertSameSql($expected, $builder->lockForUpdate()->getCompiledSelect());
    }

    public function testLockForUpdateWithOCI8Nowait(): void
    {
        $builder = new OCI8Builder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE NOWAIT';

        $this->assertSameSql($expected, $builder->lockForUpdate()->nowait()->getCompiledSelect());
    }

    public function testLockForUpdateWithOCI8SkipLocked(): void
    {
        $builder = new OCI8Builder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE SKIP LOCKED';

        $this->assertSameSql($expected, $builder->lockForUpdate()->skipLocked()->getCompiledSelect());
    }

    public function testSharedLockThrowsExceptionOnOCI8(): void
    {
        $builder = new OCI8Builder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('OCI8 does not support sharedLock().');

        $builder->sharedLock()->getCompiledSelect();
    }

    public function testLockForUpdateThrowsExceptionWithOCI8Limit(): void
    {
        $builder = new OCI8Builder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('OCI8 does not support lockForUpdate() with limit() or offset().');

        $builder->limit(1)->lockForUpdate()->getCompiledSelect();
    }

    #[DataProvider('provideSelectLockUnsupportedSelectClauses')]
    public function testLockForUpdateThrowsExceptionWithOCI8SelectClause(string $clause): void
    {
        $builder = new OCI8Builder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('OCI8 does not support lockForUpdate() with distinct(), groupBy(), having(), or aggregate helper selections.');

        $this->applySelectLockUnsupportedClause($builder, $clause)
            ->lockForUpdate()
            ->getCompiledSelect();
    }

    public function testLockForUpdateWithPostgre(): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE';

        $this->assertSameSql($expected, $builder->lockForUpdate()->getCompiledSelect());
    }

    public function testSharedLockWithPostgre(): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR SHARE';

        $this->assertSameSql($expected, $builder->sharedLock()->getCompiledSelect());
    }

    public function testLockForUpdateWithPostgreNowait(): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE NOWAIT';

        $this->assertSameSql($expected, $builder->lockForUpdate()->nowait()->getCompiledSelect());
    }

    public function testLockForUpdateWithPostgreSkipLocked(): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR UPDATE SKIP LOCKED';

        $this->assertSameSql($expected, $builder->lockForUpdate()->skipLocked()->getCompiledSelect());
    }

    public function testSharedLockWithPostgreNowait(): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR SHARE NOWAIT';

        $this->assertSameSql($expected, $builder->sharedLock()->nowait()->getCompiledSelect());
    }

    public function testSharedLockWithPostgreSkipLocked(): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $expected = 'SELECT * FROM "users" FOR SHARE SKIP LOCKED';

        $this->assertSameSql($expected, $builder->sharedLock()->skipLocked()->getCompiledSelect());
    }

    #[DataProvider('provideSelectLockUnsupportedSelectClauses')]
    public function testLockForUpdateThrowsExceptionWithPostgreSelectClause(string $clause): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Postgre does not support lockForUpdate() with distinct(), groupBy(), having(), or aggregate helper selections.');

        $this->applySelectLockUnsupportedClause($builder, $clause)
            ->lockForUpdate()
            ->getCompiledSelect();
    }

    #[DataProvider('provideSelectLockUnsupportedSelectClauses')]
    public function testSharedLockThrowsExceptionWithPostgreSelectClause(string $clause): void
    {
        $builder = new PostgreBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Postgre does not support sharedLock() with distinct(), groupBy(), having(), or aggregate helper selections.');

        $this->applySelectLockUnsupportedClause($builder, $clause)
            ->sharedLock()
            ->getCompiledSelect();
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function provideSelectLockUnsupportedSelectClauses(): iterable
    {
        yield 'distinct' => ['distinct'];

        yield 'groupBy' => ['groupBy'];

        yield 'having' => ['having'];

        yield 'aggregate selection' => ['aggregate'];
    }

    private function applySelectLockUnsupportedClause(BaseBuilder $builder, string $clause): BaseBuilder
    {
        return match ($clause) {
            'distinct'  => $builder->distinct(),
            'groupBy'   => $builder->groupBy('role'),
            'having'    => $builder->having('COUNT(id) >', 1, false),
            'aggregate' => $builder->selectCount('id'),
            default     => throw new DatabaseException('Unsupported clause: ' . $clause),
        };
    }

    public function testLockForUpdateThrowsExceptionOnSQLite3(): void
    {
        $builder = new SQLite3Builder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLite3 does not support lockForUpdate().');

        $builder->lockForUpdate()->getCompiledSelect();
    }

    public function testSharedLockThrowsExceptionOnSQLite3(): void
    {
        $builder = new SQLite3Builder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLite3 does not support sharedLock().');

        $builder->sharedLock()->getCompiledSelect();
    }

    public function testNowaitThrowsExceptionWithoutSelectLockOnSQLite3(): void
    {
        $builder = new SQLite3Builder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Query Builder does not support nowait() without lockForUpdate() or sharedLock().');

        $builder->nowait()->getCompiledSelect();
    }

    public function testLockForUpdateWithSQLSRV(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users" WITH (UPDLOCK, ROWLOCK)';

        $this->assertSameSql($expected, $builder->lockForUpdate()->getCompiledSelect());
    }

    public function testSharedLockWithSQLSRV(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users" WITH (HOLDLOCK, ROWLOCK)';

        $this->assertSameSql($expected, $builder->sharedLock()->getCompiledSelect());
    }

    public function testLockForUpdateWithSQLSRVNowait(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users" WITH (UPDLOCK, ROWLOCK, NOWAIT)';

        $this->assertSameSql($expected, $builder->lockForUpdate()->nowait()->getCompiledSelect());
    }

    public function testLockForUpdateWithSQLSRVSkipLocked(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users" WITH (UPDLOCK, ROWLOCK, READCOMMITTEDLOCK, READPAST)';

        $this->assertSameSql($expected, $builder->lockForUpdate()->skipLocked()->getCompiledSelect());
    }

    public function testSharedLockWithSQLSRVNowait(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users" WITH (HOLDLOCK, ROWLOCK, NOWAIT)';

        $this->assertSameSql($expected, $builder->sharedLock()->nowait()->getCompiledSelect());
    }

    public function testSharedLockWithSQLSRVSkipLockedThrowsException(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSRV does not support sharedLock() with skipLocked().');

        $builder->sharedLock()->skipLocked()->getCompiledSelect();
    }

    public function testLockForUpdateWithSQLSRVAlias(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users u', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users" "u" WITH (UPDLOCK, ROWLOCK)';

        $this->assertSameSql($expected, $builder->lockForUpdate()->getCompiledSelect());
    }

    public function testSharedLockWithSQLSRVAlias(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users u', $this->db);

        $expected = 'SELECT * FROM "test"."dbo"."users" "u" WITH (HOLDLOCK, ROWLOCK)';

        $this->assertSameSql($expected, $builder->sharedLock()->getCompiledSelect());
    }

    public function testLockForUpdateWithSQLSRVLimit(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('users', $this->db);

        $builder->where('id', 1)->orderBy('id', 'ASC')->limit(1)->lockForUpdate();

        $expected = 'SELECT * FROM "test"."dbo"."users" WITH (UPDLOCK, ROWLOCK) WHERE "id" = 1 ORDER BY "id" ASC  OFFSET 0  ROWS FETCH NEXT 1 ROWS ONLY';

        $this->assertSame($expected, trim(str_replace("\n", ' ', $builder->getCompiledSelect())));
    }

    public function testLockForUpdateWithSQLSRVJoin(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);

        $builder->join('users u', 'u.id = jobs.id', 'LEFT')->lockForUpdate();

        $expected = 'SELECT * FROM "test"."dbo"."jobs" WITH (UPDLOCK, ROWLOCK) LEFT JOIN "test"."dbo"."users" "u" ON "u"."id" = "jobs"."id"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSharedLockWithSQLSRVJoin(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder('jobs', $this->db);

        $builder->join('users u', 'u.id = jobs.id', 'LEFT')->sharedLock();

        $expected = 'SELECT * FROM "test"."dbo"."jobs" WITH (HOLDLOCK, ROWLOCK) LEFT JOIN "test"."dbo"."users" "u" ON "u"."id" = "jobs"."id"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testLockForUpdateThrowsExceptionOnSQLSRVWithoutFromTable(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = (new SQLSRVBuilder('users', $this->db))
            ->from([], true)
            ->select('1', false);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSRV does not support lockForUpdate() without a FROM table.');

        $builder->lockForUpdate()->getCompiledSelect();
    }

    public function testSharedLockThrowsExceptionOnSQLSRVWithoutFromTable(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = (new SQLSRVBuilder('users', $this->db))
            ->from([], true)
            ->select('1', false);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSRV does not support sharedLock() without a FROM table.');

        $builder->sharedLock()->getCompiledSelect();
    }

    public function testLockForUpdateThrowsExceptionOnSQLSRVSubquery(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $subquery = new SQLSRVBuilder('users', $this->db);
        $builder  = new SQLSRVBuilder('jobs', $this->db);

        $builder->fromSubquery($subquery, 'users_1');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSRV does not support lockForUpdate() on subqueries.');

        $builder->lockForUpdate()->getCompiledSelect();
    }

    public function testSharedLockThrowsExceptionOnSQLSRVSubquery(): void
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $subquery = new SQLSRVBuilder('users', $this->db);
        $builder  = new SQLSRVBuilder('jobs', $this->db);

        $builder->fromSubquery($subquery, 'users_1');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSRV does not support sharedLock() on subqueries.');

        $builder->sharedLock()->getCompiledSelect();
    }

    public function testSelectSubquery(): void
    {
        $builder  = new BaseBuilder('users', $this->db);
        $subquery = new BaseBuilder('countries', $this->db);

        $subquery->select('name')->where('id', 1);
        $builder->select('name')->selectSubquery($subquery, 'country');

        $expected = 'SELECT "name", (SELECT "name" FROM "countries" WHERE "id" = 1) "country" FROM "users"';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }

    public function testSelectResetQuery(): void
    {
        $builder = new BaseBuilder('users', $this->db);
        $builder->select('name, role');

        $builder->resetQuery();

        $sql = $builder->getCompiledSelect();
        $this->assertSameSql('SELECT * FROM "users"', $sql);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/9696
     */
    public function testGetCompiledSelect(): void
    {
        $builder = new BaseBuilder('users', $this->db);

        $builder->select('name, role')->orderBy('name', 'desc');

        $expected = 'SELECT "name", "role" FROM "users" ORDER BY "name" DESC';

        $this->assertSameSql($expected, $builder->getCompiledSelect(false));

        $builder->orderBy('role', 'desc');

        $expected = 'SELECT "name", "role" FROM "users" ORDER BY "name" DESC, "role" DESC';

        $this->assertSameSql($expected, $builder->getCompiledSelect());
    }
}
