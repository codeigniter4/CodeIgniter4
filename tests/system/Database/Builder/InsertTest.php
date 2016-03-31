<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseQuery;
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

	public function testThrowsExceptionOnNoValuesSet()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$this->setExpectedException('CodeIgniter\DatabaseException', 'You must use the "set" method to update an entry.');

		$builder->insert(null, true, true);
	}

	//--------------------------------------------------------------------
	
	public function testInsertBatch()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$insertData = array(
			['id' => 2, 'name' => 'Commedian', 'description' => 'Theres something in your teeth'],
			['id' => 3, 'name' => 'Cab Driver', 'description' => 'Iam yellow'],
		);

		$this->db->shouldReturn('query', 1)
				 ->shouldReturn('affectedRows', 1);

		$builder->insertBatch($insertData, true, true);

		$queries = $this->db->getQueries();

		$q1 = $queries[0];
		$q2 = $queries[1];

		$this->assertTrue($q1 instanceof BaseQuery);
		$this->assertTrue($q2 instanceof BaseQuery);

		$raw1 = "INSERT INTO \"jobs\" (\"description\", \"id\", \"name\") VALUES (:description,:id,:name)";
		$raw2 = "INSERT INTO \"jobs\" (\"description\", \"id\", \"name\") VALUES (:description0,:id0,:name0)";

		$this->assertEquals($raw1, str_replace("\n", ' ', $q1->getOriginalQuery() ));
		$this->assertEquals($raw2, str_replace("\n", ' ', $q2->getOriginalQuery() ));

		$expected1   = "INSERT INTO \"jobs\" (\"description\", \"id\", \"name\") VALUES ('Theres something in your teeth',2,'Commedian')";
		$expected2   = "INSERT INTO \"jobs\" (\"description\", \"id\", \"name\") VALUES ('Iam yellow',3,'Cab Driver')";

		$this->assertEquals($expected1, str_replace("\n", ' ', $q1->getQuery() ));
		$this->assertEquals($expected2, str_replace("\n", ' ', $q2->getQuery() ));
	}

	//--------------------------------------------------------------------
}
