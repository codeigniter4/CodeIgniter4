################
Database Seeding
################

Database seeding is a simple way to add data into your database. It is especially useful during development where
you need to populate the database with sample data that you can develop against, but it is not limited to that.
Seeds can contain static data that you don't want to include in a migration, like countries, or geo-coding tables,
event or setting information, and more.

Database seeds are simple classes that must have a **run()** method, and extend ``CodeIgniter\Database\Seeder``.
Within the **run()** the class can create any form of data that it needs to. It has access to the database
connection and the forge through ``$this->db`` and ``$this->forge``, respectively. Seed files must be
stored within the **app/Database/Seeds** directory. The name of the file must match the name of the class.
::

	<?php

	namespace App\Database\Seeds;

	use CodeIgniter\Database\Seeder;

	class SimpleSeeder extends Seeder
	{
		public function run()
		{
			$data = [
				'username' => 'darth',
				'email'    => 'darth@theempire.com'
			];

			// Simple Queries
			$this->db->query("INSERT INTO users (username, email) VALUES(:username:, :email:)", $data);

			// Using Query Builder
			$this->db->table('users')->insert($data);
		}
	}

Nesting Seeders
===============

Seeders can call other seeders, with the **call()** method. This allows you to easily organize a central seeder,
but organize the tasks into separate seeder files::

	<?php

	namespace App\Database\Seeds;

	use CodeIgniter\Database\Seeder;

	class TestSeeder extends Seeder
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

Using Faker
===========

If you want to automate the generation of seed data, you can use
the `Faker library <https://github.com/fzaninotto/faker>`_.

To install Faker into your project::

	> composer require --dev fzaninotto/faker

After installation, an instance of ``Faker\Generator`` is available in the main ``Seeder``
class and is accessible by all child seeders. You must use the static method ``faker()``
to access the instance.

::

	<?php

	namespace App\Database\Seeds;

	use CodeIgniter\Database\Seeder;

	class UserSeeder extends Seeder
	{
		public function run()
		{
			$model = model('UserModel');

			$model->insert([
				'email'      => static::faker()->email,
				'ip_address' => static::faker()->ipv4,
			]);
		}
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

Creating Seed Files
-------------------

Using the command line, you can easily generate seed files.

::

	// This command will create a UserSeeder seed file
	// located at app/Database/Seeds/ directory.
	> php spark make:seeder UserSeeder

You can supply the **root** namespace where the seed file will be stored by supplying the ``-n`` option::

	> php spark make:seeder MySeeder -n Acme\Blog

If ``Acme\Blog`` is mapped to ``app/Blog`` directory, then this command will save the
seed file to ``app/Blog/Database/Seeds/``.

Supplying the ``--force`` option will overwrite existing files in destination.
