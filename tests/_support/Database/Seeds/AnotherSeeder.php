<?php namespace Tests\Support\Database\Seeds;

class AnotherSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$row = [
			'name'    => 'Jerome Lohan',
			'email'   => 'jlo@lohanenterprises.com',
			'country' => 'UK',
		];

		$this->db->table('user')->insert($row);
	}
}
