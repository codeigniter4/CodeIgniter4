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

namespace CodeIgniter\Database\Live\MySQLi;

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
        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('This test is only for MySQLi.');
        }

        $this->forge = Database::forge($this->db);
    }

    /**
     * As of MySQL 8.0.17, the display width attribute for integer data types
     * is deprecated and is not reported back anymore.
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/numeric-type-attributes.html
     */
    private function isOldMySQL(): bool
    {
        return ! (
            version_compare($this->db->getVersion(), '8.0.17', '>=')
            && ! str_contains($this->db->getVersion(), 'MariaDB')
        );
    }

    public function testGetFieldDataDefault(): void
    {
        $this->createTableForDefault();

        $fields = $this->db->getFieldData($this->table);

        $expected = [
            (object) [
                'name'        => 'id',
                'type'        => 'int',
                'max_length'  => $this->isOldMySQL() ? 11 : null,
                'nullable'    => false,
                'default'     => null, // The default value is not defined.
                'primary_key' => 1,
            ],
            (object) [
                'name'        => 'text_not_null',
                'type'        => 'varchar',
                'max_length'  => 64,
                'nullable'    => false,
                'default'     => null, // The default value is not defined.
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_null',
                'type'        => 'varchar',
                'max_length'  => 64,
                'nullable'    => true,
                'default'     => null, // The default value is not defined.
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'int_default_0',
                'type'        => 'int',
                'max_length'  => $this->isOldMySQL() ? 11 : null,
                'nullable'    => false,
                'default'     => '0', // int 0
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_default_null',
                'type'        => 'varchar',
                'max_length'  => 64,
                'nullable'    => true,
                'default'     => null, // NULL value
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_default_text_null',
                'type'        => 'varchar',
                'max_length'  => 64,
                'nullable'    => false,
                'default'     => 'null', // string "null"
                'primary_key' => 0,
            ],
            (object) [
                'name'        => 'text_default_abc',
                'type'        => 'varchar',
                'max_length'  => 64,
                'nullable'    => false,
                'default'     => 'abc', // string "abc"
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
                'type'        => 'int',
                'max_length'  => $this->isOldMySQL() ? 20 : null,
                'nullable'    => false,
                'default'     => null,
                'primary_key' => 1,
            ],
            1 => (object) [
                'name'        => 'type_varchar',
                'type'        => 'varchar',
                'max_length'  => 40,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            2 => (object) [
                'name'        => 'type_char',
                'type'        => 'char',
                'max_length'  => 10,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            3 => (object) [
                'name'        => 'type_text',
                'type'        => 'text',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            4 => (object) [
                'name'        => 'type_smallint',
                'type'        => 'smallint',
                'max_length'  => $this->isOldMySQL() ? 6 : null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            5 => (object) [
                'name'        => 'type_integer',
                'type'        => 'int',
                'max_length'  => $this->isOldMySQL() ? 11 : null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            6 => (object) [
                'name'        => 'type_float',
                'type'        => 'float',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            7 => (object) [
                'name'        => 'type_numeric',
                'type'        => 'decimal',
                'max_length'  => 18,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            8 => (object) [
                'name'        => 'type_date',
                'type'        => 'date',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            9 => (object) [
                'name'        => 'type_time',
                'type'        => 'time',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            10 => (object) [
                'name'        => 'type_datetime',
                'type'        => 'datetime',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            11 => (object) [
                'name'        => 'type_timestamp',
                'type'        => 'timestamp',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            12 => (object) [
                'name'        => 'type_bigint',
                'type'        => 'bigint',
                'max_length'  => $this->isOldMySQL() ? 20 : null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            13 => (object) [
                'name'        => 'type_real',
                'type'        => 'double',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            14 => (object) [
                'name'        => 'type_enum',
                'type'        => 'enum',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            15 => (object) [
                'name'        => 'type_set',
                'type'        => 'set',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            16 => (object) [
                'name'        => 'type_mediumtext',
                'type'        => 'mediumtext',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            17 => (object) [
                'name'        => 'type_double',
                'type'        => 'double',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            18 => (object) [
                'name'        => 'type_decimal',
                'type'        => 'decimal',
                'max_length'  => 18,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            19 => (object) [
                'name'        => 'type_blob',
                'type'        => 'blob',
                'max_length'  => null,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
            20 => (object) [
                'name'        => 'type_boolean',
                'type'        => 'tinyint',
                'max_length'  => 1,
                'nullable'    => true,
                'default'     => null,
                'primary_key' => 0,
            ],
        ];
        $this->assertSameFieldData($expected, $fields);
    }
}
