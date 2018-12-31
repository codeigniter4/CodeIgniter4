<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class GroupTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testGroupBy()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingBy()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->having('SUM(id) > 2');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) > 2';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingBy()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->having('id >', 3)
				->orHaving('SUM(id) > 2');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" > 3 OR SUM(id) > 2';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testAndGroups()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->groupStart()
					->where('id >', 3)
					->where('name !=', 'Luke')
				->groupEnd()
				->where('name', 'Darth');

		$expectedSQL = 'SELECT * FROM "user" WHERE   ( "id" > 3 AND "name" != \'Luke\'  ) AND "name" = \'Darth\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrGroups()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->where('name', 'Darth')
				->orGroupStart()
					->where('id >', 3)
					->where('name !=', 'Luke')
				->groupEnd();

		$expectedSQL = 'SELECT * FROM "user" WHERE "name" = \'Darth\' OR   ( "id" > 3 AND "name" != \'Luke\'  )';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testNotGroups()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->where('name', 'Darth')
				->notGroupStart()
				->where('id >', 3)
				->where('name !=', 'Luke')
				->groupEnd();

		$expectedSQL = 'SELECT * FROM "user" WHERE "name" = \'Darth\' AND NOT   ( "id" > 3 AND "name" != \'Luke\'  )';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrNotGroups()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->where('name', 'Darth')
				->orNotGroupStart()
				->where('id >', 3)
				->where('name !=', 'Luke')
				->groupEnd();

		$expectedSQL = 'SELECT * FROM "user" WHERE "name" = \'Darth\' OR NOT   ( "id" > 3 AND "name" != \'Luke\'  )';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
