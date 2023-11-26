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

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class InsertTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * @var Forge
     */
    public $forge;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testInsert(): void
    {
        $jobData = [
            'name'        => 'Grocery Sales',
            'description' => 'Discount!',
        ];

        $this->db->table('job')->insert($jobData);

        $this->seeInDatabase('job', ['name' => 'Grocery Sales']);
    }

    public function testInsertBatch(): void
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

    public function testReplaceWithNoMatchingData(): void
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

    public function testReplaceWithMatchingData(): void
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

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6726
     */
    public function testReplaceTwice(): void
    {
        $builder = $this->db->table('job');

        $data = [
            'id'          => 1,
            'name'        => 'John Smith',
            'description' => 'American',
        ];
        $builder->replace($data);

        $row = $this->db->table('job')
            ->getwhere(['id' => 1])
            ->getRow();
        $this->assertSame('John Smith', $row->name);

        $data = [
            'id'          => 2,
            'name'        => 'Hans Schmidt',
            'description' => 'German',
        ];
        $builder->replace($data);

        $row = $this->db->table('job')
            ->getwhere(['id' => 2])
            ->getRow();
        $this->assertSame('Hans Schmidt', $row->name);
    }

    public function testBug302(): void
    {
        $code = "my code \\'CodeIgniter\\Autoloader\\'";

        $this->db->table('misc')->insert([
            'key'   => 'test',
            'value' => $code,
        ]);

        $this->seeInDatabase('misc', ['key' => 'test']);
        $this->seeInDatabase('misc', ['value' => $code]);
    }

    public function testInsertPasswordHash(): void
    {
        $hash = '$2y$10$tNevVVMwW52V2neE3H79a.wp8ZoItrwosk54.Siz5Fbw55X9YIBsW';

        $this->db->table('misc')->insert([
            'key'   => 'password',
            'value' => $hash,
        ]);

        $this->seeInDatabase('misc', ['value' => $hash]);
    }

    public function setupUser2(): void
    {
        $this->forge->dropTable('user2', true);

        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'country'     => ['type' => 'VARCHAR', 'constraint' => 40],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
            'last_loggin' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('id', true)->addUniqueKey('email')->addKey('country')->createTable('user2', true);

        $data = [
            [
                'name'    => 'Derek Jones user2',
                'email'   => 'derek@world.com',
                'country' => 'France',
            ],
            [
                'name'    => 'Ahmadinejad user2',
                'email'   => 'ahmadinejad@world.com',
                'country' => 'Greece',
            ],
            [
                'name'    => 'Richard A Causey user2',
                'email'   => 'richard@world.com',
                'country' => 'France',
            ],
            [
                'name'    => 'Chris Martin user2',
                'email'   => 'chris@world.com',
                'country' => 'Greece',
            ],
            [
                'name'    => 'New User user2',
                'email'   => 'newuser@example.com',
                'country' => 'US',
            ],
            [
                'name'    => 'New User2 user2',
                'email'   => 'newuser2@example.com',
                'country' => 'US',
            ],
        ];
        $this->db->table('user2')->insertBatch($data);
    }

    public function testInsertBatchWithQuery(): void
    {
        $this->forge = Database::forge($this->DBGroup);

        $this->setupUser2();

        $subQuery = $this->db->table('user2')
            ->select('user2.name, user2.email, user2.country')
            ->join('user', 'user.email = user2.email', 'left')
            ->where('user.email IS NULL');

        $this->db->table('user')->setQueryAsData($subQuery)->insertBatch();

        $this->seeInDatabase('user', ['name' => 'New User user2']);
        $this->seeInDatabase('user', ['name' => 'New User2 user2']);

        $this->forge->dropTable('user2', true);
    }

    public function testInsertBatchWithQueryAndRawSqlAndManualColumns(): void
    {
        $this->forge = Database::forge($this->DBGroup);

        $this->setupUser2();

        $sql = $this->db->table('user2')
            ->select('user2.name, user2.email, user2.country')
            ->join('user', 'user.email = user2.email', 'left')
            ->where('user.email IS NULL')
            ->getCompiledSelect();

        $this->db->table('user')
            ->setQueryAsData(new RawSql($sql), null, 'name, email, country')
            ->insertBatch();

        $this->seeInDatabase('user', ['name' => 'New User user2']);
        $this->seeInDatabase('user', ['name' => 'New User2 user2']);

        $this->forge->dropTable('user2', true);
    }
}
