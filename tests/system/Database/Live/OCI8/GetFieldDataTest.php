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
        if ($this->db->DBDriver !== 'OCI8') {
            $this->markTestSkipped('This test is only for OCI8.');
        }

        $this->forge = Database::forge($this->db);
    }

    public function testGetFieldData(): void
    {
        $fields = $this->db->getFieldData('test1');

        $data = [];

        foreach ($fields as $obj) {
            $data[$obj->name] = $obj;
        }

        $idDefault = $data['id']->default;
        $this->assertMatchesRegularExpression('/"ORACLE"."ISEQ\$\$_[0-9]+".nextval/', $idDefault);

        $expected = json_decode(json_encode([
            (object) [
                'name'       => 'id',
                'type'       => 'NUMBER',
                'max_length' => '11',
                'default'    => $idDefault, // The default value is not defined.
                // 'primary_key' => 1,
                'nullable' => false,
            ],
            (object) [
                'name'       => 'text_not_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'default'    => null, // The default value is not defined.
                // 'primary_key' => 0,
                'nullable' => false,
            ],
            (object) [
                'name'       => 'text_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'default'    => null, // The default value is not defined.
                // 'primary_key' => 0,
                'nullable' => true,
            ],
            (object) [
                'name'       => 'int_default_0',
                'type'       => 'NUMBER',
                'max_length' => '11',
                'default'    => '0 ', // int 0
                // 'primary_key' => 0,
                'nullable' => false,
            ],
            (object) [
                'name'       => 'text_default_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'default'    => 'NULL ', // NULL value
                // 'primary_key' => 0,
                'nullable' => true,
            ],
            (object) [
                'name'       => 'text_default_text_null',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'default'    => "'null' ", // string "null"
                // 'primary_key' => 0,
                'nullable' => false,
            ],
            (object) [
                'name'       => 'text_default_abc',
                'type'       => 'VARCHAR2',
                'max_length' => '64',
                'default'    => "'abc' ", // string "abc"
                // 'primary_key' => 0,
                'nullable' => false,
            ],
        ]), true);
        $names = array_column($expected, 'name');
        array_multisort($names, SORT_ASC, $expected);

        $fields = json_decode(json_encode($fields), true);
        $names  = array_column($fields, 'name');
        array_multisort($names, SORT_ASC, $fields);

        $this->assertSame($expected, $fields);
    }
}
