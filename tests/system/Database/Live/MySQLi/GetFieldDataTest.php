<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\MySQLi;

use CodeIgniter\Database\MySQLi\Connection;
use CodeIgniter\Database\MySQLi\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class GetFieldDataTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;

    /**
     * @var Connection
     */
    protected $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('This test is only for MySQLi.');
        }

        $this->forge = Database::forge($this->db);
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
        $this->forge->createTable('test1');

        $fields = $this->db->getFieldData('test1');

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                (object) [
                    'name'        => 'id',
                    'type'        => 'int',
                    'max_length'  => null,
                    'default'     => null, // The default value is not defined.
                    'primary_key' => 1,
                    'nullable'    => false,
                ],
                (object) [
                    'name'        => 'text_not_null',
                    'type'        => 'varchar',
                    'max_length'  => 64,
                    'default'     => null, // The default value is not defined.
                    'primary_key' => 0,
                    'nullable'    => false,
                ],
                (object) [
                    'name'        => 'text_null',
                    'type'        => 'varchar',
                    'max_length'  => 64,
                    'default'     => null, // The default value is not defined.
                    'primary_key' => 0,
                    'nullable'    => true,
                ],
                (object) [
                    'name'        => 'int_default_0',
                    'type'        => 'int',
                    'max_length'  => null,
                    'default'     => '0', // int 0
                    'primary_key' => 0,
                    'nullable'    => false,
                ],
                (object) [
                    'name'        => 'text_default_null',
                    'type'        => 'varchar',
                    'max_length'  => 64,
                    'default'     => null, // NULL value
                    'primary_key' => 0,
                    'nullable'    => true,
                ],
                (object) [
                    'name'        => 'text_default_text_null',
                    'type'        => 'varchar',
                    'max_length'  => 64,
                    'default'     => 'null', // string "null"
                    'primary_key' => 0,
                    'nullable'    => false,
                ],
                (object) [
                    'name'        => 'text_default_abc',
                    'type'        => 'varchar',
                    'max_length'  => 64,
                    'default'     => 'abc', // string "abc"
                    'primary_key' => 0,
                    'nullable'    => false,
                ],
            ]),
            json_encode($fields)
        );

        $this->forge->dropTable('test1', true);
    }
}
