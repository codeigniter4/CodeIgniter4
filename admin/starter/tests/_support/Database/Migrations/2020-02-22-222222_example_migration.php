<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExampleMigration extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        $this->forge->addField('id');
        $this->forge->addField([
            'name'       => ['type' => 'varchar', 'constraint' => 31],
            'uid'        => ['type' => 'varchar', 'constraint' => 31],
            'class'      => ['type' => 'varchar', 'constraint' => 63],
            'icon'       => ['type' => 'varchar', 'constraint' => 31],
            'summary'    => ['type' => 'varchar', 'constraint' => 255],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);

        $this->forge->addKey('name');
        $this->forge->addKey('uid');
        $this->forge->addKey(['deleted_at', 'id']);
        $this->forge->addKey('created_at');

        $this->forge->createTable('factories');
    }

    public function down(): void
    {
        $this->forge->dropTable('factories');
    }
}
