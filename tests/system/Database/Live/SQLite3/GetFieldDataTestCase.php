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

use CodeIgniter\Database\Live\AbstractGetFieldDataTestCase;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class GetFieldDataTestCase extends AbstractGetFieldDataTestCase
{
    protected function createForge(): void
    {
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->markTestSkipped('This test is only for SQLite3.');
        }

        $config = [
            'DBDriver' => 'SQLite3',
            'database' => 'database.db',
            'DBDebug'  => true,
        ];
        $this->db    = db_connect($config, false);
        $this->forge = Database::forge($config);
    }

    public function testGetFieldDataDefault(): void
    {
        $this->createTableForDefault();

        $fields = $this->db->getFieldData($this->table);

        $expected = [
            (object) [
                'name'        => 'id',
                'type'        => 'INTEGER',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null, // The default value is not defined.
                'primary_key' => 1,
            ],
            (object) [
                'name'        => 'text_not_null',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => false,
                'default'     => null, // The default value is not defined.
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_null',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null, // The default value is not defined.
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'int_default_0',
                'type'        => 'INT',
                'max_length'  => null,
                'nullable'    => false,
                'default'     => '0', // int 0
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_default_null',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => 'NULL', // NULL value
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_default_text_null',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => false,
                'default'     => "'null'", // string "null"
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_default_abc',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => false,
                'default'     => "'abc'", // string "abc"
                'primary_key' => 0,
            ],
        ];
        $this->assertSameFieldData($expected, $fields);
    }

    protected function createTableCompositePrimaryKey(): void
    {
        $this->forge->dropTable($this->table, true);

        $this->forge->addField([
            'pk1' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'pk2' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'text' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
        ]);
        $this->forge->addPrimaryKey(['pk1', 'pk2']);
        $this->forge->createTable($this->table);
    }

    public function testGetFieldDataCompositePrimaryKey(): void
    {
        $this->createTableCompositePrimaryKey();

        $fields = $this->db->getFieldData($this->table);

        $expected = [
            (object) [
                'name'        => 'pk1',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => false,
                'default'     => null,
                'primary_key' => 1,
            ],
            (object) [
                'name'        => 'pk2',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => false,
                'default'     => null,
                'primary_key' => 1,
            ],
            (object) [
                'name'        => 'text',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => false,
                'default'     => null,
                'primary_key' => 0,
            ],
        ];
        $this->assertSameFieldData($expected, $fields);
    }

    public function testGetFieldDataType(): void
    {
        $this->createTableForType();

        $fields = $this->db->getFieldData($this->table);

        $expected = [
            0 => (object) [
                'name'        => 'id',
                'type'        => 'INTEGER',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 1,
            ],
            1 => (object) [
                'name'        => 'type_varchar',
                'type'        => 'VARCHAR',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            2 => (object) [
                'name'        => 'type_char',
                'type'        => 'CHAR',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            3 => (object) [
                'name'        => 'type_text',
                'type'        => 'TEXT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            4 => (object) [
                'name'        => 'type_smallint',
                'type'        => 'SMALLINT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            5 => (object) [
                'name'        => 'type_integer',
                'type'        => 'INTEGER',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            6 => (object) [
                'name'        => 'type_float',
                'type'        => 'FLOAT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            7 => (object) [
                'name'        => 'type_numeric',
                'type'        => 'NUMERIC',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            8 => (object) [
                'name'        => 'type_date',
                'type'        => 'DATE',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            9 => (object) [
                'name'        => 'type_time',
                'type'        => 'TIME',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            10 => (object) [
                'name'        => 'type_datetime',
                'type'        => 'DATETIME',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            11 => (object) [
                'name'        => 'type_timestamp',
                'type'        => 'TIMESTAMP',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            12 => (object) [
                'name'        => 'type_bigint',
                'type'        => 'BIGINT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            13 => (object) [
                'name'        => 'type_real',
                'type'        => 'REAL',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            14 => (object) [
                'name'        => 'type_enum',
                'type'        => 'TEXT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            15 => (object) [
                'name'        => 'type_set',
                'type'        => 'TEXT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            16 => (object) [
                'name'        => 'type_mediumtext',
                'type'        => 'MEDIUMTEXT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            17 => (object) [
                'name'        => 'type_double',
                'type'        => 'DOUBLE',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            18 => (object) [
                'name'        => 'type_decimal',
                'type'        => 'DECIMAL',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            19 => (object) [
                'name'        => 'type_blob',
                'type'        => 'BLOB',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            20 => (object) [
                'name'        => 'type_boolean',
                'type'        => 'INT',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
        ];
        $this->assertSameFieldData($expected, $fields);
    }
}
