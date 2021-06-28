<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Query;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class PreparedQueryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = 'Tests\Support\Database\Seeds\CITestSeeder';

    //--------------------------------------------------------------------

    public function testPrepareReturnsPreparedQuery()
    {
        $query = $this->db->prepare(static function ($db) {
            return $db->table('user')->insert([
                'name'  => 'a',
                'email' => 'b@example.com',
            ]);
        });

        $this->assertInstanceOf(BasePreparedQuery::class, $query);

        $ec  = $this->db->escapeChar;
        $pre = $this->db->DBPrefix;

        $placeholders = '?, ?';

        if ($this->db->DBDriver === 'Postgre') {
            $placeholders = '$1, $2';
        }

        if ($this->db->DBDriver === 'SQLSRV') {
            $database = $this->db->getDatabase();
            $expected = "INSERT INTO {$ec}{$database}{$ec}.{$ec}dbo{$ec}.{$ec}{$pre}user{$ec} ({$ec}name{$ec},{$ec}email{$ec}) VALUES ({$placeholders})";
        } else {
            $expected = "INSERT INTO {$ec}{$pre}user{$ec} ({$ec}name{$ec}, {$ec}email{$ec}) VALUES ({$placeholders})";
        }
        $this->assertSame($expected, $query->getQueryString());

        $query->close();
    }

    public function testPrepareReturnsManualPreparedQuery()
    {
        $query = $this->db->prepare(static function ($db) {
            $sql = "INSERT INTO {$db->DBPrefix}user (name, email, country) VALUES (?, ?, ?)";

            return (new Query($db))->setQuery($sql);
        });

        $this->assertInstanceOf(BasePreparedQuery::class, $query);

        $pre = $this->db->DBPrefix;

        $placeholders = '?, ?, ?';

        if ($this->db->DBDriver === 'Postgre') {
            $placeholders = '$1, $2, $3';
        }

        $expected = "INSERT INTO {$pre}user (name, email, country) VALUES ({$placeholders})";
        $this->assertSame($expected, $query->getQueryString());

        $query->close();
    }

    //--------------------------------------------------------------------

    public function testExecuteRunsQueryAndReturnsResultObject()
    {
        $query = $this->db->prepare(static function ($db) {
            return $db->table('user')->insert([
                'name'    => 'a',
                'email'   => 'b@example.com',
                'country' => 'x',
            ]);
        });

        $query->execute('foo', 'foo@example.com', 'US');
        $query->execute('bar', 'bar@example.com', 'GB');

        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'foo', 'email' => 'foo@example.com']);
        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'bar', 'email' => 'bar@example.com']);

        $query->close();
    }

    public function testExecuteRunsQueryAndReturnsManualResultObject()
    {
        $query = $this->db->prepare(static function ($db) {
            $sql = "INSERT INTO {$db->DBPrefix}user (name, email, country) VALUES (?, ?, ?)";

            return (new Query($db))->setQuery($sql);
        });

        $query->execute('foo', 'foo@example.com', '');
        $query->execute('bar', 'bar@example.com', '');

        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'foo', 'email' => 'foo@example.com']);
        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'bar', 'email' => 'bar@example.com']);

        $query->close();
    }
}
