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

class AnotherSeeder extends Seeder
{
    public function run(): void
    {
        $row = [
            'name'    => 'Jerome Lohan',
            'email'   => 'jlo@lohanenterprises.com',
            'country' => 'UK',
        ];

        $this->db->table('user')->insert($row);
    }
}
