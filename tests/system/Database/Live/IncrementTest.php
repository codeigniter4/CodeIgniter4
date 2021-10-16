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

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class IncrementTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testIncrement()
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->increment('description');

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '7']);
    }

    public function testIncrementWithValue()
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->increment('description', 2);

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '8']);
    }

    public function testDecrement()
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->decrement('description');

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '5']);
    }

    public function testDecrementWithValue()
    {
        $this->hasInDatabase('job', ['name' => 'incremental', 'description' => '6']);

        $this->db->table('job')
            ->where('name', 'incremental')
            ->decrement('description', 2);

        $this->seeInDatabase('job', ['name' => 'incremental', 'description' => '4']);
    }
}
