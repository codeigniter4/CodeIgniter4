<?php namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class UpdateTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------
	
	public function testUpdate() 
	{
	    $builder = new BaseBuilder('jobs', $this->db);
		
		$builder->where('id', 1)->update(['name' => 'Programmer'], null, null, true);

		$expectedSQL = "UPDATE \"jobs\" SET \"name\" = :name WHERE \"id\" = :id";
		$expectedBinds = ['id' => 1, 'name' => 'Programmer'];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}
	
	//--------------------------------------------------------------------

	public function testUpdateInternalWhereAndLimit()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$builder->update(['name' => 'Programmer'], ['id' => 1], 5, true);

		$expectedSQL = "UPDATE \"jobs\" SET \"name\" = :name WHERE \"id\" = :id LIMIT 5";
		$expectedBinds = ['id' => 1, 'name' => 'Programmer'];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testUpdateWithSet()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$builder->set('name', 'Programmer')->where('id', 1)->update(null, null, null, true);

		$expectedSQL = "UPDATE \"jobs\" SET \"name\" = :name WHERE \"id\" = :id";
		$expectedBinds = ['id' => 1, 'name' => 'Programmer'];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------
}
