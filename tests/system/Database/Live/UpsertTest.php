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
final class UpsertTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testUpsertOnUniqueIndex()
    {
        $userData = [
            'email'   => 'upsertone@test.com',
            'name'    => 'Upsert One',
            'country' => 'US',
        ];

        $this->db->table('user')->upsert($userData);

        $this->seeInDatabase('user', ['name' => 'Upsert One']);
    }

    public function testUpsertBatchOnUniqueIndex()
    {
        $userData = [
            [
                'email'   => 'upsertone@test.com',
                'name'    => 'Upsert One New Name',
                'country' => 'US',
            ],
            [
                'email'   => 'upserttwo@test.com',
                'name'    => 'Upsert Two',
                'country' => 'US',
            ],
            [
                'email'   => 'upsertthree@test.com',
                'name'    => 'Upsert Three',
                'country' => 'US',
            ],
        ];

        $this->db->table('user')->upsertBatch($userData);

        $this->seeInDatabase('user', ['name' => 'Upsert One New Name']);
        $this->seeInDatabase('user', ['name' => 'Upsert Two']);
        $this->seeInDatabase('user', ['name' => 'Upsert Three']);
    }

    public function testUpsertWithMatchingDataOnUniqueIndexandPrimaryKey()
    {
        $data = [
            'id'      => 5,
            'email'   => 'someone@something.com',
            'name'    => 'Some Name',
            'country' => 'US',
        ];

        $this->db->table('user')->upsert($data);

        $original = $this->db->table('user')
            ->getwhere(['id' => 5])
            ->getRow();

        $data = [
            'email'   => $original->email,
            'name'    => 'Random Name 356',
            'country' => 'US',
        ];

        // upsert on email
        $this->db->table('user')->upsert($data);

        // get by id
        $row = $this->db->table('user')
            ->getwhere(['id' => 5])
            ->getRow();

        $this->assertSame('Random Name 356', $row->name);
        $this->assertNotSame($original->name, $row->name);
    }

    public function testUpsertBatchOnPrimaryKey()
    {
        $userData = [
            [
                'id'      => 1,
                'email'   => 'upsertone@domain.com',
                'name'    => 'Upsert One On Id',
                'country' => 'US',
            ],
            [
                'id'      => null,
                'email'   => 'upserttwo@domain.com',
                'name'    => 'Upsert Two Id Null',
                'country' => 'US',
            ],
            [
                'id'      => null,
                'email'   => 'upsertthree@domain.com',
                'name'    => 'Upsert Three Id Null',
                'country' => 'US',
            ],
        ];

        // postgre and oracle doesn't support inserting null values on auto increment
        if ($this->db->DBDriver !== 'Postgre' && $this->db->DBDriver !== 'OCI8') {
            $this->db->table('user')->upsertBatch($userData);

            // get by id
            $row = $this->db->table('user')
                ->getwhere(['id' => 1])
                ->getRow();

            $this->assertSame('Upsert One On Id', $row->name);
            $this->seeInDatabase('user', ['name' => 'Upsert Two Id Null']);
            $this->seeInDatabase('user', ['name' => 'Upsert Three Id Null']);
        } else {
            $this->assertTrue(true);
        }
    }
}
