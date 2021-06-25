<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Exception;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class BadQueryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';
    protected static $origDebug;

    //--------------------------------------------------------------------

    /**
     * This test must run first to store the inital debug value before we tinker with it below
     */
    public function testFirst()
    {
        $this::$origDebug = $this->getPrivateProperty($this->db, 'DBDebug');

        $this->assertIsBool($this::$origDebug);
    }

    public function testBadQueryDebugTrue()
    {
        // WARNING this value will persist! take care to roll it back.
        $this->setPrivateProperty($this->db, 'DBDebug', true);
        // expect an exception, class and message varies by DBMS
        $this->expectException(Exception::class);
        $this->db->query('SELECT * FROM table_does_not_exist');

        // this code is never executed
    }

    public function testBadQueryDebugFalse()
    {
        // WARNING this value will persist! take care to roll it back.
        $this->setPrivateProperty($this->db, 'DBDebug', false);
        // this throws an exception when DBDebug is true, but it'll return FALSE when DBDebug is false
        $query = $this->db->query('SELECT * FROM table_does_not_exist');
        $this->assertFalse($query);

        // restore the DBDebug value in effect when this unit test began
        $this->setPrivateProperty($this->db, 'DBDebug', self::$origDebug);
    }
}
