<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\SQLSRV;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class SelectLockTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->db->DBDriver !== 'SQLSRV') {
            $this->markTestSkipped('This test is only for SQLSRV.');
        }
    }

    public function testLockForUpdateSkipLockedExecutes(): void
    {
        $row = null;

        $this->db->transBegin();

        try {
            $row = $this->db->table('job')
                ->where('name', 'Developer')
                ->lockForUpdate()
                ->skipLocked()
                ->get()
                ->getRowArray();
        } finally {
            $this->db->transRollback();
        }

        $this->assertIsArray($row);
        $this->assertSame('Developer', $row['name']);
    }
}
