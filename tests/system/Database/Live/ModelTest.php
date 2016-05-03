<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Model;

class DeleteTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function setUp()
	{
	    parent::setUp();

		$this->model = new Model($this->db);
	}

	//--------------------------------------------------------------------

	public function testHashIDsWithNumber()
	{
	    $expected = '123';

		$str = $this->model->encodeID($expected);

		$this->assertNotEquals($expected, $str);

		$this->assertEquals($expected, $this->model->decodeID($str));
	}

	//--------------------------------------------------------------------

	public function testHashIDsWithString()
	{
		$expected = 'my test hash';

		$str = $this->model->encodeID($expected);

		$this->assertNotEquals($expected, $str);

		$this->assertEquals($expected, $this->model->decodeID($str));
	}

	//--------------------------------------------------------------------

	public function testHashedIdsWithFind()
	{
	    $this->hasInDatabase('job', ['name' => 'Hasher', 'description' => 'One who hashes']);

		$hash = $this->model->encodeId(5);

		$this->model->setTable('job')
					->withDeleted();

		$user = $this->model->asObject()
							->findByHashedID($hash);

		$this->assertNotEmpty($user);
		$this->assertEquals(5, $user->id);
	}

	//--------------------------------------------------------------------


}