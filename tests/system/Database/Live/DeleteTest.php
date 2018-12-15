<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class DeleteTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testDeleteThrowExceptionWithNoCriteria()
	{
		$this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');

		$this->db->table('job')->delete();
	}

	//--------------------------------------------------------------------

	public function testDeleteWithExternalWhere()
	{
		$this->seeInDatabase('job', ['name' => 'Developer']);

		$this->db->table('job')->where('name', 'Developer')->delete();

		$this->dontSeeInDatabase('job', ['name' => 'Developer']);
	}

	//--------------------------------------------------------------------

	public function testDeleteWithInternalWhere()
	{
		$this->seeInDatabase('job', ['name' => 'Developer']);

		$this->db->table('job')->delete(['name' => 'Developer']);

		$this->dontSeeInDatabase('job', ['name' => 'Developer']);
	}

	//--------------------------------------------------------------------

	/**
	 * @group  single
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function testDeleteWithLimit()
	{
		$this->seeNumRecords(2, 'user', ['country' => 'US']);

		try
		{
			$this->db->table('user')
					 ->delete(['country' => 'US'], 1);
		}
		catch (DatabaseException $e)
		{
			if (strpos($e->getMessage(), 'does not allow LIMITs on DELETE queries.') !== false)
			{
				return;
			}
		}

		$this->seeNumRecords(1, 'user', ['country' => 'US']);
	}

	//--------------------------------------------------------------------

}
