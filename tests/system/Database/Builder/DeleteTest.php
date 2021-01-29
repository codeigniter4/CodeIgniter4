<?php namespace Builder;

use CodeIgniter\Test\Mock\MockConnection;

class DeleteTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testDelete()
	{
		$builder = $this->db->table('jobs');

		$answer = $builder->testMode()->delete(['id' => 1], null, true);

		$expectedSQL   = 'DELETE FROM "jobs" WHERE "id" = :id:';
		$expectedBinds = [
			'id' => [
				1,
				true,
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	public function testGetCompiledDelete()
	{
		$builder = $this->db->table('jobs');

		$builder->where('id', 1);
		$sql = $builder->getCompiledDelete();

		$expectedSQL = 'DELETE FROM "jobs"'
			. "\n" . 'WHERE "id" = 1';
		$this->assertEquals($expectedSQL, $sql);
	}

	public function testGetCompiledDeleteWithLimit()
	{
		$builder = $this->db->table('jobs');

		$builder->where('id', 1);
		$builder->limit(10);
		$sql = $builder->getCompiledDelete();

		$expectedSQL = 'DELETE FROM "jobs"'
			. "\n" . 'WHERE "id" = 1 LIMIT 10';
		$this->assertEquals($expectedSQL, $sql);
	}
}
