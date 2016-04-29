<?php

class CITestSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		// Job Data
		$data = array(
			'user' => array(
				array('id' => 1, 'name' => 'Derek Jones', 'email' => 'derek@world.com', 'country' => 'US'),
				array('id' => 2, 'name' => 'Ahmadinejad', 'email' => 'ahmadinejad@world.com', 'country' => 'Iran'),
				array('id' => 3, 'name' => 'Richard A Causey', 'email' => 'richard@world.com', 'country' => 'US'),
				array('id' => 4, 'name' => 'Chris Martin', 'email' => 'chris@world.com', 'country' => 'UK')
			),
			'job' => array(
				array('id' => 1, 'name' => 'Developer', 'description' => 'Awesome job, but sometimes makes you bored'),
				array('id' => 2, 'name' => 'Politician', 'description' => 'This is not really a job'),
				array('id' => 3, 'name' => 'Accountant', 'description' => 'Boring job, but you will get free snack at lunch'),
				array('id' => 4, 'name' => 'Musician', 'description' => 'Only Coldplay can actually called Musician')
			),
			'misc' => array(
				array('id' => 1, 'key' => '\\xxxfoo456', 'value' => 'Entry with \\xxx'),
				array('id' => 2, 'key' => '\\%foo456', 'value' => 'Entry with \\%'),
				array('id' => 3, 'key' => 'spaces and tabs', 'value' => ' One  two   three	tab')
			)
		);

		foreach ($data as $table => $dummy_data)
		{
			$this->db->table($table)->truncate();

			foreach ($dummy_data as $single_dummy_data)
			{
				$this->db->table($table)->insert($single_dummy_data);
			}
		}
	}

	//--------------------------------------------------------------------


}
