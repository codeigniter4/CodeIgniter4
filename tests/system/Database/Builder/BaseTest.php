<?php namespace Builder;

use CodeIgniter\Database\Query;
use CodeIgniter\Test\Mock\MockConnection;

class BaseTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testDbReturnsConnection()
	{
		$builder = $this->db->table('jobs');

		$result = $builder->db();

		$this->assertInstanceOf(MockConnection::class, $result);
	}

	//--------------------------------------------------------------------

	public function testGetTableReturnsTable()
	{
		$builder = $this->db->table('jobs');

		$result = $builder->getTable();
		$this->assertEquals('jobs', $result);
	}

	public function testGetTableIgnoresFrom()
	{
		$builder = $this->db->table('jobs');

		$builder->from('foo');
		$result = $builder->getTable();
		$this->assertEquals('jobs', $result);
	}
}
