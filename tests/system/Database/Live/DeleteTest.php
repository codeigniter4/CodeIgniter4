<?php namespace CodeIgniter\Database\Live;

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

	public function testDeleteWithLimit()
	{
		$this->seeNumRecords(2, 'user', ['country' => 'US']);

		$this->db->table('user')->delete(['country' => 'US'], 1);

		$this->seeNumRecords(1, 'user', ['country' => 'US']);
	}

	//--------------------------------------------------------------------

	public function testCanReuseDeleteCriteria()
	{
	    $this->seeNumRecords(2, 'user', ['country' => 'US']);

		$builder = $this->db->table('user');

		$builder->delete(['country' => 'US'], 1, false);
		$this->seeNumRecords(1, 'user', ['country' => 'US']);

		$this->assertEquals(1, $builder->countAllResults());
	}

	//--------------------------------------------------------------------


}