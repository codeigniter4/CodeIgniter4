<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\DatabaseException;

/**
 * @group DatabaseLive
 */
class DeleteTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function testDeleteThrowExceptionWithNoCriteria()
	{
	    $this->setExpectedException('CodeIgniter\DatabaseException');

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
	 * @group single
	 * @throws \CodeIgniter\DatabaseException
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