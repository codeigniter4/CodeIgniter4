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

use CodeIgniter\Database\SQLSRV\Builder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class IncrementTest extends CIUnitTestCase
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

    public function testIncrementWhenCastTextToIntFalse(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'created_at' => 6]);

        $builder = $this->db->table('job');

        $this->assertInstanceOf(Builder::class, $builder);

        $builder->castTextToInt = false;

        $builder->where('name', 'incremental')
            ->increment('created_at');

        $this->seeInDatabase('job', ['name' => 'incremental', 'created_at' => 7]);
    }

    public function testDecrementWhenCastTextToIntFalse(): void
    {
        $this->hasInDatabase('job', ['name' => 'decremental', 'created_at' => 6]);

        $builder = $this->db->table('job');

        $this->assertInstanceOf(Builder::class, $builder);

        $builder->castTextToInt = false;

        $builder->where('name', 'decremental')
            ->decrement('created_at');

        $this->seeInDatabase('job', ['name' => 'decremental', 'created_at' => 5]);
    }
}
