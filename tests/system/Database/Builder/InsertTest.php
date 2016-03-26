<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class InsertTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testSimpleInsert()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$insertData = [
			'id' => 1,
		    'name' => 'Grocery Sales'
		];
		$builder->insert($insertData, true, true);

		$expectedSQL   = "INSERT INTO \"jobs\" (\"id\", \"name\") VALUES (:id, :name)";
		$expectedBinds = $insertData;

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledInsert()));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	// @todo Come back and revisit insert Batch later.
//	public function testInsertBatch()
//	{
//		$builder = new BaseBuilder('jobs', $this->db);
//
//		$insertData = array(
//			['id' => 2, 'name' => 'Commedian', 'description' => 'Theres something in your teeth'],
//			['id' => 3, 'name' => 'Cab Driver', 'description' => 'Iam yellow'],
//		);
//		$builder->insertBatch($insertData, true, true);
//
//		$expectedSQL   = "INSERT INTO \"jobs\" (\"id\", \"name\") VALUES (:id, :name)";
//		$expectedBinds = $insertData;
//
//		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledInsert()));
//		$this->assertEquals($expectedBinds, $builder->getBinds());
//	}

	//--------------------------------------------------------------------
}
