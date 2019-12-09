<?php namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DataException;
use Tests\Support\Database\MockConnection;

class SelectTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testSimpleSelect()
	{
		$builder = new BaseBuilder('users', $this->db);

		$expected = 'SELECT * FROM "users"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectOnlyOneColumn()
	{
		$builder = new BaseBuilder('users', $this->db);

		$builder->select('name');

		$expected = 'SELECT "name" FROM "users"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectAcceptsArray()
	{
		$builder = new BaseBuilder('users', $this->db);

		$builder->select(['name', 'role']);

		$expected = 'SELECT "name", "role" FROM "users"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectAcceptsMultipleColumns()
	{
		$builder = new BaseBuilder('users', $this->db);

		$builder->select('name, role');

		$expected = 'SELECT "name", "role" FROM "users"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectKeepsAliases()
	{
		$builder = new BaseBuilder('users', $this->db);

		$builder->select('name, role as myRole');

		$expected = 'SELECT "name", "role" as "myRole" FROM "users"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectWorksWithComplexSelects()
	{
		$builder = new BaseBuilder('users', $this->db);

		$builder->select('(SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid');

		$expected = 'SELECT (SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id=4) AS amount_paid FROM "users"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectMinWithNoAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectMin('payments');

		$expected = 'SELECT MIN("payments") AS "payments" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectMinWithAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectMin('payments', 'myAlias');

		$expected = 'SELECT MIN("payments") AS "myAlias" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectMaxWithNoAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectMax('payments');

		$expected = 'SELECT MAX("payments") AS "payments" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectMaxWithAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectMax('payments', 'myAlias');

		$expected = 'SELECT MAX("payments") AS "myAlias" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectAvgWithNoAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectAvg('payments');

		$expected = 'SELECT AVG("payments") AS "payments" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectAvgWithAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectAvg('payments', 'myAlias');

		$expected = 'SELECT AVG("payments") AS "myAlias" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectSumWithNoAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectSum('payments');

		$expected = 'SELECT SUM("payments") AS "payments" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectSumWithAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectSum('payments', 'myAlias');

		$expected = 'SELECT SUM("payments") AS "myAlias" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectCountWithNoAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectCount('payments');

		$expected = 'SELECT COUNT("payments") AS "payments" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectCountWithAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectCount('payments', 'myAlias');

		$expected = 'SELECT COUNT("payments") AS "myAlias" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectMinThrowsExceptionOnEmptyValue()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$this->expectException(DataException::class);
		$this->expectExceptionMessage('Empty statement is given for the field `Select`');

		$builder->selectSum('');
	}

	//--------------------------------------------------------------------

	public function testSelectMaxWithDotNameAndNoAlias()
	{
		$builder = new BaseBuilder('invoices', $this->db);

		$builder->selectMax('db.payments');

		$expected = 'SELECT MAX("db"."payments") AS "payments" FROM "invoices"';

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testSelectMinThrowsExceptionOnMultipleColumn()
	{
		$builder = new BaseBuilder('users', $this->db);

		$this->expectException(DataException::class);
		$this->expectExceptionMessage('You must provide a valid column name not separated by comma.');

		$builder->selectSum('name,role');
	}

	//--------------------------------------------------------------------

}
