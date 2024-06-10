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

namespace CodeIgniter\Database\Live\Postgre;

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
        if ($this->db->DBDriver !== 'Postgre') {
            $this->markTestSkipped('This test is only for Postgre.');
        }

        $this->forge = Database::forge($this->db);
    }

    public function testGetFieldDataDefault(): void
    {
        $this->createTableForDefault();

        $fields = $this->db->getFieldData('test1');

        $expected = [
            (object) [
                'name'       => 'id',
                'type'       => 'integer',
                'max_length' => '32',
                'nullable'   => false,
                // 'primary_key' => 1,
                'default' => "nextval('db_test1_id_seq'::regclass)", // The default value is not defined.
            ],
            (object) [
                'name'       => 'text_not_null',
                'type'       => 'character varying',
                'max_length' => '64',
                'nullable'   => false,
                // 'primary_key' => 0,
                'default' => null, // The default value is not defined.
            ],
            (object) [
                'name'       => 'text_null',
                'type'       => 'character varying',
                'max_length' => '64',
                'nullable'   => true,
                // 'primary_key' => 0,
                'default' => null, // The default value is not defined.
            ],
            (object) [
                'name'       => 'int_default_0',
                'type'       => 'integer',
                'max_length' => '32',
                'nullable'   => false,
                // 'primary_key' => 0,
                'default' => '0', // int 0
            ],
            (object) [
                'name'       => 'text_default_null',
                'type'       => 'character varying',
                'max_length' => '64',
                'nullable'   => true,
                // 'primary_key' => 0,
                'default' => 'NULL::character varying', // NULL value
            ],
            (object) [
                'name'       => 'text_default_text_null',
                'type'       => 'character varying',
                'max_length' => '64',
                'nullable'   => false,
                // 'primary_key' => 0,
                'default' => "'null'::character varying", // string "null"
            ],
            (object) [
                'name'       => 'text_default_abc',
                'type'       => 'character varying',
                'max_length' => '64',
                'nullable'   => false,
                // 'primary_key' => 0,
                'default' => "'abc'::character varying", // string "abc"
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
                'type'       => 'integer',
                'max_length' => '32',
                'nullable'   => false,
                'default'    => 'nextval(\'db_test1_id_seq\'::regclass)',
            ],
            1 => (object) [
                'name'       => 'type_varchar',
                'type'       => 'character varying',
                'max_length' => '40',
                'nullable'   => true,
                'default'    => null,
            ],
            2 => (object) [
                'name'       => 'type_char',
                'type'       => 'character',
                'max_length' => '10',
                'nullable'   => true,
                'default'    => null,
            ],
            3 => (object) [
                'name'       => 'type_text',
                'type'       => 'text',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            4 => (object) [
                'name'       => 'type_smallint',
                'type'       => 'smallint',
                'max_length' => '16',
                'nullable'   => true,
                'default'    => null,
            ],
            5 => (object) [
                'name'       => 'type_integer',
                'type'       => 'integer',
                'max_length' => '32',
                'nullable'   => true,
                'default'    => null,
            ],
            6 => (object) [
                'name'       => 'type_float',
                'type'       => 'double precision',
                'max_length' => '53',
                'nullable'   => true,
                'default'    => null,
            ],
            7 => (object) [
                'name'       => 'type_numeric',
                'type'       => 'numeric',
                'max_length' => '18',
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
                'type'       => 'time without time zone',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            10 => (object) [
                'name'       => 'type_datetime',
                'type'       => 'timestamp without time zone',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            11 => (object) [
                'name'       => 'type_timestamp',
                'type'       => 'timestamp without time zone',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
            12 => (object) [
                'name'       => 'type_bigint',
                'type'       => 'bigint',
                'max_length' => '64',
                'nullable'   => true,
                'default'    => null,
            ],
            13 => (object) [
                'name'       => 'type_real',
                'type'       => 'real',
                'max_length' => '24',
                'nullable'   => true,
                'default'    => null,
            ],
            14 => (object) [
                'name'       => 'type_decimal',
                'type'       => 'numeric',
                'max_length' => '18',
                'nullable'   => true,
                'default'    => null,
            ],
            15 => (object) [
                'name'       => 'type_boolean',
                'type'       => 'boolean',
                'max_length' => null,
                'nullable'   => true,
                'default'    => null,
            ],
        ];
        $this->assertSameFieldData($expected, $fields);
    }
}
