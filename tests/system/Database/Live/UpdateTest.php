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
use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class UpdateTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * @var Forge
     */
    public $forge;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testUpdateSetsAllWithoutWhere(): void
    {
        $this->db->table('user')->update(['name' => 'Bobby']);

        $result = $this->db->table('user')->get()->getResult();

        $this->assertSame('Bobby', $result[0]->name);
        $this->assertSame('Bobby', $result[1]->name);
        $this->assertSame('Bobby', $result[2]->name);
        $this->assertSame('Bobby', $result[3]->name);
    }

    public function testUpdateSetsAllWithoutWhereAndLimit(): void
    {
        try {
            $this->db->table('user')->update(['name' => 'Bobby'], null, 1);

            $result = $this->db->table('user')
                ->orderBy('id', 'asc')
                ->get()
                ->getResult();

            // this is really a bad test - indexes and other things can affect sort order
            if ($this->db->DBDriver === 'SQLSRV') {
                $this->assertSame('Derek Jones', $result[0]->name);
                $this->assertSame('Bobby', $result[1]->name);
            } else {
                $this->assertSame('Bobby', $result[0]->name);
                $this->assertSame('Ahmadinejad', $result[1]->name);
            }

            $this->assertSame('Richard A Causey', $result[2]->name);
            $this->assertSame('Chris Martin', $result[3]->name);
        } catch (DatabaseException) {
            // This DB doesn't support Where and Limit together
            // but we don't want it called a "Risky" test.
            $this->assertTrue(true);
        }
    }

    public function testUpdateWithWhere(): void
    {
        $this->db->table('user')->update(['name' => 'Bobby'], ['country' => 'US']);

        $result = $this->db->table('user')->get()->getResultArray();

        $rows = [];

        foreach ($result as $row) {
            if ($row['name'] === 'Bobby') {
                $rows[] = $row;
            }
        }

        $this->assertCount(2, $rows);
    }

    public function testUpdateWithWhereAndLimit(): void
    {
        try {
            $this->db->table('user')->update(['name' => 'Bobby'], ['country' => 'US'], 1);

            $result = $this->db->table('user')->get()->getResult();

            $this->assertSame('Bobby', $result[0]->name);
            $this->assertSame('Ahmadinejad', $result[1]->name);
            $this->assertSame('Richard A Causey', $result[2]->name);
            $this->assertSame('Chris Martin', $result[3]->name);
        } catch (DatabaseException) {
            // This DB doesn't support Where and Limit together
            // but we don't want it called a "Risky" test.
            $this->assertTrue(true);
        }
    }

    #[DataProvider('provideUpdateBatch')]
    public function testUpdateBatch(string $constraints, array $data, array $expected): void
    {
        $table = 'type_test';

        // Prepares test data.
        $builder = $this->db->table($table);
        $builder->truncate();

        for ($i = 1; $i < 4; $i++) {
            $builder->insert([
                'type_varchar'  => 'test' . $i,
                'type_char'     => 'char' . $i,
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

        $this->db->table($table)->updateBatch($data, $constraints);

        if ($this->db->DBDriver === 'SQLSRV') {
            // We cannot compare `text` and `varchar` with `=`. It causes the error:
            // [Microsoft][ODBC Driver 17 for SQL Server][SQL Server]The data types text and varchar are incompatible in the equal to operator.
            // And data type `text`, `ntext`, `image` are deprecated in SQL Server 2016
            // See https://github.com/codeigniter4/CodeIgniter4/pull/8439#issuecomment-1902535909
            unset($expected[0]['type_text'], $expected[1]['type_text']);
        }

        $this->seeInDatabase($table, $expected[0]);
        $this->seeInDatabase($table, $expected[1]);
    }

    public static function provideUpdateBatch(): iterable
    {
        yield from [
            'constraints varchar' => [
                'type_varchar',
                [
                    [
                        'type_varchar'  => 'test1', // Key
                        'type_char'     => 'updated',
                        'type_text'     => 'updated',
                        'type_smallint' => 9999,
                        'type_integer'  => 9_999_999,
                        'type_bigint'   => 9_999_999,
                        'type_float'    => 99.9,
                        'type_numeric'  => 999999.99,
                        'type_date'     => '2024-01-01',
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                    [
                        'type_varchar'  => 'test2', // Key
                        'type_char'     => 'updated',
                        'type_text'     => 'updated',
                        'type_smallint' => 9999,
                        'type_integer'  => 9_999_999,
                        'type_bigint'   => 9_999_999,
                        'type_float'    => 99.9,
                        'type_numeric'  => 999999.99,
                        'type_date'     => '2024-01-01',
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                ],
                [
                    [
                        'type_varchar'  => 'test1',
                        'type_char'     => 'updated',
                        'type_text'     => 'updated',
                        'type_smallint' => 9999,
                        'type_integer'  => 9_999_999,
                        'type_bigint'   => 9_999_999,
                        'type_numeric'  => 999999.99,
                        'type_date'     => '2024-01-01',
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                    [
                        'type_varchar'  => 'test2',
                        'type_char'     => 'updated',
                        'type_text'     => 'updated',
                        'type_smallint' => 9999,
                        'type_integer'  => 9_999_999,
                        'type_bigint'   => 9_999_999,
                        'type_numeric'  => 999999.99,
                        'type_date'     => '2024-01-01',
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                ],
            ],
            'constraints date' => [
                'type_date',
                [
                    [
                        'type_text'     => 'updated',
                        'type_bigint'   => 9_999_999,
                        'type_date'     => '2023-12-01', // Key
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                    [
                        'type_text'     => 'updated',
                        'type_bigint'   => 9_999_999,
                        'type_date'     => '2023-12-02', // Key
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                ],
                [
                    [
                        'type_varchar'  => 'test1',
                        'type_text'     => 'updated',
                        'type_bigint'   => 9_999_999,
                        'type_date'     => '2023-12-01',
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                    [
                        'type_varchar'  => 'test2',
                        'type_text'     => 'updated',
                        'type_bigint'   => 9_999_999,
                        'type_date'     => '2023-12-02',
                        'type_datetime' => '2024-01-01 09:00:00',
                    ],
                ],
            ],
            'int as string' => [
                'type_varchar',
                [
                    [
                        'type_varchar' => 'test1', // Key
                        'type_integer' => '9999999', // PHP string
                        'type_bigint'  => '2448114396435166946', // PHP string
                    ],
                    [
                        'type_varchar' => 'test2', // Key
                        'type_integer' => '9999999', // PHP string
                        'type_bigint'  => '2448114396435166946', // PHP string
                    ],
                ],
                [
                    [
                        'type_varchar' => 'test1',
                        'type_integer' => 9_999_999,
                        'type_bigint'  => 2_448_114_396_435_166_946,
                    ],
                    [
                        'type_varchar' => 'test2',
                        'type_integer' => 9_999_999,
                        'type_bigint'  => 2_448_114_396_435_166_946,
                    ],
                ],
            ],
        ];
    }

    public function testUpdateWithWhereSameColumn(): void
    {
        $this->db->table('user')->update(['country' => 'CA'], ['country' => 'US']);

        $result = $this->db->table('user')->get()->getResultArray();

        $rows = [];

        foreach ($result as $row) {
            if ($row['country'] === 'CA') {
                $rows[] = $row;
            }
        }

        $this->assertCount(2, $rows);
    }

    public function testUpdateWithWhereSameColumn2(): void
    {
        // calling order: set() -> where()
        $this->db->table('user')
            ->set('country', 'CA')
            ->where('country', 'US')
            ->update();

        $result = $this->db->table('user')->get()->getResultArray();

        $rows = [];

        foreach ($result as $row) {
            if ($row['country'] === 'CA') {
                $rows[] = $row;
            }
        }

        $this->assertCount(2, $rows);
    }

    public function testUpdateWithWhereSameColumn3(): void
    {
        // calling order: where() -> set() in update()
        $this->db->table('user')
            ->where('country', 'US')
            ->update(['country' => 'CA']);

        $result = $this->db->table('user')->get()->getResultArray();

        $rows = [];

        foreach ($result as $row) {
            if ($row['country'] === 'CA') {
                $rows[] = $row;
            }
        }

        $this->assertCount(2, $rows);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/324
     */
    public function testUpdatePeriods(): void
    {
        $this->db->table('misc')
            ->where('key', 'spaces and tabs')
            ->update([
                'value' => '30.192',
            ]);

        $this->seeInDatabase('misc', [
            'value' => '30.192',
        ]);
    }

    /**
     * @see https://codeigniter4.github.io/CodeIgniter4/database/query_builder.html#updating-data
     */
    public function testSetWithoutEscape(): void
    {
        $this->db->table('job')
            ->set('description', $this->db->escapeIdentifiers('name'), false)
            ->update();

        $this->seeInDatabase('job', [
            'name'        => 'Developer',
            'description' => 'Developer',
        ]);
    }

    public function testSetWithBoolean(): void
    {
        $this->db->table('type_test')
            ->set('type_boolean', false)
            ->update();

        $this->seeInDatabase('type_test', [
            'type_boolean' => false,
        ]);

        $this->db->table('type_test')
            ->set('type_boolean', true)
            ->update();

        $this->seeInDatabase('type_test', [
            'type_boolean' => true,
        ]);
    }

    public function testUpdateBatchTwoConstraints(): void
    {
        if (version_compare($this->db->getVersion(), '3.33.0') < 0) {
            $this->markTestSkipped('This SQLite version does not support this test.');
        }

        $data = [
            [
                'id'      => 1,
                'name'    => 'Derek Jones Changes',
                'country' => 'US',
            ],
            [
                'id'      => 2,
                'name'    => 'Ahmadinejad Does Not Change',
                'country' => 'Greece',
            ],
        ];

        $this->db->table('user')->updateBatch($data, 'id, country');

        $this->seeInDatabase('user', [
            'name'    => 'Derek Jones Changes',
            'country' => 'US',
        ]);
        $this->seeInDatabase('user', [
            'name'    => 'Ahmadinejad',
            'country' => 'Iran',
        ]);
    }

    public function testUpdateBatchConstraintsRawSqlAndAlias(): void
    {
        if (version_compare($this->db->getVersion(), '3.33.0') < 0) {
            $this->markTestSkipped('This SQLite version does not support this test.');
        }

        $data = [
            [
                'id'      => 1,
                'name'    => 'Derek Jones Changes',
                'country' => 'US',
            ],
            [
                'id'      => 2,
                'name'    => 'Ahmadinejad Changes',
                'country' => 'Uruguay',
            ],
            [
                'id'      => 3,
                'name'    => 'Richard A Causey Changes',
                'country' => 'US',
            ],
            [
                'id'      => 4,
                'name'    => 'Chris Martin Does Not Change',
                'country' => 'Greece',
            ],
        ];

        $this->db->table('user')->setData($data, true, 'd')->updateBatch(
            null,
            ['id', new RawSql($this->db->protectIdentifiers('d')
            . '.' . $this->db->protectIdentifiers('country')
            . " LIKE 'U%'")],
        );

        $this->seeInDatabase('user', [
            'name'    => 'Derek Jones Changes',
            'country' => 'US',
        ]);
        $this->seeInDatabase('user', [
            'name'    => 'Ahmadinejad Changes',
            'country' => 'Uruguay',
        ]);
        $this->seeInDatabase('user', [
            'name'    => 'Richard A Causey Changes',
            'country' => 'US',
        ]);
        $this->seeInDatabase('user', [
            'name'    => 'Chris Martin',
            'country' => 'UK',
        ]);
    }

    public function testUpdateBatchUpdateFieldsAndAlias(): void
    {
        if ($this->db->DBDriver === 'SQLite3' && version_compare($this->db->getVersion(), '3.33.0') < 0) {
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
            ->setData($data, null, '_update')
            ->updateBatch();

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

    public function testUpdateBatchWithoutOnConstraint(): void
    {
        if ($this->db->DBDriver === 'SQLite3' && version_compare($this->db->getVersion(), '3.33.0') < 0) {
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

    public function testRawSqlConstraint(): void
    {
        if ($this->db->DBDriver === 'SQLite3' && version_compare($this->db->getVersion(), '3.33.0') < 0) {
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

        $builder->setData($data, true, 'myalias')
            ->updateFields('name, country')
            ->onConstraint(new RawSql($this->db->protectIdentifiers('user.email') . ' = ' . $this->db->protectIdentifiers('myalias.email')))
            ->updateBatch();

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'country' => 'Germany']);
    }

    public function testRawSqlConstraintWithKey(): void
    {
        if ($this->db->DBDriver === 'SQLite3' && version_compare($this->db->getVersion(), '3.33.0') < 0) {
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

        $builder->setData($data, true, 'myalias')
            ->updateFields('name, country')
            ->onConstraint(['email' => new RawSql($this->db->protectIdentifiers('myalias.email'))])
            ->updateBatch();

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'country' => 'Germany']);
    }

    public function testNoConstraintFound(): void
    {
        $jobData = [
            'name'        => 'Programmer',
            'description' => 'General PHP Coding',
        ];

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must specify a constraint to match on for batch updates.');

        $this->db->table('job')
            ->updateBatch($jobData);
    }

    public function testUpdateBatchWithQuery(): void
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

        if ($this->db->DBDriver === 'SQLite3' && version_compare($this->db->getVersion(), '3.33.0') < 0) {
            $this->markTestSkipped('Only SQLite 3.33 and newer can complete this test.');
        }

        $updateFields = ['country', 'updated_at' => new RawSql('CURRENT_TIMESTAMP')];

        $subQuery = $this->db->table('user2')
            ->select('email, country')
            ->where('country', 'France');

        $affectedRows = $this->db->table('user')
            ->setQueryAsData($subQuery)
            ->updateFields($updateFields, true)
            ->updateBatch(null, 'email');

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

        $this->forge->dropTable('user2', true);
    }
}
