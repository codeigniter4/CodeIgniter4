<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\MigrationTestMigrations\Database\Migrations;

class Migration_some_migration extends \CodeIgniter\Database\Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->createTable('foo');

        $this->db->table('foo')->insert([
            'key' => 'foobar',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('foo', true);
    }
}
