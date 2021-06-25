<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DbDebugTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    public function testDBDebugTrue()
    {
        $this->setPrivateProperty($this->db, 'DBDebug', true);
        $this->expectException('Exception');
        $this->db->simpleQuery('SELECT * FROM db_error');
    }

    public function testDBDebugFalse()
    {
        $this->setPrivateProperty($this->db, 'DBDebug', false);
        $result = $this->db->simpleQuery('SELECT * FROM db_error');
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', true);
        parent::tearDown();
    }
}
