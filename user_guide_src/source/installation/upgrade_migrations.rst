Upgrade Migrations
##################

.. contents::
    :local:
    :depth: 1

Documentations
==============

- `Database Migrations Documentation Codeigniter 3.X <http://codeigniter.com/userguide3/libraries/migration.html?highlight=migration>`_
- `Database Migrations Documentation Codeigniter 4.X </dbmgmt/migration.html?highlight=migration>`_

What has been changed
=====================

- First of all, the sequential naming (``001_create_users``, ``002_create_posts``) of migrations is not longer supported. Version 4 of CodeIgniter only supports the timestamp scheme (``20121031100537_create_users``, ``20121031500638_create_posts``) . If you have used sequential naming you have to rename each migration file.
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

Code Example
============

Codeigniter Version 3.11
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
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE,
                ),
                'blog_title' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                ),
                'blog_description' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                ),
            ));
            $this->dbforge->add_key('blog_id', TRUE);
            $this->dbforge->create_table('blog');
        }

        public function down()
        {
            $this->dbforge->drop_table('blog');
        }
    }

Codeigniter Version 4.x
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
