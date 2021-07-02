<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class EmptyTest extends CIUnitTestCase
{
    protected $db;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    //--------------------------------------------------------------------

    public function testEmptyWithNoTable()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $answer = $builder->testMode()->emptyTable();

        $expectedSQL = 'DELETE FROM "jobs"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    //--------------------------------------------------------------------
}
