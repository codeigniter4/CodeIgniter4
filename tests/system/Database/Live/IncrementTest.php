<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class IncrementTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testIncrement()
	{
		$this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

		$this->db->table('job')
				->where('name', 'incremental')
				->increment('description');

		$this->seeInDatabase('job', ['name' => 'incremental', 'description' => '7']);
	}

	//--------------------------------------------------------------------

	public function testIncrementWithValue()
	{
		$this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

		$this->db->table('job')
				 ->where('name', 'incremental')
				 ->increment('description', 2);

		$this->seeInDatabase('job', ['name' => 'incremental', 'description' => '8']);
	}

	//--------------------------------------------------------------------

	public function testDecrement()
	{
		$this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

		$this->db->table('job')
				 ->where('name', 'incremental')
				 ->decrement('description');

		$this->seeInDatabase('job', ['name' => 'incremental', 'description' => '5']);
	}

	//--------------------------------------------------------------------

	public function testDecrementWithValue()
	{
		$this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

		$this->db->table('job')
				 ->where('name', 'incremental')
				 ->decrement('description', 2);

		$this->seeInDatabase('job', ['name' => 'incremental', 'description' => '4']);
	}

	//--------------------------------------------------------------------
}
