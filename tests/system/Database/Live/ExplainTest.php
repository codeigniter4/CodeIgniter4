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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ExplainTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testExplainReturnsResultForSupportedDrivers(): void
    {
        if (in_array($this->db->DBDriver, ['OCI8', 'SQLSRV'], true)) {
            $this->markTestSkipped($this->db->DBDriver . ' does not support explain().');
        }

        $result = $this->db->table('job')
            ->where('name', 'Developer')
            ->explain();

        $this->assertInstanceOf(ResultInterface::class, $result);

        $expectedPrefix = $this->db->DBDriver === 'SQLite3'
            ? 'EXPLAIN QUERY PLAN SELECT'
            : 'EXPLAIN SELECT';

        $this->assertStringStartsWith(
            $expectedPrefix,
            str_replace("\n", ' ', (string) $this->db->getLastQuery()),
        );
    }

    public function testExplainThrowsForUnsupportedDrivers(): void
    {
        if (! in_array($this->db->DBDriver, ['OCI8', 'SQLSRV'], true)) {
            $this->markTestSkipped($this->db->DBDriver . ' supports explain().');
        }

        $this->expectException(DatabaseException::class);

        $this->db->table('job')->explain();
    }
}
