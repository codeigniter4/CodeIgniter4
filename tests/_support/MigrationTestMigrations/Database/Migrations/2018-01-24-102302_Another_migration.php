<?php

namespace Tests\Support\MigrationTestMigrations\Database\Migrations;

class Migration_another_migration extends \CodeIgniter\Database\Migration
{
    public function up()
    {
        $fields = [
            'value' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ];
        $this->forge->addColumn('foo', $fields);

        $this->db->table('foo')->insert([
            'key'   => 'foobar',
            'value' => 'raboof',
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('foo')) {
            $this->forge->dropColumn('foo', 'value');
        }
    }
}
