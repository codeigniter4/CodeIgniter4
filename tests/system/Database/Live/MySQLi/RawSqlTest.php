<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\MySQLi;

use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use stdclass;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class RawSqlTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi has its own implementation.');
        } else {
            $this->addSqlFunction();
        }
    }

    protected function addSqlFunction(): void
    {
        $this->db->query('DROP FUNCTION IF EXISTS setDateTime');

        $sql = "CREATE FUNCTION setDateTime ( setDate varchar(20) )
                RETURNS DATETIME
                READS SQL DATA
                DETERMINISTIC
                BEGIN
                RETURN CONVERT(CONCAT(setDate,' ','01:01:11'), DATETIME);
                END;";

        $this->db->query($sql);
    }

    public function testRawSqlUpdateObject(): void
    {
        $data = [];

        $row             = new stdclass();
        $row->email      = 'derek@world.com';
        $row->created_at = new RawSql("setDateTime('2022-01-01')");
        $data[]          = $row;

        $row             = new stdclass();
        $row->email      = 'ahmadinejad@world.com';
        $row->created_at = new RawSql("setDateTime('2022-01-01')");
        $data[]          = $row;

        $this->db->table('user')->updateBatch($data, 'email');

        $row->created_at = new RawSql("setDateTime('2022-01-11')");

        $this->db->table('user')->update($row, "email = 'ahmadinejad@world.com'");

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'created_at' => '2022-01-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'created_at' => '2022-01-11 01:01:11']);
    }

    public function testRawSqlSetUpdateObject(): void
    {
        $data = [];

        $row             = new stdclass();
        $row->email      = 'derek@world.com';
        $row->created_at = new RawSql("setDateTime('2022-02-01')");
        $data[]          = $row;

        $row             = new stdclass();
        $row->email      = 'ahmadinejad@world.com';
        $row->created_at = new RawSql("setDateTime('2022-02-01')");
        $data[]          = $row;

        $this->db->table('user')->setUpdateBatch($data, 'email')->updateBatch(null, 'email');

        $row->created_at = new RawSql("setDateTime('2022-02-11')");

        $this->db->table('user')->set($row)->update(null, "email = 'ahmadinejad@world.com'");

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'created_at' => '2022-02-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'created_at' => '2022-02-11 01:01:11']);
    }

    public function testRawSqlUpdateArray(): void
    {
        $data = [
            ['email' => 'derek@world.com', 'created_at' => new RawSql("setDateTime('2022-03-01')")],
            ['email' => 'ahmadinejad@world.com', 'created_at' => new RawSql("setDateTime('2022-03-01')")],
        ];

        $this->db->table('user')->updateBatch($data, 'email');

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'created_at' => '2022-03-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'created_at' => '2022-03-01 01:01:11']);

        $data = ['email' => 'ahmadinejad@world.com', 'created_at' => new RawSql("setDateTime('2022-03-11')")];

        $this->db->table('user')->update($data, "email = 'ahmadinejad@world.com'");

        $this->seeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'created_at' => '2022-03-11 01:01:11']);
    }

    public function testRawSqlInsertArray(): void
    {
        $data = [
            ['email' => 'pedro@world.com', 'created_at' => new RawSql("setDateTime('2022-04-01')")],
            ['email' => 'todd@world.com', 'created_at' => new RawSql("setDateTime('2022-04-01')")],
        ];

        $this->db->table('user')->insertBatch($data);

        $this->seeInDatabase('user', ['email' => 'pedro@world.com', 'created_at' => '2022-04-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'todd@world.com', 'created_at' => '2022-04-01 01:01:11']);

        $data = ['email' => 'jason@world.com', 'created_at' => new RawSql("setDateTime('2022-04-11')")];

        $this->db->table('user')->insert($data);

        $this->seeInDatabase('user', ['email' => 'jason@world.com', 'created_at' => '2022-04-11 01:01:11']);
    }

    public function testRawSqlInsertObject(): void
    {
        $data = [];

        $row             = new stdclass();
        $row->email      = 'tony@world.com';
        $row->created_at = new RawSql("setDateTime('2022-05-01')");
        $data[]          = $row;

        $row             = new stdclass();
        $row->email      = 'sara@world.com';
        $row->created_at = new RawSql("setDateTime('2022-05-01')");
        $data[]          = $row;

        $this->db->table('user')->insertBatch($data);

        $row->email      = 'jessica@world.com';
        $row->created_at = new RawSql("setDateTime('2022-05-11')");

        $this->db->table('user')->insert($row);

        $this->seeInDatabase('user', ['email' => 'tony@world.com', 'created_at' => '2022-05-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'sara@world.com', 'created_at' => '2022-05-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'jessica@world.com', 'created_at' => '2022-05-11 01:01:11']);
    }

    public function testRawSqlSetInsertObject(): void
    {
        $data = [];

        $row             = new stdclass();
        $row->email      = 'laura@world.com';
        $row->created_at = new RawSql("setDateTime('2022-06-01')");
        $data[]          = $row;

        $row             = new stdclass();
        $row->email      = 'travis@world.com';
        $row->created_at = new RawSql("setDateTime('2022-06-01')");
        $data[]          = $row;

        $this->db->table('user')->setInsertBatch($data)->insertBatch();

        $this->seeInDatabase('user', ['email' => 'laura@world.com', 'created_at' => '2022-06-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'travis@world.com', 'created_at' => '2022-06-01 01:01:11']);

        $row->email      = 'steve@world.com';
        $row->created_at = new RawSql("setDateTime('2022-06-11')");

        $this->db->table('user')->set($row)->insert();

        $this->seeInDatabase('user', ['email' => 'steve@world.com', 'created_at' => '2022-06-11 01:01:11']);

        $this->db->table('user')
            ->set('email', 'dan@world.com')
            ->set('created_at', new RawSql("setDateTime('2022-06-13')"))
            ->insert();

        $this->seeInDatabase('user', ['email' => 'dan@world.com', 'created_at' => '2022-06-13 01:01:11']);
    }
}
