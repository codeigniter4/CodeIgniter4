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

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use stdclass;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class SclubricantsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * @var Forge|mixed
     */
    public $forge;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupTable();
    }

    public function setupTable()
    {
        $this->forge = Database::forge($this->DBGroup);

        $this->forge->dropTable('users', true);

        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'country'     => ['type' => 'VARCHAR', 'constraint' => 40],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
            'last_loggin' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('id', true)->addUniqueKey('email')->addKey('country')->createTable('users', true);

        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'userid'     => ['type' => 'INTEGER', 'constraint' => 11],
            'page'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('id', true)->createTable('pageviews', true);

        $data = [
            'pageviews' => [
                [
                    'userid'     => 1,
                    'page'       => 'Home',
                    'created_at' => '2022-01-01 13:21:01',
                ],
                [
                    'userid'     => 1,
                    'page'       => 'Account',
                    'created_at' => '2022-01-01 13:54:26',
                ],
                [
                    'userid'     => 2,
                    'page'       => 'Home',
                    'created_at' => '2022-02-11 07:34:18',
                ],
                [
                    'userid'     => 2,
                    'page'       => 'Profile',
                    'created_at' => '2022-02-11 07:37:21',
                ],
                [
                    'userid'     => 2,
                    'page'       => 'Invoices',
                    'created_at' => '2022-02-11 07:39:48',
                ],
                [
                    'userid'     => 3,
                    'page'       => 'Home',
                    'created_at' => '2022-02-21 11:43:55',
                ],
                [
                    'userid'     => 1,
                    'page'       => 'Profile',
                    'created_at' => '2022-03-01 03:27:41',
                ],
            ],
            'users' => [
                [
                    'name'    => 'Derek Jones users',
                    'email'   => 'derek@world.com',
                    'country' => 'France',
                ],
                [
                    'name'    => 'Ahmadinejad users',
                    'email'   => 'ahmadinejad@world.com',
                    'country' => 'Greece',
                ],
                [
                    'name'    => 'Richard A Causey users',
                    'email'   => 'richard@world.com',
                    'country' => 'France',
                ],
                [
                    'name'    => 'Chris Martin users',
                    'email'   => 'chris@world.com',
                    'country' => 'Greece',
                ],
                [
                    'name'    => 'New User users',
                    'email'   => 'newuser@example.com',
                    'country' => 'US',
                ],
                [
                    'name'    => 'New User2 users',
                    'email'   => 'newuser2@example.com',
                    'country' => 'US',
                ],
            ],
        ];

        foreach ($data as $table => $dummyData) {
            $this->db->table($table)->truncate();

            foreach ($dummyData as $singleDummyData) {
                $this->db->table($table)->insert($singleDummyData);
            }
        }
    }

    public function testUpdateBatchUpdateFieldsAndAlias()
    {
        if ($this->db->DBDriver === 'SQLite3' && ! (version_compare($this->db->getVersion(), '3.33.0') >= 0)) {
            $this->markTestSkipped('Only SQLite 3.33 and newer can complete this test.');
        }

        $data = [
            [
                'email'   => 'derek@world.com',
                'name'    => 'Derek Jones Does Not Change',
                'country' => 'Greece',
            ],
            [

                'email'   => 'ahmadinejad@world.com',
                'name'    => 'Ahmadinejad No change',
                'country' => 'Greece',
            ],
        ];

        $rawSql = new RawSql('CURRENT_TIMESTAMP');

        $updateFields = ['country', 'updated_at' => $rawSql];

        $this->db->table('user')->updateFields($updateFields)->onConstraint('email')->updateBatch($data);

        // check to see if update_at was updated
        $result = $this->db->table('user')
            ->where("email IN ('derek@world.com','ahmadinejad@world.com')")
            ->get()
            ->getResultArray();

        foreach ($result as $row) {
            $this->assertNotNull($row['updated_at']);
        }

        // only country and update_at should have changed
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'country' => 'Greece']);
        $this->seeInDatabase('user', ['name' => 'Ahmadinejad', 'country' => 'Greece']);

        // Original dataset from seeder
        $data = [
            [
                'name'    => 'Derek Should Change',
                'email'   => 'derek@world.com',
                'country' => 'Greece', // will update
            ],
            [
                'name'    => 'Ahmadinejad', // did't change above and will not change
                'email'   => 'ahmadinejad@world.com',
                'country' => 'Iran', // will not update
            ],
            [
                'name'    => 'Should Not Change',
                'email'   => 'richard@world.com',
                'country' => 'Greece', // will not update
            ],
            [
                'name'    => 'Should Change',
                'email'   => 'chris@world.com',
                'country' => 'UK', // will update
            ],
        ];

        $updateFields = ['name', 'updated_at' => new RawSql('NULL')];

        $esc = $this->db->escapeChar;

        // contraint is email and if the updated country = the source country
        // setting alias allows us to reference it in RawSql
        $this->db->table('user')
            ->updateFields($updateFields)
            ->onConstraint(['email', new RawSql("{$esc}db_user{$esc}.{$esc}country{$esc} = {$esc}_update{$esc}.{$esc}country{$esc}")])
            ->setAlias('_update')
            ->updateBatch($data);

        $result = $this->db->table('user')->get()->getResultArray();

        foreach ($result as $row) {
            if ($row['email'] === 'ahmadinejad@world.com') {
                $this->assertNotNull($row['updated_at']);
            } else {
                $this->assertNull($row['updated_at']);
            }
        }

        $this->seeInDatabase('user', ['name' => 'Derek Should Change', 'country' => 'Greece']);
        $this->seeInDatabase('user', ['name' => 'Ahmadinejad', 'country' => 'Greece']);
        $this->seeInDatabase('user', ['name' => 'Richard A Causey', 'country' => 'US']);
        $this->seeInDatabase('user', ['name' => 'Should Change', 'country' => 'UK']);
    }

    public function testUpdateBatchWithoutOnConstraint()
    {
        if ($this->db->DBDriver === 'SQLite3' && ! (version_compare($this->db->getVersion(), '3.33.0') >= 0)) {
            $this->markTestSkipped('Only SQLite 3.33 and newer can complete this test.');
        }

        $data = [
            [
                'name'    => 'Derek Nothing', // won't update
                'email'   => 'derek@world.com',
                'country' => 'Canada',
            ],
            [
                'name'    => 'Ahmadinejad',
                'email'   => 'ahmadinejad@world.com',
                'country' => 'Canada',
            ],
            [
                'name'    => 'Richard A Causey',
                'email'   => 'richard@world.com',
                'country' => 'Canada',
            ],
            [
                'name'    => 'Chris Martin',
                'email'   => 'chris@world.com',
                'country' => 'Canada',
            ],
        ];

        $this->db->table('user')->updateBatch($data, 'email, name', 2);

        $result = $this->db->table('user')->get()->getResultArray();

        foreach ($result as $row) {
            if ($row['email'] === 'derek@world.com') {
                $this->assertSame('US', $row['country']);
            } else {
                $this->assertSame('Canada', $row['country']);
            }
        }
    }

    public function testUpsertNoData()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No data availble to process.');

        $this->db->table('user')->onConstraint('email')->upsertBatch();
    }

    public function testUpdateWithQuery()
    {
        if ($this->db->DBDriver === 'SQLite3' && ! (version_compare($this->db->getVersion(), '3.33.0') >= 0)) {
            $this->markTestSkipped('Only SQLite 3.33 and newer can complete this test.');
        }

        $this->setupTable();

        $updateFields = ['country', 'updated_at' => new RawSql('CURRENT_TIMESTAMP')];

        $subQuery = $this->db->table('users')->select('email, country')->where('country', 'France');

        $affectedRows = $this->db->table('user')->updateFields($updateFields, true)->updateBatch($subQuery, 'email');

        $this->assertSame(2, (int) $affectedRows);

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'country' => 'France']);
        $this->seeInDatabase('user', ['name' => 'Ahmadinejad', 'country' => 'Iran']);
        $this->seeInDatabase('user', ['name' => 'Richard A Causey', 'country' => 'France']);
        $this->seeInDatabase('user', ['name' => 'Chris Martin', 'country' => 'UK']);

        $result = $this->db->table('user')->get()->getResultArray();

        foreach ($result as $row) {
            if ($row['email'] === 'richard@world.com' || $row['email'] === 'derek@world.com') {
                $this->assertNotNull($row['updated_at']);
            } else {
                $this->assertNull($row['updated_at']);
            }
        }
    }

    public function testUpsertWithQuery()
    {
        $this->setupTable();

        $rawSql = new RawSql('CURRENT_TIMESTAMP');

        $updateFields = ['updated_at' => $rawSql];

        $subQuery = $this->db->table('users')->select('email, name, country');

        $this->db->table('user')->updateFields($updateFields, true)->onConstraint('email')->upsertBatch($subQuery);

        $this->seeInDatabase('user', ['name' => 'Derek Jones users', 'email' => 'derek@world.com']);
        $this->seeInDatabase('user', ['name' => 'New User users', 'email' => 'newuser@example.com']);

        $result = $this->db->table('user')->get()->getResultArray();

        foreach ($result as $row) {
            if ($row['email'] === 'newuser@example.com' || $row['email'] === 'newuser2@example.com') {
                $this->assertNull($row['updated_at']);
            } else {
                $this->assertNotNull($row['updated_at']);
            }
        }
    }

    public function testInsertWithQuery()
    {
        $this->setupTable();

        $subQuery = $this->db->table('users')->select('email, name, country');

        $this->db->table('user')->ignore(true)->insertBatch($subQuery);

        $this->seeInDatabase('user', ['name' => 'New User users']);
        $this->seeInDatabase('user', ['name' => 'New User2 users']);

        $this->db->table('user')->get()->getResultArray();
    }

    public function testSetBatchOneRow()
    {
        $data = [
            [
                'name'    => 'Derek Jones users',
                'email'   => 'derek@world.com',
                'country' => 'Netherlands',
            ],
            [
                'name'    => 'Ahmadinejad users',
                'email'   => 'ahmadinejad@world.com',
                'country' => 'Netherlands',
            ],
            [
                'name'    => 'Richard A Causey users',
                'email'   => 'richard@world.com',
                'country' => 'Netherlands',
            ],
            [
                'name'    => 'Chris Martin users',
                'email'   => 'chris@world.com',
                'country' => 'Netherlands',
            ],
            [
                'name'    => 'New User users',
                'email'   => 'newuser@example.com',
                'country' => 'Netherlands',
            ],
        ];

        $builder = $this->db->table('user');

        // set batch row at a time
        foreach ($data as $moreData) {
            $builder->setBatch($moreData);
        }

        $evenMoreData          = new stdclass();
        $evenMoreData->name    = 'New User2 users';
        $evenMoreData->email   = 'newuser2@example.com';
        $evenMoreData->country = 'Netherlands';

        $builder->onConstraint('email')->upsertBatch($evenMoreData, true, 2);

        $result = $this->db->table('user')->get()->getResultArray();

        foreach ($result as $row) {
            $this->assertSame('Netherlands', $row['country']);
        }
    }

    public function testRawSqlConstraint()
    {
        if ($this->db->DBDriver === 'SQLite3' && ! (version_compare($this->db->getVersion(), '3.33.0') >= 0)) {
            $this->markTestSkipped('Only SQLite 3.33 and newer can complete this test.');
        }

        $data = [
            [
                'name'    => 'Derek Jones',
                'email'   => 'derek@world.com',
                'country' => 'Germany',
            ],
        ];

        $builder = $this->db->table('user');

        $builder->setBatch($data, true, 'db_myalias')->updateFields('name, country')->onConstraint(new RawSql($this->db->protectIdentifiers('user.email') . ' = ' . $this->db->protectIdentifiers('myalias.email')))->updateBatch();

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'country' => 'Germany']);
    }
}
