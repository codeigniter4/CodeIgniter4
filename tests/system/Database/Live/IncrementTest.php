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

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;
use TypeError;

/**
 * @internal
 */
#[Group('DatabaseLive')]
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

    public function testIncrementMany(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->db->table('job')
            ->where('name', 'job1')
            ->incrementMany(['description' => 2, 'created_at' => 3]);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '8', 'created_at' => 4]);
    }

    public function testIncrementManyWithValue(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->db->table('job')
            ->where('name', 'job1')
            ->incrementMany(['description', 'created_at'], 2);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '8', 'created_at' => 3]);
    }

    public function testIncrementManyWithNegativeValue(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->db->table('job')
            ->where('name', 'job1')
            ->incrementMany(['description' => 2, 'created_at' => -1]);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '8', 'created_at' => 0]);
    }

    public function testIncrementManyWithEmptyColumns(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument #1 ($columns) cannot be empty.');

        $this->db->table('job')
            ->where('name', 'task1')
            ->incrementMany([]);
    }

    public function testIncrementManyWithNonIntegerValues(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #1 ($columns) must contain only int values, string given for "created_at".');

        $this->db->table('job')
            ->where('name', 'job1')
            ->incrementMany(['description' => 2, 'created_at' => 'wrongValue']);
    }

    public function testResetStateAfterIncrementMany(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);
        $this->hasInDatabase('job', ['name' => 'job2', 'description' => '2', 'created_at' => 4]);

        $builder = $this->db->table('job');

        $builder->where('name', 'job1')->incrementMany(['description', 'created_at']);
        $builder->where('name', 'job2')->incrementMany(['description', 'created_at']);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '7', 'created_at' => 2]);
        $this->seeInDatabase('job', ['name' => 'job2', 'description' => '3', 'created_at' => 5]);
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

    public function testDecrementMany(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->db->table('job')
            ->where('name', 'job1')
            ->decrementMany(['description' => 2, 'created_at' => 3]);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '4', 'created_at' => -2]);
    }

    public function testDecrementManyWithValue(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->db->table('job')
            ->where('name', 'job1')
            ->decrementMany(['description', 'created_at'], 2);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '4', 'created_at' => -1]);
    }

    public function testDecrementManyWithNegativeValues(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->db->table('job')
            ->where('name', 'job1')
            ->decrementMany(['description' => 2, 'created_at' => -1]);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '4', 'created_at' => 2]);
    }

    public function testDecrementManyWithEmptyColumns(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument #1 ($columns) cannot be empty.');

        $this->db->table('job')
            ->where('name', 'task1')
            ->decrementMany([]);
    }

    public function testDecrementManyWithNonIntegerValues(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #1 ($columns) must contain only int values, string given for "created_at".');

        $this->db->table('job')
            ->where('name', 'job1')
            ->decrementMany(['description' => 2, 'created_at' => 'wrongValue']);
    }

    public function testResetStateAfterDecrementMany(): void
    {
        $this->hasInDatabase('job', ['name' => 'job1', 'description' => '6', 'created_at' => 1]);
        $this->hasInDatabase('job', ['name' => 'job2', 'description' => '2', 'created_at' => 4]);

        $builder = $this->db->table('job');

        $builder->where('name', 'job1')->decrementMany(['description', 'created_at']);
        $builder->where('name', 'job2')->decrementMany(['description', 'created_at']);

        $this->seeInDatabase('job', ['name' => 'job1', 'description' => '5', 'created_at' => 0]);
        $this->seeInDatabase('job', ['name' => 'job2', 'description' => '1', 'created_at' => 3]);
    }
}
