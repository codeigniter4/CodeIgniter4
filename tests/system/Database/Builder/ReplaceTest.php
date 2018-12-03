<?php namespace Builder;

use Tests\Support\Database\MockConnection;

class ReplaceTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testSimpleReplace()
	{
		$builder = $this->db->table('jobs');

		$expected = 'REPLACE INTO "jobs" ("title", "name", "date") VALUES (:title:, :name:, :date:)';

		$data = [
			'title' => 'My title',
			'name'  => 'My Name',
			'date'  => 'My date',
		];

		$this->assertSame($expected, $builder->replace($data, true));
	}

	//--------------------------------------------------------------------

	public function testReplaceThrowsExceptionWithNoData()
	{
		$builder = $this->db->table('jobs');

		$this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
		$this->expectExceptionMessage('You must use the "set" method to update an entry.');

		$builder->replace();
	}

	//--------------------------------------------------------------------

}
