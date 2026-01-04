<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['image', 'document', 'other'],
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'is_used' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('assets');
    }

    public function down()
    {
        $this->forge->dropTable('assets');
    }
}
