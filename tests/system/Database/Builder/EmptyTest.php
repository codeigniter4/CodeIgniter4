<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class EmptyTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testEmptyWithNoTable(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $answer = $builder->testMode()->emptyTable();

        $expectedSQL = 'DELETE FROM "jobs"';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }
}
