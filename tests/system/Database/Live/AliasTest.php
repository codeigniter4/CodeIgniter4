<?php namespace CodeIgniter\Database\Live;


use CodeIgniter\Database\Config;

class AliasTest extends \CIUnitTestCase
{
	protected $refresh = true;

	protected $db;

	public function setUp()
	{
		parent::setUp();

		$this->db = Config::connect();
	}


	public function testAlias()
	{
		$builder = $this->db->table('job j');

		$jobs = $builder
			->where('j.name', 'Developer')
			->get();

		$this->assertEquals(1, count($jobs->getResult()));
	}

	//--------------------------------------------------------------------

}
