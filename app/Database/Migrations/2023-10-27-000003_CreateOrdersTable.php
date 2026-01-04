<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdersTable extends Migration
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
            'tracking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '6',
                'unique'     => true,
            ],
            'simcard_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'buyer_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'buyer_national_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
            ],
            'buyer_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '11',
            ],
            'buyer_father_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'buyer_birthdate' => [
                'type' => 'DATE',
            ],
            'amount' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'payment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'success', 'failed'],
                'default'    => 'pending',
            ],
            'authority' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'ref_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
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
        $this->forge->addForeignKey('simcard_id', 'simcards', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('orders');
    }

    public function down()
    {
        $this->forge->dropTable('orders');
    }
}
