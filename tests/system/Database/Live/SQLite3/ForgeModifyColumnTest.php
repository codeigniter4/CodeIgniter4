<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\SQLite3;

use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class ForgeModifyColumnTest extends CIUnitTestCase
{
    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect($this->DBGroup);

        if ($this->db->DBDriver !== 'SQLite3') {
            $this->markTestSkipped('This test is only for SQLite3.');
        }

        $this->forge = Database::forge($this->DBGroup);
    }

    public function testModifyColumnRename(): void
    {
        $table = 'forge_test_three';

        $this->forge->dropTable($table, true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'int' => [
                'type'       => 'INT',
                'constraint' => 10,
                'null'       => false,
                'default'    => 0,
            ],
            'varchar' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'null'       => false,
            ],
            'decimal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,5',
                'default'    => 0.1,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable($table);

        $this->assertTrue($this->db->fieldExists('name', $table));

        $this->forge->modifyColumn($table, [
            'name' => [
                'name'       => 'altered',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->db->resetDataCache();

        $fieldData = $this->db->getFieldData($table);
        $fields    = [];

        foreach ($fieldData as $obj) {
            $fields[$obj->name] = $obj;
        }

        $this->assertFalse($this->db->fieldExists('name', $table));
        $this->assertTrue($this->db->fieldExists('altered', $table));

        $this->assertFalse($fields['int']->nullable);
        $this->assertSame('0', $fields['int']->default);

        $this->assertFalse($fields['varchar']->nullable);
        $this->assertNull($fields['varchar']->default);

        $this->assertFalse($fields['decimal']->nullable);
        $this->assertSame('0.1', $fields['decimal']->default);

        $this->assertTrue($fields['altered']->nullable);
        $this->assertNull($fields['altered']->default);

        $this->forge->dropTable($table, true);
    }
}
