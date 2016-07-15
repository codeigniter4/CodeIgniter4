<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\DatabaseException;

/**
 * @group DatabaseLive
 */
class UpdateTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function testUpdateSetsAllWithoutWhere()
	{
	    $this->db->table('user')
		            ->update(['name' => 'Bobby']);

		$result = $this->db->table('user')->get()->getResult();

		$this->assertEquals('Bobby', $result[0]->name);
		$this->assertEquals('Bobby', $result[1]->name);
		$this->assertEquals('Bobby', $result[2]->name);
		$this->assertEquals('Bobby', $result[3]->name);
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
			if ($row['name'] == 'Bobby')
			{
				$rows[] = $row;
			}
		}

		$this->assertEquals(2, count($rows));
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
			return;
		}
	}

	//--------------------------------------------------------------------

	public function testUpdateBatch()
	{
	    $data = [
		    [
			    'name' => 'Derek Jones',
		        'country' => 'Greece'
		    ],
		    [
			    'name' => 'Ahmadinejad',
			    'country' => 'Greece'
		    ],
	    ];

		$this->db->table('user')
					->updateBatch($data, 'name');

		$this->seeInDatabase('user', [
			'name' => 'Derek Jones',
			'country' => 'Greece'
		]);
		$this->seeInDatabase('user', [
			'name' => 'Ahmadinejad',
			'country' => 'Greece'
		]);
	}

	//--------------------------------------------------------------------


}