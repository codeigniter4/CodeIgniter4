<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 */
class UpdateTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testUpdateSetsAllWithoutWhere()
	{
		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Updates are not allowed unless they contain a "where" or "like" clause.');

		$this->db->table('user')
					->update(['name' => 'Bobby']);
	}

	//--------------------------------------------------------------------

	public function testUpdateSetsAllWithoutWhereAndLimit()
	{
		try
		{
			$this->db->table('user')
					 ->update(['name' => 'Bobby'], null, 1);

			$result = $this->db->table('user')
							   ->orderBy('id', 'asc')
							   ->get()
							   ->getResult();

			$this->assertEquals('Bobby', $result[0]->name);
			$this->assertEquals('Ahmadinejad', $result[1]->name);
			$this->assertEquals('Richard A Causey', $result[2]->name);
			$this->assertEquals('Chris Martin', $result[3]->name);
		}
		catch (DatabaseException $e)
		{
			// This DB doesn't support Where and Limit together
			// but we don't want it called a "Risky" test.
			$this->assertTrue(true);
			return;
		}
	}

	//--------------------------------------------------------------------

	public function testUpdateWithWhere()
	{
		$this->db->table('user')
				 ->update(['name' => 'Bobby'], ['country' => 'US']);

		$result = $this->db->table('user')->get()->getResultArray();

		$rows = [];

		foreach ($result as $row)
		{
			if ($row['name'] === 'Bobby')
			{
				$rows[] = $row;
			}
		}

		$this->assertCount(2, $rows);
	}

	//--------------------------------------------------------------------

	public function testUpdateWithWhereAndLimit()
	{
		try
		{
			$this->db->table('user')
					 ->update(['name' => 'Bobby'], ['country' => 'US'], 1);

			$result = $this->db->table('user')
							   ->get()
							   ->getResult();

			$this->assertEquals('Bobby', $result[0]->name);
			$this->assertEquals('Ahmadinejad', $result[1]->name);
			$this->assertEquals('Richard A Causey', $result[2]->name);
			$this->assertEquals('Chris Martin', $result[3]->name);
		}
		catch (DatabaseException $e)
		{
			// This DB doesn't support Where and Limit together
			// but we don't want it called a "Risky" test.
			$this->assertTrue(true);
			return;
		}
	}

	//--------------------------------------------------------------------

	public function testUpdateBatch()
	{
		$data = [
			[
				'name'    => 'Derek Jones',
				'country' => 'Greece',
			],
			[
				'name'    => 'Ahmadinejad',
				'country' => 'Greece',
			],
		];

		$this->db->table('user')
					->updateBatch($data, 'name');

		$this->seeInDatabase('user', [
			'name'    => 'Derek Jones',
			'country' => 'Greece',
		]);
		$this->seeInDatabase('user', [
			'name'    => 'Ahmadinejad',
			'country' => 'Greece',
		]);
	}

	//--------------------------------------------------------------------

	public function testUpdateWithWhereSameColumn()
	{
		$this->db->table('user')
				 ->update(['country' => 'CA'], ['country' => 'US']);

		$result = $this->db->table('user')->get()->getResultArray();

		$rows = [];

		foreach ($result as $row)
		{
			if ($row['country'] === 'CA')
			{
				$rows[] = $row;
			}
		}

		$this->assertCount(2, $rows);
	}

	//--------------------------------------------------------------------

	public function testUpdateWithWhereSameColumn2()
	{
		// calling order: set() -> where()
		$this->db->table('user')
				 ->set('country', 'CA')
				 ->where('country', 'US')
				 ->update();

		$result = $this->db->table('user')->get()->getResultArray();

		$rows = [];

		foreach ($result as $row)
		{
			if ($row['country'] === 'CA')
			{
				$rows[] = $row;
			}
		}

		$this->assertCount(2, $rows);
	}

	//--------------------------------------------------------------------

	public function testUpdateWithWhereSameColumn3()
	{
		// calling order: where() -> set() in update()
		$this->db->table('user')
				 ->where('country', 'US')
				 ->update(['country' => 'CA']);

		$result = $this->db->table('user')->get()->getResultArray();

		$rows = [];

		foreach ($result as $row)
		{
			if ($row['country'] === 'CA')
			{
				$rows[] = $row;
			}
		}

		$this->assertCount(2, $rows);
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 * @see   https://github.com/codeigniter4/CodeIgniter4/issues/324
	 */
	public function testUpdatePeriods()
	{
		$this->db->table('misc')
			->where('key', 'spaces and tabs')
			->update([
				'value' => '30.192',
			]);

		$this->seeInDatabase('misc', [
			'value' => '30.192',
		]);
	}

	//--------------------------------------------------------------------

	// @see https://codeigniter4.github.io/CodeIgniter4/database/query_builder.html#updating-data
	public function testSetWithoutEscapeWillThrowException()
	{
		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Updates are not allowed unless they contain a "where" or "like" clause.');
		$this->db->table('job')
				 ->set('description', 'name', false)
				 ->update();
	}

}
