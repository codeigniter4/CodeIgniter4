<?php

class CITestSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		// Job Data
		$data = array(
			'user' => array(
				array('name' => 'Derek Jones', 'email' => 'derek@world.com', 'country' => 'US'),
				array('name' => 'Ahmadinejad', 'email' => 'ahmadinejad@world.com', 'country' => 'Iran'),
				array('name' => 'Richard A Causey', 'email' => 'richard@world.com', 'country' => 'US'),
				array('name' => 'Chris Martin', 'email' => 'chris@world.com', 'country' => 'UK')
			),
			'job' => array(
				array('name' => 'Developer', 'description' => 'Awesome job, but sometimes makes you bored'),
				array('name' => 'Politician', 'description' => 'This is not really a job'),
				array('name' => 'Accountant', 'description' => 'Boring job, but you will get free snack at lunch'),
				array('name' => 'Musician', 'description' => 'Only Coldplay can actually called Musician')
			),
			'misc' => array(
				array('key' => '\\xxxfoo456', 'value' => 'Entry with \\xxx'),
				array('key' => '\\%foo456', 'value' => 'Entry with \\%'),
				array('key' => 'spaces and tabs', 'value' => ' One  two   three	tab')
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
