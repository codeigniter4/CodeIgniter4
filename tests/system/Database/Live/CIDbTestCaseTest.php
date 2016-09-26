<?php namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 */
class CIDbTestCaseTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function testHasInDatabase()
	{
		$this->hasInDatabase('user', ['name' => 'Ricky', 'email' => 'sofine@example.com', 'country' => 'US']);

		$this->seeInDatabase('user', ['name' => 'Ricky', 'email' => 'sofine@example.com', 'country' => 'US']);
	}
	
	//--------------------------------------------------------------------

	public function testDontSeeInDatabase()
	{
		$this->dontSeeInDatabase('user', ['name' => 'Ricardo']);
	}

	//--------------------------------------------------------------------

	public function testSeeNumRecords()
	{
		$this->seeNumRecords(2, 'user', ['country' => 'US']);
	}

	//--------------------------------------------------------------------


	public function testGrabFromDatabase()
	{
	    $email = $this->grabFromDatabase('user', 'email', ['name' => 'Derek Jones']);

		$this->assertEquals('derek@world.com', $email);
	}

	//--------------------------------------------------------------------


	
}