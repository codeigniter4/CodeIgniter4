################
Database Seeding
################

Database seeding is a simple way to add data into your database. It is especially useful during development where
you need to populate the database with sample data that you can develop against, but it is not limited to that.
Seeds can contain static data that you don't want to include in a migration, like countries, or geo-coding tables,
event or setting information, and more.

Database seeds are simple classes that must have a **run()** method, and extend **CodeIgniter\Database\Seeder**.
Within the **run()** the class can create any form of data that it needs to. It has access to the database
connection and the forge through ``$this->db`` and ``$this->forge``, respectively. Seed files must be
stored within the **app/Database/Seeds** directory. The name of the file must match the name of the class.
::

        <?php namespace App\Database\Seeds;

	class SimpleSeeder extends \CodeIgniter\Database\Seeder
	{
		public function run()
		{
			$data = [
				'username' => 'darth',
				'email'    => 'darth@theempire.com'
			];

			// Simple Queries
			$this->db->query("INSERT INTO users (username, email) VALUES(:username:, :email:)",
				$data
			);

			// Using Query Builder
			$this->db->table('users')->insert($data);
		}
	}

Nesting Seeders
===============

Seeders can call other seeders, with the **call()** method. This allows you to easily organize a central seeder,
but organize the tasks into separate seeder files::

        <?php namespace App\Database\Seeds;

	class TestSeeder extends \CodeIgniter\Database\Seeder
	{
		public function run()
		{
			$this->call('UserSeeder');
			$this->call('CountrySeeder');
			$this->call('JobSeeder');
		}
	}

You can also use a fully-qualified class name in the **call()** method, allowing you to keep your seeders
anywhere the autoloader can find them. This is great for more modular code bases::

	public function run()
	{
		$this->call('UserSeeder');
		$this->call('My\Database\Seeds\CountrySeeder');
	}

Using Seeders
=============

You can grab a copy of the main seeder through the database config class::

	$seeder = \Config\Database::seeder();
	$seeder->call('TestSeeder');

Command Line Seeding
--------------------

You can also seed data from the command line, as part of the Migrations CLI tools, if you don't want to create
a dedicated controller::

	> php spark db:seed TestSeeder

