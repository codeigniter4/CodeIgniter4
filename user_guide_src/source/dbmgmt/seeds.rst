################
Database Seeding
################

Database seeding is a simple way to add data into your database. It is especially useful during development where
you need to populate the database with sample data that you can develop against, but it is not limited to that.
Seeders can contain static data that you don't want to include in a migration, like countries, or geo-coding tables,
event or setting information, and more.

.. contents::
    :local:
    :depth: 2

****************
Database Seeders
****************

Database seeders are simple classes that must have a ``run()`` method, and extend ``CodeIgniter\Database\Seeder``.
Within the ``run()`` the class can create any form of data that it needs to. It has access to the database
connection and the forge through ``$this->db`` and ``$this->forge``, respectively. Seed files must be
stored within the **app/Database/Seeds** directory. The name of the file must match the name of the class.

.. literalinclude:: seeds/001.php

***************
Nesting Seeders
***************

Seeders can call other seeders, with the ``call()`` method. This allows you to easily organize a central seeder,
but organize the tasks into separate seeder files:

.. literalinclude:: seeds/002.php

You can also use a fully-qualified class name in the ``call()`` method, allowing you to keep your seeders
anywhere the autoloader can find them. This is great for more modular code bases:

.. literalinclude:: seeds/003.php

*************
Using Seeders
*************

You can grab a copy of the main seeder through the database config class:

.. literalinclude:: seeds/004.php

Command Line Seeding
====================

You can also seed data from the command line, as part of the Migrations CLI tools, if you don't want to create
a dedicated controller:

.. code-block:: console

    php spark db:seed TestSeeder

*********************
Creating Seeder Files
*********************

Using the command line, you can easily generate seed files:

.. code-block:: console

    php spark make:seeder user --suffix

The above command outputs **UserSeeder.php** file located at **app/Database/Seeds** directory.

You can supply the ``root`` namespace where the seed file will be stored by supplying the ``--namespace`` option:

For Unix:

.. code-block:: console

    php spark make:seeder MySeeder --namespace Acme\\Blog

For Windows:

.. code-block:: console

    php spark make:seeder MySeeder --namespace Acme\Blog

If ``Acme\Blog`` is mapped to **app/Blog** directory, then this command will generate **MySeeder.php** at **app/Blog/Database/Seeds** directory.

Supplying the ``--force`` option will overwrite existing files in destination.
