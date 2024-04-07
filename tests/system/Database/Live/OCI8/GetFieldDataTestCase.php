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

namespace CodeIgniter\Database\Live\OCI8;

use CodeIgniter\Database\Live\AbstractGetFieldDataTestCase;
use Config\Database;
use LogicException;
use stdClass;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class GetFieldDataTestCase extends AbstractGetFieldDataTestCase
{
    protected function createForge(): void
    {
        if ($this->db->DBDriver !== 'OCI8') {
            $this->markTestSkipped('This test is only for OCI8.');
        }

        $this->forge = Database::forge($this->db);
    }

    private function getFieldMetaData(string $column, string $table): stdClass
    {
        $fields = $this->db->getFieldData($table);

        $name = array_search(
            $column,
            array_column($fields, 'name'),
            true
        );

        if ($name === false) {
            throw new LogicException('Field not found: ' . $column);
        }

        return $fields[$name];
    }

    public function testGetFieldDataDefault(): void
    {
        $this->createTableForDefault();

        $fields = $this->db->getFieldData($this->table);

        $idDefault = $this->getFieldMetaData('id', $this->table)->default;
        $this->assertMatchesRegularExpression('/"ORACLE"."ISEQ\$\$_[0-9]+".nextval/', $idDefault);

        $expected = [
            (object) [
                'name'       => 'id',
                'type'       => 'NUMBER',
                'max_length' => '11',
                'nullable'   => false,
                'default'    => $idDefault, // The default value is not defined.
                // 'primary_key' => 1,
            ],
            (object) [
                'name'       => 'text_not_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'nullable'   => false,
                'default'    => null, // The default value is not defined.
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'nullable'   => true,
                'default'    => null, // The default value is not defined.
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'int_default_0',
                'type'       => 'NUMBER',
                'max_length' => '11',
                'nullable'   => false,
                'default'    => '0 ', // int 0
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_default_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'nullable'   => true,
                'default'    => 'NULL ', // NULL value
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_default_text_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'nullable'   => false,
                'default'    => "'null' ", // string "null"
                // 'primary_key' => 0,
            ],
            (object) [
                'name'       => 'text_default_abc',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'nullable'   => false,
                'default'    => "'abc' ", // string "abc"
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
                'type'       => 'NUMBER',
                'max_length' => '20',
                'nullable'   => false,
                'default'    => $this->getFieldMetaData('id', $this->table)->default,
            ],
            1 => (object) [
                'name'       => 'type_varchar',
                'type'       => 'VARCHAR2',
                'max_length' => '40',
                'nullable'   => true,
                'default'    => null,
            ],
            2 => (object) [
                'name'       => 'type_char',
                'type'       => 'CHAR',
                'max_length' => '10',
                'nullable'   => true,
                'default'    => null,
            ],
            3 => (object) [
                'name'       => 'type_text',
                'type'       => 'VARCHAR2',
                'max_length' => '4000',
                'nullable'   => true,
                'default'    => null,
            ],
            4 => (object) [
                'name'       => 'type_smallint',
                'type'       => 'NUMBER',
                'max_length' => '5',
                'nullable'   => true,
                'default'    => null,
            ],
            5 => (object) [
                'name'       => 'type_integer',
                'type'       => 'NUMBER',
                'max_length' => '11',
                'nullable'   => true,
                'default'    => null,
            ],
            6 => (object) [
                'name'       => 'type_float',
                'type'       => 'FLOAT',
                'max_length' => '126',
                'nullable'   => true,
                'default'    => null,
            ],
            7 => (object) [
                'name'       => 'type_numeric',
                'type'       => 'NUMBER',
                'max_length' => '18',
                'nullable'   => true,
                'default'    => null,
            ],
            8 => (object) [
                'name'       => 'type_date',
                'type'       => 'DATE',
                'max_length' => '7',
                'nullable'   => true,
                'default'    => null,
            ],
            9 => (object) [
                'name'       => 'type_time',
                'type'       => 'DATE',
                'max_length' => '7',
                'nullable'   => true,
                'default'    => null,
            ],
            10 => (object) [
                'name'       => 'type_datetime',
                'type'       => 'DATE',
                'max_length' => '7',
                'nullable'   => true,
                'default'    => null,
            ],
            11 => (object) [
                'name'       => 'type_timestamp',
                'type'       => 'TIMESTAMP(6)',
                'max_length' => '11',
                'nullable'   => true,
                'default'    => null,
            ],
            12 => (object) [
                'name'       => 'type_bigint',
                'type'       => 'NUMBER',
                'max_length' => '19',
                'nullable'   => true,
                'default'    => null,
            ],
            13 => (object) [
                'name'       => 'type_real',
                'type'       => 'FLOAT',
                'max_length' => '63',
                'nullable'   => true,
                'default'    => null,
            ],
            14 => (object) [
                'name'       => 'type_enum',
                'type'       => 'VARCHAR2',
                'max_length' => '5',
                'nullable'   => true,
                'default'    => null,
            ],
            15 => (object) [
                'name'       => 'type_set',
                'type'       => 'VARCHAR2',
                'max_length' => '3',
                'nullable'   => true,
                'default'    => null,
            ],
            16 => (object) [
                'name'       => 'type_mediumtext',
                'type'       => 'VARCHAR2',
                'max_length' => '4000',
                'nullable'   => true,
                'default'    => null,
            ],
            17 => (object) [
                'name'       => 'type_double',
                'type'       => 'FLOAT',
                'max_length' => '126',
                'nullable'   => true,
                'default'    => null,
            ],
            18 => (object) [
                'name'       => 'type_decimal',
                'type'       => 'NUMBER',
                'max_length' => '18',
                'nullable'   => true,
                'default'    => null,
            ],
            19 => (object) [
                'name'       => 'type_blob',
                'type'       => 'BLOB',
                'max_length' => '4000',
                'nullable'   => true,
                'default'    => null,
            ],
            20 => (object) [
                'name'       => 'type_boolean',
                'type'       => 'NUMBER',
                'max_length' => '1',
                'nullable'   => true,
                'default'    => null,
            ],
        ];
        $this->assertSameFieldData($expected, $fields);
    }
}
