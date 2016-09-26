<?php namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;
use CodeIgniter\Database\MockQuery;

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

		$expectedSQL = "UPDATE \"jobs\" SET \"name\" = :name WHERE \"id\" = :id  LIMIT 5";
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

	public function testUpdateThrowsExceptionWithNoData()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$this->setExpectedException('CodeIgniter\DatabaseException', 'You must use the "set" method to update an entry.');

		$builder->update(null, null, null, true);
	}

	//--------------------------------------------------------------------

	public function testUpdateBatch()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$updateData = array(
			['id' => 2, 'name' => 'Comedian', 'description' => 'Theres something in your teeth'],
			['id' => 3, 'name' => 'Cab Driver', 'description' => 'Iam yellow'],
		);

		$this->db->shouldReturn('execute', 1)
		         ->shouldReturn('affectedRows', 1);

		$builder->updateBatch($updateData, 'id');

		$query = $this->db->getQueries();

		$this->assertTrue(is_array($query));

		$query = $query[0];

		$this->assertTrue($query instanceof MockQuery);

		$expected = 'UPDATE "jobs" SET "name" = CASE 
WHEN "id" = :id THEN :name
WHEN "id" = :id0 THEN :name0
ELSE "name" END, "description" = CASE 
WHEN "id" = :id THEN :description
WHEN "id" = :id0 THEN :description0
ELSE "description" END
WHERE "id" IN(:id,:id0)';

		$this->assertEquals($expected, $query->getOriginalQuery() );

		$expected = 'UPDATE "jobs" SET "name" = CASE 
WHEN "id" = 2 THEN \'Comedian\'
WHEN "id" = 3 THEN \'Cab Driver\'
ELSE "name" END, "description" = CASE 
WHEN "id" = 2 THEN \'Theres something in your teeth\'
WHEN "id" = 3 THEN \'Iam yellow\'
ELSE "description" END
WHERE "id" IN(2,3)';

		$this->assertEquals($expected, $query->getQuery() );
	}

	//--------------------------------------------------------------------

	public function testUpdateBatchThrowsExceptionWithNoData()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$this->setExpectedException('CodeIgniter\DatabaseException', 'You must use the "set" method to update an entry.');

		$builder->updateBatch(null, 'id');
	}

	//--------------------------------------------------------------------

	public function testUpdateBatchThrowsExceptionWithNoID()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$this->setExpectedException('CodeIgniter\DatabaseException', 'You must specify an index to match on for batch updates.');

		$builder->updateBatch([]);
	}

	//--------------------------------------------------------------------

	public function testUpdateBatchThrowsExceptionWithEmptySetArray()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$this->setExpectedException('CodeIgniter\DatabaseException', 'updateBatch() called with no data');

		$builder->updateBatch([], 'id');
	}

	//--------------------------------------------------------------------

}
