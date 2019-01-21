<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class InsertTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testInsert()
	{
		$job_data = [
			'name'        => 'Grocery Sales',
			'description' => 'Discount!',
		];

		$this->db->table('job')->insert($job_data);

		$this->seeInDatabase('job', ['name' => 'Grocery Sales']);
	}

	//--------------------------------------------------------------------

	public function testInsertNoEscape()
	{
		$timestamp = time();

		$job_data = [
			'name'        => '1',
			'description' => $this->db->DBDriver === 'SQLite3'
				? "date({$timestamp}, 'unixepoch', 'localtime')"
				: 'DATE()',
		];

		$this->db->table('job')->insert($job_data, false);

		$lastRecord = $this->db->table('job')->orderBy('id', 'desc')->limit(1)->get()->getResultArray()[0];

		$this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($lastRecord['description'])));
	}

	//--------------------------------------------------------------------

	public function testInsertBatch()
	{
		$job_data = [
			[
				'name'        => 'Comedian',
				'description' => 'Theres something in your teeth',
			],
			[
				'name'        => 'Cab Driver',
				'description' => 'Iam yellow',
			],
		];

		$this->db->table('job')->insertBatch($job_data);

		$this->seeInDatabase('job', ['name' => 'Comedian']);
		$this->seeInDatabase('job', ['name' => 'Cab Driver']);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1667
	 */
	public function testInsertBatchNoEscape()
	{
		$timestamp = time();

		$job_data = [
			[
				'name'        => '1',
				'description' => $this->db->DBDriver === 'SQLite3'
					? "date({$timestamp}, 'unixepoch', 'localtime')"
					: 'DATE()',
			],
		];

		$this->db->table('job')->insertBatch($job_data, false);

		$lastRecord = $this->db->table('job')->orderBy('id', 'desc')->limit(1)->get()->getResultArray()[0];

		$this->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($lastRecord['description'])));
	}

	//--------------------------------------------------------------------

	public function testReplaceWithNoMatchingData()
	{
		$data = [
			'id'          => 5,
			'name'        => 'Cab Driver',
			'description' => 'Iam yellow',
		];

		$this->db->table('job')->replace($data);

		$row = $this->db->table('job')
						->getwhere(['id' => 5])
						->getRow();

		$this->assertEquals('Cab Driver', $row->name);
	}

	//--------------------------------------------------------------------

	public function testReplaceWithMatchingData()
	{
		$data = [
			'id'          => 1,
			'name'        => 'Cab Driver',
			'description' => 'Iam yellow',
		];

		$this->db->table('job')->replace($data);

		$row = $this->db->table('job')
						->getwhere(['id' => 1])
						->getRow();

		$this->assertEquals('Cab Driver', $row->name);
	}

	//--------------------------------------------------------------------

	public function testBug302()
	{
		$code = "my code \'CodeIgniter\Autoloader\'";

		$this->db->table('misc')->insert([
			'key'   => 'test',
			'value' => $code,
		]);

		$this->seeInDatabase('misc', ['key' => 'test']);
		$this->seeInDatabase('misc', ['value' => $code]);
	}

	public function testInsertPasswordHash()
	{
		$hash = '$2y$10$tNevVVMwW52V2neE3H79a.wp8ZoItrwosk54.Siz5Fbw55X9YIBsW';

		$this->db->table('misc')->insert([
			'key'   => 'password',
			'value' => $hash,
		]);

		$this->seeInDatabase('misc', ['value' => $hash]);
	}

}
