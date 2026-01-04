<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSimcardsTable extends Migration
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
            'number' => [
                'type'       => 'VARCHAR',
                'constraint' => '11',
                'unique'     => true,
            ],
            'price' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['free', 'sold'],
                'default'    => 'free',
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
        $this->forge->createTable('simcards');
    }

    public function down()
    {
        $this->forge->dropTable('simcards');
    }
}
