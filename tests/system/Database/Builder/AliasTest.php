<?php namespace Builder;

use Tests\Support\Database\MockConnection;

class AliasTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testAlias()
	{
		$builder = $this->db->table('jobs j');

		$expectedSQL = 'SELECT * FROM "jobs" "j"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testAliasSupportsArrayOfNames()
	{
		$builder = $this->db->table(['jobs j', 'users u']);

		$expectedSQL = 'SELECT * FROM "jobs" "j", "users" "u"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testAliasSupportsStringOfNames()
	{
		$builder = $this->db->table('jobs j, users u');

		$expectedSQL = 'SELECT * FROM "jobs" "j", "users" "u"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
