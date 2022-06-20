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

use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Query;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class PreparedQueryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed                   = CITestSeeder::class;
    private ?BasePreparedQuery $query = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = null;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->query !== null) {
            $this->query->close();
        }
    }

    public function testPrepareReturnsPreparedQuery()
    {
        $this->query = $this->db->prepare(static fn ($db) => $db->table('user')->insert([
            'name'    => 'a',
            'email'   => 'b@example.com',
            'country' => 'JP',
        ]));

        $this->assertInstanceOf(BasePreparedQuery::class, $this->query);

        $ec  = $this->db->escapeChar;
        $pre = $this->db->DBPrefix;

        $placeholders = '?, ?, ?';

        if ($this->db->DBDriver === 'Postgre') {
            $placeholders = '$1, $2, $3';
        }

        if ($this->db->DBDriver === 'SQLSRV') {
            $database = $this->db->getDatabase();
            $expected = "INSERT INTO {$ec}{$database}{$ec}.{$ec}{$this->db->schema}{$ec}.{$ec}{$pre}user{$ec} ({$ec}name{$ec},{$ec}email{$ec},{$ec}country{$ec}) VALUES ({$placeholders})";
        } else {
            $expected = "INSERT INTO {$ec}{$pre}user{$ec} ({$ec}name{$ec}, {$ec}email{$ec}, {$ec}country{$ec}) VALUES ({$placeholders})";
        }

        $this->assertSame($expected, $this->query->getQueryString());
    }

    public function testPrepareReturnsManualPreparedQuery()
    {
        $this->query = $this->db->prepare(static function ($db) {
            $sql = "INSERT INTO {$db->DBPrefix}user (name, email, country) VALUES (?, ?, ?)";

            return (new Query($db))->setQuery($sql);
        });

        $this->assertInstanceOf(BasePreparedQuery::class, $this->query);

        $pre = $this->db->DBPrefix;

        $placeholders = '?, ?, ?';

        if ($this->db->DBDriver === 'Postgre') {
            $placeholders = '$1, $2, $3';
        }

        $expected = "INSERT INTO {$pre}user (name, email, country) VALUES ({$placeholders})";
        $this->assertSame($expected, $this->query->getQueryString());
    }

    public function testExecuteRunsQueryAndReturnsResultObject()
    {
        $this->query = $this->db->prepare(static fn ($db) => $db->table('user')->insert([
            'name'    => 'a',
            'email'   => 'b@example.com',
            'country' => 'x',
        ]));

        $this->query->execute('foo', 'foo@example.com', 'US');
        $this->query->execute('bar', 'bar@example.com', 'GB');

        $this->dontSeeInDatabase($this->db->DBPrefix . 'user', ['name' => 'a', 'email' => 'b@example.com']);
        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'foo', 'email' => 'foo@example.com']);
        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'bar', 'email' => 'bar@example.com']);
    }

    public function testExecuteRunsQueryAndReturnsManualResultObject()
    {
        $this->query = $this->db->prepare(static function ($db) {
            $sql = "INSERT INTO {$db->protectIdentifiers($db->DBPrefix . 'user')} ("
                  . $db->protectIdentifiers('name') . ', '
                  . $db->protectIdentifiers('email') . ', '
                  . $db->protectIdentifiers('country')
                  . ') VALUES (?, ?, ?)';

            if ($db->DBDriver === 'SQLSRV') {
                $sql = "INSERT INTO {$db->schema}.{$db->DBPrefix}user (name, email, country) VALUES (?, ?, ?)";
            }

            return (new Query($db))->setQuery($sql);
        });

        $this->query->execute('foo', 'foo@example.com', 'US');
        $this->query->execute('bar', 'bar@example.com', 'GB');

        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'foo', 'email' => 'foo@example.com']);
        $this->seeInDatabase($this->db->DBPrefix . 'user', ['name' => 'bar', 'email' => 'bar@example.com']);
    }
}
