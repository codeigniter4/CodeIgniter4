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

        $row          = new stdclass();
        $row->name    = 'Pedro';
        $row->email   = 'pedro@acme.com';
        $row->country = 'El Salvador';
        $data[]       = $row;

        $row          = new stdclass();
        $row->name    = 'Derek Jones';
        $row->email   = 'derek@world.com';
        $row->country = 'Canada';
        $data[]       = $row;

        $this->db->table('user')->upsertBatch($data);

        $this->seeInDatabase('user', ['name' => 'Pedro']);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'country' => 'Canada']);

        $row->country = 'Spain';
        $this->db->table('user')->upsert($row);

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'country' => 'Spain']);
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
            $this->assertTrue(true);
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
