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

use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\Database\SQLite3\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class GetFieldDataTest extends CIUnitTestCase
{
    /**
     * @var Connection
     */
    protected $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect($this->DBGroup);
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->markTestSkipped('This test is only for SQLite3.');
        }

        $config = [
            'DBDriver' => 'SQLite3',
            'database' => 'database.db',
            'DBDebug'  => true,
        ];
        $this->db    = db_connect($config);
        $this->forge = Database::forge($config);
    }

    public function testGetFieldData(): void
    {
        $this->forge->dropTable('test1', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'text_not_null' => [
                'type' => 'VARCHAR',
            ],
            'text_null' => [
                'type' => 'VARCHAR',
                'null' => true,
            ],
            'int_default_0' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'text_default_null' => [
                'type'    => 'VARCHAR',
                'default' => null,
            ],
            'text_default_text_null' => [
                'type'    => 'VARCHAR',
                'default' => 'null',
            ],
            'text_default_abc' => [
                'type'    => 'VARCHAR',
                'default' => 'abc',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('test1');

        $fields = $this->db->getFieldData('test1');

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                (object) [
                    'name'        => 'id',
                    'type'        => 'INTEGER',
                    'max_length'  => null,
                    'default'     => null, // The default value is not defined.
                    'primary_key' => true,
                    'nullable'    => true,
                ],
                (object) [
                    'name'        => 'text_not_null',
                    'type'        => 'VARCHAR',
                    'max_length'  => null,
                    'default'     => null, // The default value is not defined.
                    'primary_key' => false,
                    'nullable'    => false,
                ],
                (object) [
                    'name'        => 'text_null',
                    'type'        => 'VARCHAR',
                    'max_length'  => null,
                    'default'     => null, // The default value is not defined.
                    'primary_key' => false,
                    'nullable'    => true,
                ],
                (object) [
                    'name'        => 'int_default_0',
                    'type'        => 'INT',
                    'max_length'  => null,
                    'default'     => '0', // int 0
                    'primary_key' => false,
                    'nullable'    => false,
                ],
                (object) [
                    'name'        => 'text_default_null',
                    'type'        => 'VARCHAR',
                    'max_length'  => null,
                    'default'     => 'NULL', // NULL value
                    'primary_key' => false,
                    'nullable'    => true,
                ],
                (object) [
                    'name'        => 'text_default_text_null',
                    'type'        => 'VARCHAR',
                    'max_length'  => null,
                    'default'     => "'null'", // string "null"
                    'primary_key' => false,
                    'nullable'    => false,
                ],
                (object) [
                    'name'        => 'text_default_abc',
                    'type'        => 'VARCHAR',
                    'max_length'  => null,
                    'default'     => "'abc'", // string "abc"
                    'primary_key' => false,
                    'nullable'    => false,
                ],
            ]),
            json_encode($fields)
        );

        $this->forge->dropTable('test1', true);
    }
}
