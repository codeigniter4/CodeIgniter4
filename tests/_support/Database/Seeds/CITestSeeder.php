<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CITestSeeder extends Seeder
{
    public function run(): void
    {
        // Job Data
        $data = [
            'user' => [
                [
                    'name'    => 'Derek Jones',
                    'email'   => 'derek@world.com',
                    'country' => 'US',
                ],
                [
                    'name'    => 'Ahmadinejad',
                    'email'   => 'ahmadinejad@world.com',
                    'country' => 'Iran',
                ],
                [
                    'name'    => 'Richard A Causey',
                    'email'   => 'richard@world.com',
                    'country' => 'US',
                ],
                [
                    'name'    => 'Chris Martin',
                    'email'   => 'chris@world.com',
                    'country' => 'UK',
                ],
            ],
            'job' => [
                [
                    'name'        => 'Developer',
                    'description' => 'Awesome job, but sometimes makes you bored',
                ],
                [
                    'name'        => 'Politician',
                    'description' => 'This is not really a job',
                ],
                [
                    'name'        => 'Accountant',
                    'description' => 'Boring job, but you will get free snack at lunch',
                ],
                [
                    'name'        => 'Musician',
                    'description' => 'Only Coldplay can actually called Musician',
                ],
            ],
            'misc' => [
                [
                    'key'   => '\\xxxfoo456',
                    'value' => 'Entry with \\xxx',
                ],
                [
                    'key'   => '\\%foo456',
                    'value' => 'Entry with \\%',
                ],
                [
                    'key'   => 'spaces and tabs',
                    'value' => ' One  two   three	tab',
                ],
            ],
            'stringifypkey' => [
                [
                    'id'    => 'A01',
                    'value' => 'test',
                ],
            ],
            'without_auto_increment' => [
                [
                    'key'   => 'key',
                    'value' => 'value',
                ],
            ],
            'type_test' => [
                [
                    'type_varchar'    => 'test',
                    'type_char'       => 'test',
                    'type_enum'       => 'appel',
                    'type_set'        => 'one',
                    'type_text'       => 'test text',
                    'type_mediumtext' => 'test medium text',
                    'type_smallint'   => 1,
                    'type_integer'    => 123,
                    'type_float'      => 10.1,
                    'type_real'       => 11.21,
                    'type_double'     => 23.22,
                    'type_decimal'    => 123123.2234,
                    'type_numeric'    => 123.23,
                    'type_blob'       => 'test blob',
                    'type_date'       => '2020-01-11T22:11:00.000+02:00',
                    'type_time'       => '2020-07-18T15:22:00.000+02:00',
                    'type_datetime'   => '2020-06-18T05:12:24.000+02:00',
                    'type_timestamp'  => '2019-07-18T21:53:21.000+02:00',
                    'type_bigint'     => 2342342,
                    'type_boolean'    => 1,
                ],
            ],
        ];

        // set SQL times to more correct format
        if ($this->db->DBDriver === 'SQLite3') {
            $data['type_test'][0]['type_date']      = '2020/01/11';
            $data['type_test'][0]['type_time']      = '15:22:00';
            $data['type_test'][0]['type_datetime']  = '2020/06/18 05:12:24';
            $data['type_test'][0]['type_timestamp'] = '2019/07/18 21:53:21';
        }

        if ($this->db->DBDriver === 'Postgre') {
            $data['type_test'][0]['type_time']    = '15:22:00';
            $data['type_test'][0]['type_boolean'] = true;
            unset(
                $data['type_test'][0]['type_enum'],
                $data['type_test'][0]['type_set'],
                $data['type_test'][0]['type_mediumtext'],
                $data['type_test'][0]['type_real'],
                $data['type_test'][0]['type_double'],
                $data['type_test'][0]['type_decimal'],
                $data['type_test'][0]['type_blob']
            );
        }

        if ($this->db->DBDriver === 'SQLSRV') {
            $data['type_test'][0]['type_date']     = '2020-01-11';
            $data['type_test'][0]['type_time']     = '15:22:00.000';
            $data['type_test'][0]['type_datetime'] = '2020-06-18 05:12:24.000';

            unset(
                $data['type_test'][0]['type_timestamp'],
                $data['type_test'][0]['type_enum'],
                $data['type_test'][0]['type_set'],
                $data['type_test'][0]['type_mediumtext'],
                $data['type_test'][0]['type_double'],
                $data['type_test'][0]['type_blob']
            );
        }

        if ($this->db->DBDriver === 'MySQLi') {
            $data['ci_sessions'][] = [
                'id'         => 'ci_session:1f5o06b43phsnnf8if6bo33b635e4p2o',
                'ip_address' => '127.0.0.1',
                'timestamp'  => '2021-06-25 21:54:14',
                'data'       => '__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";',
            ];
        }

        if ($this->db->DBDriver === 'Postgre') {
            $data['ci_sessions'][] = [
                'id'         => 'ci_session:1f5o06b43phsnnf8if6bo33b635e4p2o',
                'ip_address' => '127.0.0.1',
                'timestamp'  => '2021-06-25 21:54:14.991403+02',
                'data'       => '\x' . bin2hex('__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";'),
            ];
        }

        if ($this->db->DBDriver === 'OCI8') {
            $this->db->query('alter session set NLS_DATE_FORMAT=?', ['YYYY-MM-DD HH24:MI:SS']);
            $data['type_test'][0]['type_time']      = '2020-07-18 15:22:00';
            $data['type_test'][0]['type_date']      = '2020-01-11 22:11:00';
            $data['type_test'][0]['type_time']      = '2020-07-18 15:22:00';
            $data['type_test'][0]['type_datetime']  = '2020-06-18 05:12:24';
            $data['type_test'][0]['type_timestamp'] = '2020-06-18 21:53:21';
            unset($data['type_test'][0]['type_blob']);
        }

        foreach ($data as $table => $dummy_data) {
            $this->db->table($table)->truncate();

            foreach ($dummy_data as $single_dummy_data) {
                $this->db->table($table)->insert($single_dummy_data);
            }
        }
    }
}
