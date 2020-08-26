<?php namespace Tests\Support\Database\Seeds;

class CITestSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		// Job Data
		$data = [
			'user'                   => [
				[
					'name'    => 'Derek Jones',
					'email'   => 'derek@world.com',
					'country' => 'US',
				],
				[
					'name'    => 'Ahmadinejad',
					'email'   => 'ahmadinejad@world.com',
					'country' => 'Iran',
				],
				[
					'name'    => 'Richard A Causey',
					'email'   => 'richard@world.com',
					'country' => 'US',
				],
				[
					'name'    => 'Chris Martin',
					'email'   => 'chris@world.com',
					'country' => 'UK',
				],
			],
			'job'                    => [
				[
					'name'        => 'Developer',
					'description' => 'Awesome job, but sometimes makes you bored',
				],
				[
					'name'        => 'Politician',
					'description' => 'This is not really a job',
				],
				[
					'name'        => 'Accountant',
					'description' => 'Boring job, but you will get free snack at lunch',
				],
				[
					'name'        => 'Musician',
					'description' => 'Only Coldplay can actually called Musician',
				],
			],
			'misc'                   => [
				[
					'key'   => '\\xxxfoo456',
					'value' => 'Entry with \\xxx',
				],
				[
					'key'   => '\\%foo456',
					'value' => 'Entry with \\%',
				],
				[
					'key'   => 'spaces and tabs',
					'value' => ' One  two   three	tab',
				],
			],
			'stringifypkey'          => [
				[
					'id'    => 'A01',
					'value' => 'test',
				],
			],
			'without_auto_increment' => [
				[
					'key'   => 'key',
					'value' => 'value',
				],
			],
			'type_test'              => [
				[
					'type_varchar'    => 'test',
					'type_char'       => 'test',
					'type_enum'       => 'appel',
					'type_set'        => 'one',
					'type_text'       => 'test text',
					'type_mediumtext' => 'test medium text',
					'type_smallint'   => 1,
					'type_integer'    => 123,
					'type_float'      => 10.1,
					'type_real'       => 11.21,
					'type_double'     => 23.22,
					'type_decimal'    => 123123.2234,
					'type_numeric'    => 123.23,
					'type_blob'       => 'test blob',
					'type_date'       => '2020-01-11T22:11:00.000+02:00',
					'type_time'       => '2020-07-18T15:22:00.000+02:00',
					'type_datetime'   => '2020-06-18T05:12:24.000+02:00',
					'type_timestamp'  => '2019-07-18T21:53:21.000+02:00',
					'type_bigint'     => 2342342,
				],
			],
		];
		//set SQL times to more correct format
		if ($this->db->DBDriver === 'SQLite3')
		{
			 $data['type_test'][0]['type_date']      = '2020/01/11';
			 $data['type_test'][0]['type_time']      = '15:22:00';
			 $data['type_test'][0]['type_datetime']  = '2020/06/18 05:12:24';
			 $data['type_test'][0]['type_timestamp'] = '2019/07/18 21:53:21';
		}
		elseif ($this->db->DBDriver === 'Postgre')
		{
			$data['type_test'][0]['type_time'] = '15:22:00';
			unset($data['type_test'][0]['type_enum']);
			unset($data['type_test'][0]['type_set']);
			unset($data['type_test'][0]['type_mediumtext']);
			unset($data['type_test'][0]['type_real']);
			unset($data['type_test'][0]['type_double']);
			unset($data['type_test'][0]['type_decimal']);
			unset($data['type_test'][0]['type_blob']);
		}
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
