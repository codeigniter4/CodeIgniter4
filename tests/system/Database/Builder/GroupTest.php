<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class GroupTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
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

	public function testHavingIn()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingIn('id', [1, 2]);

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (1,2)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingInClosure()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name');

		$builder->havingIn('id', function (BaseBuilder $builder) {
			return $builder->select('user_id')->from('users_jobs')->where('group_id', 3);
		});

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingIn()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingIn('id', [1, 2])
				->orHavingIn('group_id', [5, 6]);

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (1,2) OR "group_id" IN (5,6)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingInClosure()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name');

		$builder->havingIn('id', function (BaseBuilder $builder) {
			return $builder->select('user_id')->from('users_jobs')->where('group_id', 3);
		});
		$builder->orHavingIn('group_id', function (BaseBuilder $builder) {
			return $builder->select('group_id')->from('groups')->where('group_id', 6);
		});

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3) OR "group_id" IN (SELECT "group_id" FROM "groups" WHERE "group_id" = 6)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingNotIn()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingNotIn('id', [1, 2]);

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (1,2)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingNotInClosure()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name');

		$builder->havingNotIn('id', function (BaseBuilder $builder) {
			return $builder->select('user_id')->from('users_jobs')->where('group_id', 3);
		});

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingNotIn()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingNotIn('id', [1, 2])
				->orHavingNotIn('group_id', [5, 6]);

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (1,2) OR "group_id" NOT IN (5,6)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingNotInClosure()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name');

		$builder->havingNotIn('id', function (BaseBuilder $builder) {
			return $builder->select('user_id')->from('users_jobs')->where('group_id', 3);
		});
		$builder->orHavingNotIn('group_id', function (BaseBuilder $builder) {
			return $builder->select('group_id')->from('groups')->where('group_id', 6);
		});

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "id" NOT IN (SELECT "user_id" FROM "users_jobs" WHERE "group_id" = 3) OR "group_id" NOT IN (SELECT "group_id" FROM "groups" WHERE "group_id" = 6)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingLike()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingLikeBefore()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a', 'before');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingLikeAfter()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a', 'after');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'a%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testNotHavingLike()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->notHavingLike('pet_name', 'a');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" NOT LIKE \'%a%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testNotHavingLikeBefore()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->notHavingLike('pet_name', 'a', 'before');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" NOT LIKE \'%a\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testNotHavingLikeAfter()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->notHavingLike('pet_name', 'a', 'after');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" NOT LIKE \'a%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingLike()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a')
				->orHavingLike('pet_color', 'b');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a%\' ESCAPE \'!\' OR  "pet_color" LIKE \'%b%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingLikeBefore()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a', 'before')
				->orHavingLike('pet_color', 'b', 'before');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a\' ESCAPE \'!\' OR  "pet_color" LIKE \'%b\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrHavingLikeAfter()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a', 'after')
				->orHavingLike('pet_color', 'b', 'after');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'a%\' ESCAPE \'!\' OR  "pet_color" LIKE \'b%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrNotHavingLike()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a')
				->orNotHavingLike('pet_color', 'b');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a%\' ESCAPE \'!\' OR  "pet_color" NOT LIKE \'%b%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrNotHavingLikeBefore()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a', 'before')
				->orNotHavingLike('pet_color', 'b', 'before');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'%a\' ESCAPE \'!\' OR  "pet_color" NOT LIKE \'%b\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrNotHavingLikeAfter()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->havingLike('pet_name', 'a', 'after')
				->orNotHavingLike('pet_color', 'b', 'after');

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING "pet_name" LIKE \'a%\' ESCAPE \'!\' OR  "pet_color" NOT LIKE \'b%\' ESCAPE \'!\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingAndGroup()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->having('SUM(id) <', 3)
				->havingGroupStart()
					->having('SUM(id)', 2)
					->having('name', 'adam')
				->havingGroupEnd();

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) < 3 AND   ( SUM(id) = 2 AND "name" = \'adam\'  )';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingOrGroup()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->having('SUM(id) >', 3)
				->orHavingGroupStart()
					->having('SUM(id)', 2)
					->having('name', 'adam')
				->havingGroupEnd();

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) > 3 OR   ( SUM(id) = 2 AND "name" = \'adam\'  )';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testNotHavingAndGroup()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->having('SUM(id) <', 3)
				->notHavingGroupStart()
					->having('SUM(id)', 2)
					->having('name', 'adam')
				->havingGroupEnd();

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) < 3 AND NOT   ( SUM(id) = 2 AND "name" = \'adam\'  )';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testNotHavingOrGroup()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
				->having('SUM(id) <', 3)
				->orNotHavingGroupStart()
					->having('SUM(id)', 2)
					->having('name', 'adam')
				->havingGroupEnd();

		$expectedSQL = 'SELECT "name" FROM "user" GROUP BY "name" HAVING SUM(id) < 3 OR NOT   ( SUM(id) = 2 AND "name" = \'adam\'  )';

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
