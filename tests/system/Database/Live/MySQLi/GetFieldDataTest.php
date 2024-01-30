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

use CodeIgniter\Database\Live\AbstractGetFieldDataTest;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class GetFieldDataTest extends AbstractGetFieldDataTest
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
            && strpos($this->db->getVersion(), 'MariaDB') === false
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
}
