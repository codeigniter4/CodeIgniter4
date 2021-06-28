<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class TruncateTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testTruncate()
    {
        $builder = new BaseBuilder('user', $this->db);

        $expectedSQL = 'TRUNCATE "user"';

        $this->assertSame($expectedSQL, $builder->testMode()->truncate());
    }

    //--------------------------------------------------------------------
}
