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

use CodeIgniter\Database\Query;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class PretendTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        // We share `$this->db` in testing, so we need to restore the state.
        $this->db->pretend(false);
    }

    public function testPretendReturnsQueryObject(): void
    {
        $result = $this->db->pretend(false)
            ->table('user')
            ->get();

        $this->assertNotInstanceOf(Query::class, $result);

        $result = $this->db->pretend(true)
            ->table('user')
            ->get();

        $this->assertInstanceOf(Query::class, $result);
    }
}
