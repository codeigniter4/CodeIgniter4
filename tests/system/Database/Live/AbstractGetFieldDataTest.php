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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

abstract class AbstractGetFieldDataTest extends CIUnitTestCase
{
    /**
     * @var BaseConnection
     */
    protected $db;

    protected Forge $forge;
    protected string $table = 'test1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect($this->DBGroup);

        $this->createForge();
    }

    /**
     * Make sure that $db and $forge are instantiated.
     */
    abstract protected function createForge(): void;

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->forge->dropTable($this->table, true);
    }

    protected function createTableForDefault()
    {
        $this->forge->dropTable($this->table, true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'text_not_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'text_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'int_default_0' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'text_default_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => null,
            ],
            'text_default_text_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => 'null',
            ],
            'text_default_abc' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => 'abc',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable($this->table);
    }

    abstract public function testGetFieldDataDefault(): void;

    protected function assertSameFieldData(array $expected, array $actual)
    {
        $expected = json_decode(json_encode($expected), true);
        $names    = array_column($expected, 'name');
        array_multisort($names, SORT_ASC, $expected);

        $fields = json_decode(json_encode($actual), true);
        $names  = array_column($fields, 'name');
        array_multisort($names, SORT_ASC, $fields);

        $this->assertSame($expected, $fields);
    }
}
