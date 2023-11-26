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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class IncrementTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testIncrement(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->increment('description');

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '7']);
    }

    public function testIncrementWithValue(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->increment('description', 2);

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '8']);
    }

    public function testResetStateAfterIncrement(): void
    {
        $this->hasInDatabase('job', ['name' => 'account1', 'description' => '10']);
        $this->hasInDatabase('job', ['name' => 'account2', 'description' => '10']);

        $builder = $this->db->table('job');

        $builder->where('name', 'account1')->increment('description');
        $builder->where('name', 'account2')->increment('description');

        $this->seeInDatabase('job', ['name' => 'account1', 'description' => '11']);
        $this->seeInDatabase('job', ['name' => 'account2', 'description' => '11']);
    }

    public function testDecrement(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->decrement('description');

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '5']);
    }

    public function testDecrementWithValue(): void
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->decrement('description', 2);

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '4']);
    }

    public function testResetStateAfterDecrement(): void
    {
        $this->hasInDatabase('job', ['name' => 'account1', 'description' => '10']);
        $this->hasInDatabase('job', ['name' => 'account2', 'description' => '10']);

        $builder = $this->db->table('job');

        $builder->where('name', 'account1')->decrement('description');
        $builder->where('name', 'account2')->decrement('description');

        $this->seeInDatabase('job', ['name' => 'account1', 'description' => '9']);
        $this->seeInDatabase('job', ['name' => 'account2', 'description' => '9']);
    }
}
