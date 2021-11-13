Upgrade Migrations
##################

.. contents::
    :local:
    :depth: 1

Documentations
==============

- `Database Migrations Documentation CodeIgniter 3.X <http://codeigniter.com/userguide3/libraries/migration.html>`_
- :doc:`Database Migrations Documentation CodeIgniter 4.X </dbmgmt/migration>`

What has been changed
=====================

- First of all, the sequential naming (``001_create_users``, ``002_create_posts``) of migrations is not longer supported. Version 4 of CodeIgniter only supports the timestamp scheme (``20121031100537_create_users``, ``20121031500638_create_posts``) . If you have used sequential naming you have to rename each migration file.
- The migration table definition was changed. If you upgrade from CI3 to CI4 and use the same database,
  You need to upgrade the migration table definition and its data.
- The migration procedure has been also changed. You can now migrate the database with a simple CLI command::

    > php spark migrate

Upgrade Guide
=============

1. If your v3 project uses sequential migration names you have to change those to timestamp names.
2. You have to move all migration files to the new folder ``app/Database/Migrations``.
3. Remove the following line ``defined('BASEPATH') OR exit('No direct script access allowed');``.
4. Add this line just after the opening php tag: ``namespace App\Database\Migrations;``.
5. Below the ``namespace App\Database\Migrations;`` line add this line: ``use CodeIgniter\Database\Migration;``
6. Replace ``extends CI_Migration`` with ``extends Migration``.
7. The method names within the ``Forge`` class has been changed to use camelCase. For example:

    - ``$this->dbforge->add_field`` to ``$this->forge->addField``
    - ``$this->dbforge->add_key`` to ``$this->forge->addKey``
    - ``$this->dbforge->create_table`` to ``$this->forge->addTable``
    - ``$this->dbforge->drop_table`` to ``$this->forge->addTable``

8. (optional) You can change the array syntax from ``array(...)`` to ``[...]``
9. Upgrade the migration table, if you use the same database.

    - **(development)** Run the CI4 migration in the development environment or so with brand new database, to create the new migration table.
    - **(development)** Export the migration table.
    - **(production)** Drop (or rename) the existing CI3 migration table.
    - **(production)** Import the new migration table and the data.

Code Example
============

CodeIgniter Version 3.11
------------------------

Path: ``application/migrations``::

    <?php

    defined('BASEPATH') OR exit('No direct script access allowed');

    class Migration_Add_blog extends CI_Migration
    {
        public function up()
        {
            $this->dbforge->add_field(array(
                'blog_id' => array(
                    'type' => 'INT',
                    'constraint' => 5,
                    'unsigned' => true,
                    'auto_increment' => true,
                ),
                'blog_title' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                ),
                'blog_description' => array(
                    'type' => 'TEXT',
                    'null' => true,
                ),
            ));
            $this->dbforge->add_key('blog_id', true);
            $this->dbforge->create_table('blog');
        }

        public function down()
        {
            $this->dbforge->drop_table('blog');
        }
    }

CodeIgniter Version 4.x
-----------------------

Path: ``app/Database/Migrations``::

    <?php

    namespace App\Database\Migrations;

    use CodeIgniter\Database\Migration;

    class AddBlog extends Migration
    {
        public function up()
        {
            $this->forge->addField([
                'blog_id' => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'blog_title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
                'blog_description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('blog_id', true);
            $this->forge->createTable('blog');
        }

        public function down()
        {
            $this->forge->dropTable('blog');
        }
    }

Search & Replace
================

You can use to following table to search & replace your old CI3 files.

+------------------------------+----------------------------+
|  Search                      | Replace                    |
+==============================+============================+
| extends CI_Migration         | extends Migration          |
+------------------------------+----------------------------+
| $this->dbforge->add_field    | $this->forge->addField     |
+------------------------------+----------------------------+
| $this->dbforge->add_key      | $this->forge->addKey       |
+------------------------------+----------------------------+
| $this->dbforge->create_table | $this->forge->createTable  |
+------------------------------+----------------------------+
| $this->dbforge->drop_table   | $this->forge->dropTable    |
+------------------------------+----------------------------+
