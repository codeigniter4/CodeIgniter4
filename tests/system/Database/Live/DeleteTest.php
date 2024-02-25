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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DeleteTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * @var Forge|mixed
     */
    public $forge;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testDeleteThrowExceptionWithNoCriteria(): void
    {
        $this->expectException(DatabaseException::class);

        $this->db->table('job')->delete();
    }

    public function testDeleteWithExternalWhere(): void
    {
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $this->db->table('job')->where('name', 'Developer')->delete();

        $this->dontSeeInDatabase('job', ['name' => 'Developer']);
    }

    public function testDeleteWithInternalWhere(): void
    {
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $this->db->table('job')->delete(['name' => 'Developer']);

        $this->dontSeeInDatabase('job', ['name' => 'Developer']);
    }

    public function testDeleteWithLimit(): void
    {
        $this->seeNumRecords(2, 'user', ['country' => 'US']);

        try {
            $this->db->table('user')->delete(['country' => 'US'], 1);
        } catch (DatabaseException $e) {
            if (str_contains($e->getMessage(), 'does not allow LIMITs on DELETE queries.')) {
                return;
            }
        }

        $this->seeNumRecords(1, 'user', ['country' => 'US']);
    }

    public function testDeleteBatch(): void
    {
        $data = [
            ['userid' => 1, 'username' => 'Derek J', 'unused' => 'You can have fields you dont use'],
            ['userid' => 2, 'username' => 'Ahmadinejad', 'unused' => 'You can have fields you dont use'],
        ];

        $builder = $this->db->table('user')
            ->setData($data, null, 'data')
            ->onConstraint(['id' => 'userid', 'name' => 'username']);

        // SQLite does not support where for batch deletes
        if ($this->db->DBDriver !== 'SQLite3') {
            $builder->where('data.userid > 0');
        }

        $builder->deleteBatch();

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'name' => 'Derek Jones']);

        $this->dontSeeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'name' => 'Ahmadinejad']);
    }

    public function testDeleteBatchConstraintsDate(): void
    {
        $table = 'type_test';

        // Prepares test data.
        $builder = $this->db->table($table);
        $builder->truncate();

        for ($i = 1; $i < 4; $i++) {
            $builder->insert([
                'type_varchar'  => 'test' . $i,
                'type_char'     => 'char',
                'type_text'     => 'text',
                'type_smallint' => 32767,
                'type_integer'  => 2_147_483_647,
                'type_bigint'   => 9_223_372_036_854_775_807,
                'type_float'    => 10.1,
                'type_numeric'  => 123.23,
                'type_date'     => '2023-12-0' . $i,
                'type_datetime' => '2023-12-21 12:00:00',
            ]);
        }

        $data = [
            ['date' => '2023-12-01', 'unused' => 'You can have fields you dont use'],
            ['date' => '2023-12-02', 'unused' => 'You can have fields you dont use'],
        ];
        $builder = $this->db->table($table)
            ->setData($data, null, 'data')
            ->onConstraint(['type_date' => 'date']);
        $builder->deleteBatch();

        $this->dontSeeInDatabase(
            $table,
            ['type_date' => '2023-12-01', 'type_varchar' => 'test1']
        );
        $this->dontSeeInDatabase(
            $table,
            ['type_date' => '2023-12-02', 'type_varchar' => 'test2']
        );
        $this->seeInDatabase(
            $table,
            ['type_date' => '2023-12-03', 'type_varchar' => 'test3']
        );
    }

    public function testDeleteBatchWithQuery(): void
    {
        $this->forge = Database::forge($this->DBGroup);

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
                'name'    => 'Derek Jones',
                'email'   => 'derek@world.com',
                'country' => 'France',
            ],
            [
                'name'    => 'Ahmadinejad does not match',
                'email'   => 'ahmadinejad@world.com',
                'country' => 'Greece',
            ],
            [
                'name'    => 'Chris Martin',
                'email'   => 'chris@world.com',
                'country' => 'Greece',
            ],
        ];
        $this->db->table('user2')->insertBatch($data);

        $query = $this->db->table('user2')->select('email, name, country')->where('country', 'Greece');

        $builder = $this->db->table('user')->setQueryAsData($query, 'alias');

        if ($this->db->DBDriver === 'SQLite3') {
            $builder->onConstraint('email, name');
        } else {
            $builder->onConstraint('email');
            $builder->where('alias.name = user.name');
        }

        $builder->deleteBatch();

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'email' => 'derek@world.com']);
        $this->seeInDatabase('user', ['name' => 'Ahmadinejad', 'email' => 'ahmadinejad@world.com']);
        $this->dontSeeInDatabase('user', ['name' => 'Chris Martin', 'email' => 'chris@world.com']);

        $this->db->table('user')->get()->getResultArray();

        $this->forge->dropTable('user2', true);
    }
}
