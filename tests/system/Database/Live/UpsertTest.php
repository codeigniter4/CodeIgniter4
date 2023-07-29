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
final class UpsertTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * @var Forge
     */
    public $forge;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testUpsertOnUniqueIndex(): void
    {
        $userData = [
            'email'   => 'upsertone@test.com',
            'name'    => 'Upsert One',
            'country' => 'US',
        ];

        $this->db->table('user')->upsert($userData);

        $this->seeInDatabase('user', ['name' => 'Upsert One']);
    }

    public function testUpsertAndUpsertBatchWithObject(): void
    {
        $data = [];

        // new row insert
        $row          = new stdclass();
        $row->name    = 'Pedro';
        $row->email   = 'pedro@acme.com';
        $row->country = 'El Salvador';
        $data[]       = $row;

        // no change
        $row          = new stdclass();
        $row->name    = 'Ahmadinejad';
        $row->email   = 'ahmadinejad@world.com';
        $row->country = 'Iran';
        $data[]       = $row;

        // changed country for update
        $row          = new stdclass();
        $row->name    = 'Derek Jones';
        $row->email   = 'derek@world.com';
        $row->country = 'Canada';
        $data[]       = $row;

        $affectedRows1 = $this->db->table('user')->upsertBatch($data);

        $this->seeInDatabase('user', ['name' => 'Pedro']);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'country' => 'Canada']);

        $row->country  = 'Spain';
        $affectedRows2 = $this->db->table('user')->upsert($row);

        // a second upsert that does not affect rows
        $affectedRows3 = $this->db->table('user')->upsert($row);

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'country' => 'Spain']);

        switch ($this->db->DBDriver) {
            case 'MySQLi':
                // mysqli counts 2 for update
                $this->assertSame(3, $affectedRows1);
                // mysqli counts 2 for update
                $this->assertSame(2, $affectedRows2);
                $this->assertSame(0, $affectedRows3);
                break;

            case 'Postgre':
            case 'SQLite3':
            case 'SQLSRV':
            case 'OCI8':
                // postgre, sqlite, sqlsrv, oracle - counts row with no change
                $this->assertSame(3, $affectedRows1);
                $this->assertSame(1, $affectedRows2);
                // postgre, sqlite, sqlsrv, oracle - counts row with no change
                $this->assertSame(1, $affectedRows3);
                break;

            default:
                // one insert + one update
                $this->assertSame(2, $affectedRows1);
                // one update
                $this->assertSame(1, $affectedRows2);
                // no change
                $this->assertSame(0, $affectedRows3);
                break;
        }
    }

    public function testUpsertChangePrimaryKeyOnUniqueIndex(): void
    {
        $userData = [
            'id'      => 5,
            'email'   => 'ahmadinejad@world.com',
            'name'    => 'Ahmadinejad',
            'country' => 'Iran',
        ];

        // some databases don't allow update of auto incrememnt
        if ($this->db->DBDriver === 'SQLSRV') {
            $this->markTestSkipped('SQLSRV does not support this test.');
        } else {
            $this->db->table('user')
                ->onConstraint('email')
                ->updateFields('id, name, country')
                ->upsert($userData);

            $new = $this->db->table('user')
                ->getwhere(['email' => 'ahmadinejad@world.com'])
                ->getRow();

            $this->assertSame(5, (int) $new->id);
        }
    }

    public function testNoConstraintFound(): void
    {
        $jobData = [
            'name'        => 'Programmer',
            'description' => 'General PHP Coding',
        ];

        // MySQL doesn't require a named constraint
        if ($this->db->DBDriver === 'MySQLi') {
            $this->markTestSkipped('MySql is not compatible with this test.');
        } else {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('No constraint found for upsert.');

            $this->db->table('job')
                ->upsert($jobData);
        }
    }

    public function testGetCompiledUpsert(): void
    {
        switch ($this->db->DBDriver) {
            case 'MySQLi':
                $expected = <<<'SQL'
                    INSERT INTO `db_user` (`country`, `email`, `name`)
                    VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
                    ON DUPLICATE KEY UPDATE
                    `db_user`.`country` = VALUES(`country`),
                    `db_user`.`email` = VALUES(`email`),
                    `db_user`.`name` = VALUES(`name`)
                    SQL;
                break;

            case 'Postgre':
                $expected = <<<'SQL'
                    INSERT INTO "db_user" ("country", "email", "name")
                    VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
                    ON CONFLICT("email")
                    DO UPDATE SET
                    "country" = "excluded"."country",
                    "name" = "excluded"."name"
                    SQL;
                break;

            case 'SQLite3':
                $expected = <<<'SQL'
                    INSERT INTO `db_user` (`country`, `email`, `name`)
                    VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
                    ON CONFLICT(`email`)
                    DO UPDATE SET
                    `country` = `excluded`.`country`,
                    `name` = `excluded`.`name`
                    SQL;
                break;

            case 'SQLSRV':
                $expected = <<<'SQL'
                    MERGE INTO "test"."dbo"."db_user"
                    USING (
                    VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
                    ) "_upsert" ("country", "email", "name")
                    ON ("test"."dbo"."db_user"."email" = "_upsert"."email")
                    WHEN MATCHED THEN UPDATE SET
                    "country" = "_upsert"."country",
                    "name" = "_upsert"."name"
                    WHEN NOT MATCHED THEN INSERT ("country", "email", "name")
                    VALUES ("_upsert"."country", "_upsert"."email", "_upsert"."name");
                    SQL;
                break;

            case 'OCI8':
                $expected = <<<'SQL'
                    MERGE INTO "db_user"
                    USING (
                    SELECT 'Iran' "country", 'ahmadinejad@world.com' "email", 'Ahmadinejad' "name" FROM DUAL
                    ) "_upsert"
                    ON ("db_user"."email" = "_upsert"."email")
                    WHEN MATCHED THEN UPDATE SET
                    "country" = "_upsert"."country",
                    "name" = "_upsert"."name"
                    WHEN NOT MATCHED THEN INSERT ("country", "email", "name")
                    VALUES  ("_upsert"."country", "_upsert"."email", "_upsert"."name")
                    SQL;
                break;

            default:
                $expected = false;
                break;
        }

        $userData = [
            'email'   => 'ahmadinejad@world.com',
            'name'    => 'Ahmadinejad',
            'country' => 'Iran',
        ];

        $this->assertSame($expected, $this->db->table('user')
            ->set($userData)
            ->getCompiledUpsert());
    }

    public function testGetCompiledUpsertBatch(): void
    {
        $userData = [
            [
                'email'   => 'ahmadinejad@example.com',
                'name'    => 'Ahmadinejad',
                'country' => 'Iran',
            ],
            [
                'email'   => 'pedro@example.com',
                'name'    => 'Pedro',
                'country' => 'El Salvador',
            ],
        ];

        $sql = $this->db->table('user')
            ->setData($userData)
            ->onConstraint('email')
            ->getCompiledUpsert();

        $this->assertStringContainsString('ahmadinejad@example.com', $sql);
        $this->assertStringContainsString('pedro@example.com', $sql);

        $insertString = 'INSERT INTO '
            . $this->db->protectIdentifiers('db_user')
            . ' (' . $this->db->protectIdentifiers('country')
            . ', ' . $this->db->protectIdentifiers('email')
            . ', ' . $this->db->protectIdentifiers('name') . ')';

        if ($this->db->DBDriver === 'SQLSRV' || $this->db->DBDriver === 'OCI8') {
            $insertString = 'INSERT ("country", "email", "name")';
        }

        $this->assertStringContainsString($insertString, $sql);
    }

    public function testUpsertCauseConstraintError(): void
    {
        $userData = [
            'id'      => 1,
            'email'   => 'ahmadinejad@world.com',
            'name'    => 'Ahmadinejad',
            'country' => 'Iran',
        ];

        $this->expectException(DatabaseException::class);

        $esc = $this->db->escapeChar;

        $this->db->table('user')
            ->onConstraint(new RawSql($esc . 'user' . $esc . '.' . $esc . 'id' . $esc))
            ->updateFields('name, email, country')
            ->upsert($userData);
    }

    public function testUpsertBatchOnUniqueIndex(): void
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

    public function testSetDataUpsertBatch(): void
    {
        $userData = [
            [
                'email'   => 'upsertone@test.com',
                'name'    => 'Upsert One New Name',
                'country' => 'US',
            ],
            [
                'email'   => 'ahmadinejad@world.com',
                'name'    => 'Ahmadinejad',
                'country' => 'Iran',
            ],
            [
                'email'   => 'upsertthree@test.com',
                'name'    => 'Upsert Three',
                'country' => 'US',
            ],
        ];

        $this->db->table('user')->setData($userData)->upsertBatch(null, true, 2);

        $this->seeInDatabase('user', ['name' => 'Upsert One New Name']);
        $this->seeInDatabase('user', ['name' => 'Ahmadinejad', 'email' => 'ahmadinejad@world.com', 'country' => 'Iran']);
        $this->seeInDatabase('user', ['name' => 'Upsert Three']);
    }

    public function testUpsertBatchWithOnConflictAndUpdateFields(): void
    {
        $userData = [
            [
                'email'   => 'derek@world.com',
                'name'    => 'Derek Jones Doesnt Change',
                'country' => 'Updated To Canada',
            ],
            [
                'email'   => 'ahmadinejad@world.com',
                'name'    => 'Ahmadinejad Doesnt Change',
                'country' => 'Updated To Mexico',
            ],
            [
                'email'   => 'pedro@acme.com',
                'name'    => 'Pedro Is Inserted',
                'country' => 'El Salvador',
            ],
        ];

        // only update country
        $this->db->table('user')
            ->onConstraint('email')
            ->updateFields('country')
            ->upsertBatch($userData);

        $data = $this->db->table('user')
            ->orderBy('id', 'asc')
            ->get()
            ->getResultObject();

        $this->assertSame('Derek Jones', $data[0]->name);
        $this->assertSame('Updated To Canada', $data[0]->country);

        $this->assertSame('Ahmadinejad', $data[1]->name);
        $this->assertSame('Updated To Mexico', $data[1]->country);

        $this->assertSame('Pedro Is Inserted', $data[4]->name);
        $this->assertSame('El Salvador', $data[4]->country);
    }

    public function testUpsertWithMatchingDataOnUniqueIndexandPrimaryKey(): void
    {
        $data = [
            'id'      => 6,
            'email'   => 'someone@something.com',
            'name'    => 'Some Name',
            'country' => 'US',
        ];

        $this->db->table('user')->upsert($data);

        $original = $this->db->table('user')
            ->getwhere(['id' => 6])
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
            ->getwhere(['id' => 6])
            ->getRow();

        $this->assertSame('Random Name 356', $row->name);
        $this->assertNotSame($original->name, $row->name);
    }

    public function testUpsertBatchOnPrimaryKey(): void
    {
        $userData = [
            [
                'id'      => 1,
                'email'   => 'upsertone@domain.com',
                'name'    => 'Upsert One On Id',
                'country' => 'US',
            ],
            [
                'id'      => 2,
                'email'   => 'upserttwo@domain.com',
                'name'    => 'Upsert Two On Id',
                'country' => 'US',
            ],
            [
                'id'      => 3,
                'email'   => 'upsertthree@domain.com',
                'name'    => 'Upsert Three On Id',
                'country' => 'US',
            ],
        ];

        $this->db->table('user')->upsertBatch($userData);

        // get by id
        $row1 = $this->db->table('user')
            ->getwhere(['id' => 1])
            ->getRow();

        // get by id
        $row2 = $this->db->table('user')
            ->getwhere(['id' => 2])
            ->getRow();

        // get by id
        $row3 = $this->db->table('user')
            ->getwhere(['id' => 3])
            ->getRow();

        $this->assertSame('Upsert One On Id', $row1->name);
        $this->assertSame('Upsert Two On Id', $row2->name);
        $this->assertSame('Upsert Three On Id', $row3->name);
    }

    public function testUpsertBatchOnNullAutoIncrement(): void
    {
        $userData = [
            [
                'id'      => null,
                'email'   => 'nullone@domain.com',
                'name'    => 'Null One',
                'country' => 'US',
            ],
            [
                'id'      => null,
                'email'   => 'nulltwo@domain.com',
                'name'    => 'Null Two',
                'country' => 'US',
            ],
            [
                'id'      => null,
                'email'   => 'nullthree@domain.com',
                'name'    => 'Null Three',
                'country' => 'US',
            ],
        ];

        $this->db->table('user')->upsertBatch($userData);

        // get by id
        $row1 = $this->db->table('user')
            ->getwhere(['email' => 'nullone@domain.com'])
            ->getRow();

        // get by id
        $row2 = $this->db->table('user')
            ->getwhere(['email' => 'nulltwo@domain.com'])
            ->getRow();

        // get by id
        $row3 = $this->db->table('user')
            ->getwhere(['email' => 'nullthree@domain.com'])
            ->getRow();

        $this->assertSame('Null One', $row1->name);
        $this->assertSame('Null Two', $row2->name);
        $this->assertSame('Null Three', $row3->name);
    }

    public function testUpsertBatchMultipleConstraints(): void
    {
        $data = [
            [
                'id'      => 1,
                'email'   => 'derek@world.com',
                'name'    => 'Derek Jones',
                'country' => 'Greece',
            ],
            [
                'id'      => 2,
                'email'   => 'ahmadinejad@world.com',
                'name'    => 'Ahmadinejad2',
                'country' => 'Greece',
            ],
            [
                'id'      => 3,
                'name'    => 'Richard A Causey',
                'email'   => 'richard@world.com',
                'country' => 'Greece',
            ],
        ];

        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 is not compatible with this test.');
        } elseif ($this->db->DBDriver === 'Postgre') {
            $this->markTestSkipped('Postgre is not compatible with this test.');
        } else {
            $this->db->table('user')->onConstraint('id, email')->upsertBatch($data);

            $this->seeInDatabase('user', ['id' => 1, 'country' => 'Greece']);
            $this->seeInDatabase('user', ['id' => 2, 'country' => 'Greece']);
            $this->seeInDatabase('user', ['id' => 3, 'country' => 'Greece']);
        }
    }

    public function testSetBatchOneRow(): void
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
            $builder->setData($moreData);
        }

        $evenMoreData          = new stdclass();
        $evenMoreData->name    = 'New User2 users';
        $evenMoreData->email   = 'newuser2@example.com';
        $evenMoreData->country = 'Netherlands';

        $rawSql       = new RawSql('CURRENT_TIMESTAMP');
        $updateFields = ['updated_at' => $rawSql];

        // set additional field updated_at
        $builder->updateFields($updateFields, true)->onConstraint('email')->upsertBatch($evenMoreData, true, 2);

        $result = $this->db->table('user')->get()->getResultArray();

        foreach ($result as $row) {
            $this->assertSame('Netherlands', $row['country']);

            if ($row['email'] !== 'newuser@example.com' && in_array($row['email'], array_column($data, 'email'), true)) {
                $this->assertNotNull($row['updated_at']);
            } else {
                $this->assertNull($row['updated_at']);
            }
        }
    }

    public function testUpsertNoData(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('upsertBatch() has no data.');

        $this->db->table('user')->onConstraint('email')->upsertBatch();
    }

    public function testUpsertWithMultipleSet(): void
    {
        $builder = $this->db->table('user');

        $ts = "DATE('2022-10-01 12:00:00')";
        if ($this->db->DBDriver === 'OCI8') {
            $ts = "to_char(TO_DATE('2022-10-01 12:00:00','yyyy/mm/dd hh24:mi:ss'), 'yyyy-mm-dd')";
        } elseif ($this->db->DBDriver === 'SQLSRV') {
            $ts = "CAST('2022-10-01 12:00:00' AS date)";
        }

        $builder->set('email', 'jarvis@example.com');
        $builder->set('name', 'Jarvis');
        $builder->set('country', $ts, false);
        $builder->upsert();

        $dt = '2022-10-01';

        $this->seeInDatabase('user', ['email' => 'jarvis@example.com', 'name' => 'Jarvis', 'country' => $dt]);
    }

    public function testUpsertWithTestModeAndGetCompiledUpsert(): void
    {
        $userData = [
            'email'   => 'upsertone@test.com',
            'name'    => 'Upsert One',
            'country' => 'US',
        ];
        $builder = $this->db->table('user');
        $builder->testMode()->upsert($userData);
        $sql = $builder->getCompiledUpsert();

        $this->assertStringContainsString('upsertone@test.com', $sql);
    }

    public function testUpsertBatchWithQuery(): void
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

        $rawSql = new RawSql('CURRENT_TIMESTAMP');

        $updateFields = ['updated_at' => $rawSql];

        $subQuery = $this->db->table('user2')->select('email, name, country');

        $this->db->table('user')
            ->setQueryAsData($subQuery)
            ->updateFields($updateFields, true)
            ->onConstraint('email')
            ->upsertBatch();

        $this->seeInDatabase('user', ['name' => 'Derek Jones user2', 'email' => 'derek@world.com']);
        $this->seeInDatabase('user', ['name' => 'New User user2', 'email' => 'newuser@example.com']);

        $result = $this->db->table('user')->get()->getResultArray();

        foreach ($result as $row) {
            if ($row['email'] === 'newuser@example.com' || $row['email'] === 'newuser2@example.com') {
                $this->assertNull($row['updated_at']);
            } else {
                $this->assertNotNull($row['updated_at']);
            }
        }

        $this->forge->dropTable('user2', true);
    }
}
