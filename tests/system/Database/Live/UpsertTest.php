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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
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

    public function testUpsertAndUpsertBatchWithObject()
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

    public function testUpsertChangePrimaryKeyOnUniqueIndex()
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

    public function testNoConstraintFound()
    {
        $jobData = [
            'name'        => 'Programmer',
            'description' => 'General PHP Coding',
        ];

        // MySQL doesn't require a named constraint
        if ($this->db->DBDriver === 'MySQLi') {
            $this->assertTrue(true);
        } else {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('No constraint found for upsert.');

            $this->db->table('job')
                ->upsert($jobData);
        }
    }

    public function testGetCompiledUpsert()
    {
        switch ($this->db->DBDriver) {

            case 'MySQLi':
                $expected = <<<'SQL'
                    INSERT INTO `db_user` (`country`, `email`, `name`)
                    VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
                    ON DUPLICATE KEY UPDATE
                    `country` = VALUES(`country`),
                    `email` = VALUES(`email`),
                    `name` = VALUES(`name`)
                    SQL;
                break;

            case 'Postgre':
                $expected = <<<'SQL'
                    INSERT INTO "db_user" ("country", "email", "name")
                    VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
                    ON CONFLICT("email")
                    DO UPDATE SET
                    "country" = "excluded"."country",
                    "email" = "excluded"."email",
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
                    `email` = `excluded`.`email`,
                    `name` = `excluded`.`name`
                    SQL;
                break;

            case 'SQLSRV':
                $expected = <<<'SQL'
                    MERGE INTO "test"."dbo"."db_user"
                    USING (
                     VALUES ('Iran','ahmadinejad@world.com','Ahmadinejad')
                    ) "_upsert" ("country", "email", "name")
                    ON ( 1 != 1 OR ("test"."dbo"."db_user"."email" = "_upsert"."email"))
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
                    ON ( 1 != 1 OR ("db_user"."email" = "_upsert"."email"))
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

    public function testUpsertCauseConstraintError()
    {
        $userData = [
            'id'      => 1,
            'email'   => 'ahmadinejad@world.com',
            'name'    => 'Ahmadinejad',
            'country' => 'Iran',
        ];

        $this->expectException(DatabaseException::class);

        $this->db->table('user')
            ->onConstraint('id')
            ->updateFields('name, email, country')
            ->upsert($userData);
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

    public function testSetBatchUpsertBatch()
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

        $this->db->table('user')->setBatch($userData)->upsertBatch(null, true, 2);

        $this->seeInDatabase('user', ['name' => 'Upsert One New Name']);
        $this->seeInDatabase('user', ['name' => 'Ahmadinejad', 'email' => 'ahmadinejad@world.com', 'country' => 'Iran']);
        $this->seeInDatabase('user', ['name' => 'Upsert Three']);
    }

    public function testUpsertBatchWithOnConflictAndUpdateFields()
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

    public function testUpsertWithMatchingDataOnUniqueIndexandPrimaryKey()
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

    public function testUpsertBatchOnNullAutoIncrement()
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
}
