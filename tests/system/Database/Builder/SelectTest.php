<?php

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
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\SQLSRV\Builder as SQLSRVBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class SelectTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleSelect()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('users');

        $expected = 'SELECT * FROM "users"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectOnlyOneColumn()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('users');

        $builder->select('name');

        $expected = 'SELECT "name" FROM "users"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectAcceptsArray()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('users');

        $builder->select(['name', 'role']);

        $expected = 'SELECT "name", "role" FROM "users"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectAcceptsMultipleColumns()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('users');

        $builder->select('name, role');

        $expected = 'SELECT "name", "role" FROM "users"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectKeepsAliases()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('users');

        $builder->select('name, role as myRole');

        $expected = 'SELECT "name", "role" as "myRole" FROM "users"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectWorksWithComplexSelects()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('users');

        $builder->select('(SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid');

        $expected = 'SELECT (SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid FROM "users"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectMinWithNoAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectMin('payments');

        $expected = 'SELECT MIN("payments") AS "payments" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectMinWithAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectMin('payments', 'myAlias');

        $expected = 'SELECT MIN("payments") AS "myAlias" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectMaxWithNoAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectMax('payments');

        $expected = 'SELECT MAX("payments") AS "payments" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectMaxWithAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectMax('payments', 'myAlias');

        $expected = 'SELECT MAX("payments") AS "myAlias" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectAvgWithNoAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectAvg('payments');

        $expected = 'SELECT AVG("payments") AS "payments" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectAvgWithAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectAvg('payments', 'myAlias');

        $expected = 'SELECT AVG("payments") AS "myAlias" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectSumWithNoAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectSum('payments');

        $expected = 'SELECT SUM("payments") AS "payments" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectSumWithAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectSum('payments', 'myAlias');

        $expected = 'SELECT SUM("payments") AS "myAlias" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectCountWithNoAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectCount('payments');

        $expected = 'SELECT COUNT("payments") AS "payments" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectCountWithAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectCount('payments', 'myAlias');

        $expected = 'SELECT COUNT("payments") AS "myAlias" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectMinThrowsExceptionOnEmptyValue()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Empty statement is given for the field `Select`');

        $builder->selectSum('');
    }

    public function testSelectMaxWithDotNameAndNoAlias()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('invoices');

        $builder->selectMax('db.payments');

        $expected = 'SELECT MAX("db"."payments") AS "payments" FROM "invoices"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testSelectMinThrowsExceptionOnMultipleColumn()
    {
        $builder = new BaseBuilder($this->db);
        $builder->from('users');

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('You must provide a valid column name not separated by comma.');

        $builder->selectSum('name,role');
    }

    public function testSimpleSelectWithSQLSRV()
    {
        $this->db = new MockConnection(['DBDriver' => 'SQLSRV', 'database' => 'test', 'schema' => 'dbo']);

        $builder = new SQLSRVBuilder($this->db);
        $builder->from('users');

        $expected = 'SELECT * FROM "test"."dbo"."users"';

        $this->assertSame($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
