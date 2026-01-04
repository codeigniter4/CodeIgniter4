<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagesTable extends Migration
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
            'page_number' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'layout_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'content' => [
                'type' => 'JSON',
            ],
            'ai_suggestions' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'is_approved' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'rendered_image' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
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
        // Unique key for project_id + page_number
        // CodeIgniter 4 migration doesn't have direct method for composite unique in addKey easily without raw sql sometimes,
        // but let's try addUniqueKey if available or just addKey
        // The prompt says: $table->unique(['project_id', 'page_number']);
        // In CI4 forge:
        $this->forge->addUniqueKey(['project_id', 'page_number']);

        $this->forge->createTable('pages');
    }

    public function down()
    {
        $this->forge->dropTable('pages');
    }
}
