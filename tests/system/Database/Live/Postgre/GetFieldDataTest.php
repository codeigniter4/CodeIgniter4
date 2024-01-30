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
        if ($this->db->DBDriver !== 'Postgre') {
            $this->markTestSkipped('This test is only for Postgre.');
        }

        $this->forge = Database::forge($this->db);
    }

    public function testGetFieldDataDefault(): void
    {
        $this->createTableForDefault();

        $fields = $this->db->getFieldData('test1');

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                (object) [
                    'name'       => 'id',
                    'type'       => 'integer',
                    'max_length' => '32',
                    'default'    => "nextval('db_test1_id_seq'::regclass)", // The default value is not defined.
                    // 'primary_key' => 1,
                    'nullable' => false,
                ],
                (object) [
                    'name'       => 'text_not_null',
                    'type'       => 'character varying',
                    'max_length' => '64',
                    'default'    => null, // The default value is not defined.
                    // 'primary_key' => 0,
                    'nullable' => false,
                ],
                (object) [
                    'name'       => 'text_null',
                    'type'       => 'character varying',
                    'max_length' => '64',
                    'default'    => null, // The default value is not defined.
                    // 'primary_key' => 0,
                    'nullable' => true,
                ],
                (object) [
                    'name'       => 'int_default_0',
                    'type'       => 'integer',
                    'max_length' => '32',
                    'default'    => '0', // int 0
                    // 'primary_key' => 0,
                    'nullable' => false,
                ],
                (object) [
                    'name'       => 'text_default_null',
                    'type'       => 'character varying',
                    'max_length' => '64',
                    'default'    => 'NULL::character varying', // NULL value
                    // 'primary_key' => 0,
                    'nullable' => true,
                ],
                (object) [
                    'name'       => 'text_default_text_null',
                    'type'       => 'character varying',
                    'max_length' => '64',
                    'default'    => "'null'::character varying", // string "null"
                    // 'primary_key' => 0,
                    'nullable' => false,
                ],
                (object) [
                    'name'       => 'text_default_abc',
                    'type'       => 'character varying',
                    'max_length' => '64',
                    'default'    => "'abc'::character varying", // string "abc"
                    // 'primary_key' => 0,
                    'nullable' => false,
                ],
            ]),
            json_encode($fields)
        );
    }
}
