<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
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
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '100', // Typically keys are not that long, but 100 is safe
                'unique'     => true,
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'string',
            ],
            'group' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'general',
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
        $this->forge->createTable('settings');

        // Seeding default settings
        $seeder = \Config\Database::seeder();
        // Since we are in migration, usually we don't seed here, but we can insert data.
        // Or we can leave it empty. The prompt mentions "Default settings to seed".
        // I will insert them.

        $data = [
            [
                'key' => 'price_per_page',
                'value' => '10000',
                'type' => 'number',
                'group' => 'pricing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'default_ai_provider',
                'value' => 'claude',
                'type' => 'string',
                'group' => 'ai',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'max_upload_size',
                'value' => '10485760',
                'type' => 'number',
                'group' => 'upload',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
             [
                'key' => 'max_images_per_project',
                'value' => '50',
                'type' => 'number',
                'group' => 'upload',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
             [
                'key' => 'allowed_image_types',
                'value' => 'jpg,jpeg,png,webp',
                'type' => 'string',
                'group' => 'upload',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('settings')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
