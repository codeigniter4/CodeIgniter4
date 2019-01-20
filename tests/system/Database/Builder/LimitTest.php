<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class LimitTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testLimitAlone()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->limit(5);

		$expectedSQL = 'SELECT * FROM "user"  LIMIT 5';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testLimitAndOffset()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->limit(5, 1);

		$expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testLimitAndOffsetMethod()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->limit(5)->offset(1);

		$expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
