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

namespace CodeIgniter\Database\Live\MySQLi;

use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class RawSqlTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->db->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi has its own implementation.');
        }

        $this->addSqlFunction();
    }

    private function addSqlFunction(): void
    {
        $this->db->query('DROP FUNCTION IF EXISTS setDateTime');
        $this->db->query(<<<'SQL_WRAP'
            CREATE FUNCTION setDateTime ( setDate varchar(20) )
            RETURNS DATETIME
            READS SQL DATA
            DETERMINISTIC
            BEGIN
            RETURN CONVERT(CONCAT(setDate,' ','01:01:11'), DATETIME);
            END;
            SQL_WRAP);
    }

    public function testRawSqlUpdateObject(): void
    {
        $this->db->table('user')->updateBatch([
            (object) [
                'email'      => 'derek@world.com',
                'created_at' => new RawSql("setDateTime('2022-01-01')"),
            ],
            (object) [
                'email'      => 'ahmadinejad@world.com',
                'created_at' => new RawSql("setDateTime('2022-01-01')"),
            ],
        ], 'email');
        $this->db->table('user')->update(
            (object) ['created_at' => new RawSql("setDateTime('2022-01-11')")],
            "email = 'ahmadinejad@world.com'",
        );

        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'created_at' => '2022-01-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'created_at' => '2022-01-11 01:01:11']);
    }

    /**
     * @deprecated This test covers the deprecated setUpdateBatch() method.
     */
    public function testRawSqlSetUpdateObject(): void
    {
        $data = [];

        $row             = new stdClass();
        $row->email      = 'derek@world.com';
        $row->created_at = new RawSql("setDateTime('2022-02-01')");
        $data[]          = $row;

        $row             = new stdClass();
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
        $this->db->table('user')->updateBatch([
            ['email' => 'derek@world.com', 'created_at' => new RawSql("setDateTime('2022-03-01')")],
            ['email' => 'ahmadinejad@world.com', 'created_at' => new RawSql("setDateTime('2022-03-01')")],
        ], 'email');
        $this->seeInDatabase('user', ['email' => 'derek@world.com', 'created_at' => '2022-03-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'created_at' => '2022-03-01 01:01:11']);

        $this->db->table('user')->update(
            ['email' => 'ahmadinejad@world.com', 'created_at' => new RawSql("setDateTime('2022-03-11')")],
            "email = 'ahmadinejad@world.com'",
        );
        $this->seeInDatabase('user', ['email' => 'ahmadinejad@world.com', 'created_at' => '2022-03-11 01:01:11']);
    }

    public function testRawSqlInsertArray(): void
    {
        $this->db->table('user')->insertBatch([
            [
                'name'       => 'Pedro Pascal',
                'email'      => 'pedro@world.com',
                'country'    => 'Chile',
                'created_at' => new RawSql("setDateTime('2022-04-01')"),
            ],
            [
                'name'       => 'Todd Howard',
                'email'      => 'todd@world.com',
                'country'    => 'US',
                'created_at' => new RawSql("setDateTime('2022-04-01')"),
            ],
        ]);
        $this->seeInDatabase('user', ['email' => 'pedro@world.com', 'created_at' => '2022-04-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'todd@world.com', 'created_at' => '2022-04-01 01:01:11']);

        $this->db->table('user')->insert([
            'name'       => 'Jason Momoa',
            'email'      => 'jason@world.com',
            'country'    => 'US',
            'created_at' => new RawSql("setDateTime('2022-04-11')"),
        ]);
        $this->seeInDatabase('user', ['email' => 'jason@world.com', 'created_at' => '2022-04-11 01:01:11']);
    }

    public function testRawSqlInsertObject(): void
    {
        $this->db->table('user')->insertBatch([
            (object) [
                'name'       => 'Tony Stark',
                'email'      => 'tony@world.com',
                'country'    => 'US',
                'created_at' => new RawSql("setDateTime('2022-05-01')"),
            ],
            (object) [
                'name'       => 'Sara Connor',
                'email'      => 'sara@world.com',
                'country'    => 'US',
                'created_at' => new RawSql("setDateTime('2022-05-01')"),
            ],
        ]);
        $this->db->table('user')->insert((object) [
            'name'       => 'Jessica Jones',
            'email'      => 'jessica@world.com',
            'country'    => 'US',
            'created_at' => new RawSql("setDateTime('2022-05-11')"),
        ]);

        $this->seeInDatabase('user', ['email' => 'tony@world.com', 'created_at' => '2022-05-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'sara@world.com', 'created_at' => '2022-05-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'jessica@world.com', 'created_at' => '2022-05-11 01:01:11']);
    }

    /**
     * @deprecated This test covers the deprecated setInsertBatch() method.
     */
    public function testRawSqlSetInsertObject(): void
    {
        $data = [];

        $row             = new stdClass();
        $row->name       = 'Laura Palmer';
        $row->email      = 'laura@world.com';
        $row->country    = 'US';
        $row->created_at = new RawSql("setDateTime('2022-06-01')");
        $data[]          = $row;

        $row             = new stdClass();
        $row->name       = 'Travis Touchdown';
        $row->email      = 'travis@world.com';
        $row->country    = 'US';
        $row->created_at = new RawSql("setDateTime('2022-06-01')");
        $data[]          = $row;

        $this->db->table('user')->setInsertBatch($data)->insertBatch();
        $this->seeInDatabase('user', ['email' => 'laura@world.com', 'created_at' => '2022-06-01 01:01:11']);
        $this->seeInDatabase('user', ['email' => 'travis@world.com', 'created_at' => '2022-06-01 01:01:11']);

        $row             = new stdClass();
        $row->name       = 'Steve Rogers';
        $row->email      = 'steve@world.com';
        $row->country    = 'US';
        $row->created_at = new RawSql("setDateTime('2022-06-11')");
        $this->db->table('user')->set($row)->insert();
        $this->seeInDatabase('user', ['email' => 'steve@world.com', 'created_at' => '2022-06-11 01:01:11']);

        $this->db->table('user')
            ->set('name', 'Dan Brown')
            ->set('email', 'dan@world.com')
            ->set('country', 'US')
            ->set('created_at', new RawSql("setDateTime('2022-06-13')"))
            ->insert();
        $this->seeInDatabase('user', ['email' => 'dan@world.com', 'created_at' => '2022-06-13 01:01:11']);
    }
}
