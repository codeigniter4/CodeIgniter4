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

namespace CodeIgniter\Database\Live\SQLSRV;

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
        if ($this->db->DBDriver !== 'SQLSRV') {
            $this->markTestSkipped('This test is only for SQLSRV.');
        }

        $this->forge = Database::forge($this->db);
    }

    public function testGetFieldDataDefault(): void
    {
        $this->createTableForDefault();

        $fields = $this->db->getFieldData($this->table);

        $expected = [
            (object) [
                'name'       => 'id',
                'type'       => 'int',
                'max_length' => 10,
                'nullable'   => false,
                'default'    => null, // The default value is not defined.
                // 'primary_key' => 1,
            ],
            (object) [
                'name'       => 'text_not_null',
                'type'       => 'varchar',
                'max_length' => 64,
                'nullable'   => false,
                'default'    => null, // The default value is not defined.
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_null',
                'type'       => 'varchar',
                'max_length' => 64,
                'nullable'   => true,
                'default'    => null, // The default value is not defined.
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'int_default_0',
                'type'       => 'int',
                'max_length' => 10,
                'nullable'   => false,
                'default'    => '((0))', // int 0
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_default_null',
                'type'       => 'varchar',
                'max_length' => 64,
                'nullable'   => true,
                'default'    => '(NULL)', // NULL value
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_default_text_null',
                'type'       => 'varchar',
                'max_length' => 64,
                'nullable'   => false,
                'default'    => "('null')", // string "null"
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_default_abc',
                'type'       => 'varchar',
                'max_length' => 64,
                'nullable'   => false,
                'default'    => "('abc')", // string "abc"
                // 'primary_key' => 0,
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
                'name'       => 'id',
                'type'       => 'int',
                'max_length' => 10,
                'nullable'   => false,
                'default'    => null,
            ],
            1 => (object) [
                'name'       => 'type_varchar',
                'type'       => 'varchar',
                'max_length' => 40,
                'nullable'   => true,
                'default'    => null,
            ],
            2 => (object) [
                'name'       => 'type_char',
                'type'       => 'char',
                'max_length' => 10,
                'nullable'   => true,
                'default'    => null,
            ],
            3 => (object) [
                'name'       => 'type_text',
                'type'       => 'text',
                'max_length' => 2_147_483_647,
                'nullable'   => true,
                'default'    => null,
            ],
            4 => (object) [
                'name'       => 'type_smallint',
                'type'       => 'smallint',
                'max_length' => 5,
                'nullable'   => true,
                'default'    => null,
            ],
            5 => (object) [
                'name'       => 'type_integer',
                'type'       => 'int',
                'max_length' => 10,
                'nullable'   => true,
                'default'    => null,
            ],
            6 => (object) [
                'name'       => 'type_float',
                'type'       => 'float',
                'max_length' => 53,
                'nullable'   => true,
                'default'    => null,
            ],
            7 => (object) [
                'name'       => 'type_numeric',
                'type'       => 'numeric',
                'max_length' => 18,
                'nullable'   => true,
                'default'    => null,
            ],
            8 => (object) [
                'name'       => 'type_date',
                'type'       => 'date',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            9 => (object) [
                'name'       => 'type_time',
                'type'       => 'time',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            10 => (object) [
                'name'       => 'type_datetime',
                'type'       => 'datetime',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            11 => (object) [
                'name'       => 'type_timestamp',
                'type'       => 'datetime',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            12 => (object) [
                'name'       => 'type_bigint',
                'type'       => 'bigint',
                'max_length' => 19,
                'nullable'   => true,
                'default'    => null,
            ],
            13 => (object) [
                'name'       => 'type_real',
                'type'       => 'real',
                'max_length' => 24,
                'nullable'   => true,
                'default'    => null,
            ],
            14 => (object) [
                'name'       => 'type_enum',
                'type'       => 'varchar',
                'max_length' => 5,
                'nullable'   => true,
                'default'    => null,
            ],
            15 => (object) [
                'name'       => 'type_decimal',
                'type'       => 'decimal',
                'max_length' => 18,
                'nullable'   => true,
                'default'    => null,
            ],
            16 => (object) [
                'name'       => 'type_boolean',
                'type'       => 'bit',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
        ];
        $this->assertSameFieldData($expected, $fields);
    }
}
