<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class EmptyTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testEmpty()
    {
        $this->db->table('misc')->emptyTable();

        $this->assertSame(0, $this->db->table('misc')->countAll());
    }

    //--------------------------------------------------------------------

    public function testTruncate()
    {
        $this->db->table('misc')->truncate();

        $this->assertSame(0, $this->db->table('misc')->countAll());
    }

    //--------------------------------------------------------------------
}
