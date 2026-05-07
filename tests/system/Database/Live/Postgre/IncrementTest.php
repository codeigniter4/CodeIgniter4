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

namespace CodeIgniter\Database\Live\Postgre;

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

        if ($this->db->DBDriver !== 'Postgre') {
            $this->markTestSkipped('This test is only for Postgre.');
        }
    }

    public function testIncrementWithNumericColumns(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'created_at' => 6]);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->increment('created_at');

        $this->seeInDatabase('job', ['name' => 'incremental', 'created_at' => 7]);
    }

    public function testIncrementWithNumericColumnsAndValue(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'created_at' => 6]);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->increment('created_at', 2);

        $this->seeInDatabase('job', ['name' => 'incremental', 'created_at' => 8]);
    }

    public function testDecrementWithNumericColumns(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'created_at' => 6]);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->decrement('created_at');

        $this->seeInDatabase('job', ['name' => 'incremental', 'created_at' => 5]);
    }

    public function testDecrementWithNumericColumnsAndValue(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'created_at' => 6]);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->decrement('created_at', 2);

        $this->seeInDatabase('job', ['name' => 'incremental', 'created_at' => 4]);
    }
}
