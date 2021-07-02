<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class OrderTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testOrderAscending()
    {
        $jobs = $this->db->table('job')
            ->orderBy('name', 'asc')
            ->get()
            ->getResult();

        $this->assertCount(4, $jobs);
        $this->assertSame('Accountant', $jobs[0]->name);
        $this->assertSame('Developer', $jobs[1]->name);
        $this->assertSame('Musician', $jobs[2]->name);
        $this->assertSame('Politician', $jobs[3]->name);
    }

    //--------------------------------------------------------------------

    public function testOrderDescending()
    {
        $jobs = $this->db->table('job')
            ->orderBy('name', 'desc')
            ->get()
            ->getResult();

        $this->assertCount(4, $jobs);
        $this->assertSame('Accountant', $jobs[3]->name);
        $this->assertSame('Developer', $jobs[2]->name);
        $this->assertSame('Musician', $jobs[1]->name);
        $this->assertSame('Politician', $jobs[0]->name);
    }

    //--------------------------------------------------------------------

    public function testMultipleOrderValues()
    {
        $users = $this->db->table('user')
            ->orderBy('country', 'asc')
            ->orderBy('name', 'desc')
            ->get()
            ->getResult();

        $this->assertCount(4, $users);
        $this->assertSame('Ahmadinejad', $users[0]->name);
        $this->assertSame('Chris Martin', $users[1]->name);
        $this->assertSame('Richard A Causey', $users[2]->name);
        $this->assertSame('Derek Jones', $users[3]->name);
    }

    //--------------------------------------------------------------------

    public function testOrderRandom()
    {
        $sql = $this->db->table('job')
            ->orderBy('name', 'random')
            ->getCompiledSelect();

        $key   = 'RANDOM()';
        $table = $this->db->protectIdentifiers('job', true);

        if ($this->db->DBDriver === 'MySQLi') {
            $key = 'RAND()';
        } elseif ($this->db->DBDriver === 'SQLSRV') {
            $key   = 'NEWID()';
            $table = '"' . $this->db->getDatabase() . '"."' . $this->db->schema . '".' . $table;
        }

        $expected = 'SELECT * FROM ' . $table . ' ORDER BY ' . $key;

        $this->assertSame($expected, str_replace("\n", ' ', $sql));
    }
}
