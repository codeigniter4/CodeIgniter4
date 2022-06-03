<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class InsertTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testInsert()
    {
        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Discount!',
        ];

        $this->db->table('job')->insert($jobData);

        $this->seeInDatabase('job', ['name' => 'Grocery Sales']);
    }

    public function testInsertBatch()
    {
        $jobData = [
            [
                'name'        => 'Comedian',
                'description' => 'Theres something in your teeth',
            ],
            [
                'name'        => 'Cab Driver',
                'description' => 'Iam yellow',
            ],
        ];

        $this->db->table('job')->insertBatch($jobData);

        $this->seeInDatabase('job', ['name' => 'Comedian']);
        $this->seeInDatabase('job', ['name' => 'Cab Driver']);
    }

    public function testReplaceWithNoMatchingData()
    {
        $data = [
            'id'          => 5,
            'name'        => 'Cab Driver',
            'description' => 'Iam yellow',
        ];

        $this->db->table('job')->replace($data);

        $row = $this->db->table('job')
            ->getwhere(['id' => 5])
            ->getRow();

        $this->assertSame('Cab Driver', $row->name);
    }

    public function testReplaceWithMatchingData()
    {
        $data = [
            'id'          => 1,
            'name'        => 'Cab Driver',
            'description' => 'Iam yellow',
        ];

        $this->db->table('job')->replace($data);

        $row = $this->db->table('job')
            ->getwhere(['id' => 1])
            ->getRow();

        $this->assertSame('Cab Driver', $row->name);
    }

    public function testBug302()
    {
        $code = "my code \\'CodeIgniter\\Autoloader\\'";

        $this->db->table('misc')->insert([
            'key'   => 'test',
            'value' => $code,
        ]);

        $this->seeInDatabase('misc', ['key' => 'test']);
        $this->seeInDatabase('misc', ['value' => $code]);
    }

    public function testInsertPasswordHash()
    {
        $hash = '$2y$10$tNevVVMwW52V2neE3H79a.wp8ZoItrwosk54.Siz5Fbw55X9YIBsW';

        $this->db->table('misc')->insert([
            'key'   => 'password',
            'value' => $hash,
        ]);

        $this->seeInDatabase('misc', ['value' => $hash]);
    }
}
