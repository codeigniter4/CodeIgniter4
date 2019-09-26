<?php namespace Builder;

use CodeIgniter\Database\Query;
use Tests\Support\Database\MockConnection;

class InsertTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testSimpleInsert()
	{
		$builder = $this->db->table('jobs');

		$insertData = [
			'id'   => 1,
			'name' => 'Grocery Sales',
		];
		$builder->testMode()->insert($insertData, true);

		$expectedSQL   = 'INSERT INTO "jobs" ("id", "name") VALUES (1, \'Grocery Sales\')';
		$expectedBinds = [
			'id'   => [
				1,
				true,
			],
			'name' => [
				'Grocery Sales',
				true,
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledInsert()));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testThrowsExceptionOnNoValuesSet()
	{
		$builder = $this->db->table('jobs');

		$this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
		$this->expectExceptionMessage('You must use the "set" method to update an entry.');

		$builder->testMode()->insert(null, true);
	}

	//--------------------------------------------------------------------

	public function testInsertBatch()
	{
		$builder = $this->db->table('jobs');

		$insertData = [
			[
				'id'          => 2,
				'name'        => 'Commedian',
				'description' => 'Theres something in your teeth',
			],
			[
				'id'          => 3,
				'name'        => 'Cab Driver',
				'description' => 'Iam yellow',
			],
		];

		$this->db->shouldReturn('execute', 1)
				 ->shouldReturn('affectedRows', 1);

		$builder->insertBatch($insertData, true, true);

		$query = $this->db->getLastQuery();

		$this->assertInstanceOf(Query::class, $query);

		$raw = 'INSERT INTO "jobs" ("description", "id", "name") VALUES (:description0:,:id0:,:name0:)';

		$this->assertEquals($raw, str_replace("\n", ' ', $query->getOriginalQuery() ));

		$expected = "INSERT INTO \"jobs\" (\"description\", \"id\", \"name\") VALUES ('Iam yellow',3,'Cab Driver')";

		$this->assertEquals($expected, str_replace("\n", ' ', $query->getQuery() ));
	}

	//--------------------------------------------------------------------

	public function testInsertBatchThrowsExceptionOnNoData()
	{
		$builder = $this->db->table('jobs');

		$this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException', 'You must use the "set" method to update an entry.');
		$this->expectExceptionMessage('You must use the "set" method to update an entry.');
		$builder->insertBatch();
	}

	//--------------------------------------------------------------------

	public function testInsertBatchThrowsExceptionOnEmptyData()
	{
		$builder = $this->db->table('jobs');

		$this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
		$this->expectExceptionMessage('insertBatch() called with no data');
		$builder->insertBatch([]);
	}

	//--------------------------------------------------------------------
}
