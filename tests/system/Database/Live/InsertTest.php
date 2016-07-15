<?php namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 */
class InsertTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function testInsert()
	{
		$job_data = array('name' => 'Grocery Sales', 'description' => 'Discount!');

		$this->db->table('job')->insert($job_data);

		$this->seeInDatabase('job', ['name' => 'Grocery Sales']);
	}

	//--------------------------------------------------------------------

	public function testInsertBatch()
	{
		$job_data = array(
			array('name' => 'Comedian', 'description' => 'Theres something in your teeth'),
			array('name' => 'Cab Driver', 'description' => 'Iam yellow'),
		);

		$this->db->table('job')->insertBatch($job_data);

		$this->seeInDatabase('job', ['name' => 'Comedian']);
		$this->seeInDatabase('job', ['name' => 'Cab Driver']);
	}

	//--------------------------------------------------------------------

	public function testReplaceWithNoMatchingData()
	{
		$data = array('id' => 5, 'name' => 'Cab Driver', 'description' => 'Iam yellow');

		$this->db->table('job')->replace($data);

		$row = $this->db->table('job')
						->getwhere(['id' => 5])
						->getRow();

		$this->assertEquals('Cab Driver', $row->name);
	}

	//--------------------------------------------------------------------

	public function testReplaceWithMatchingData()
	{
		$data = array('id' => 1, 'name' => 'Cab Driver', 'description' => 'Iam yellow');

		$this->db->table('job')->replace($data);

		$row = $this->db->table('job')
		                ->getwhere(['id' => 1])
		                ->getRow();

		$this->assertEquals('Cab Driver', $row->name);
	}

	//--------------------------------------------------------------------
}