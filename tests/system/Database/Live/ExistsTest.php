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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ExistsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testExistsReturnsTrueWithResults(): void
    {
        $this->assertTrue($this->db->table('job')->where('name', 'Developer')->exists());
    }

    public function testExistsReturnsFalseWithNoResults(): void
    {
        $this->assertFalse($this->db->table('job')->where('name', 'Superstar')->exists());
    }

    public function testDoesntExistReturnsFalseWithResults(): void
    {
        $this->assertFalse($this->db->table('job')->where('name', 'Developer')->doesntExist());
    }

    public function testDoesntExistReturnsTrueWithNoResults(): void
    {
        $this->assertTrue($this->db->table('job')->where('name', 'Superstar')->doesntExist());
    }

    public function testExistsHonorsReset(): void
    {
        $builder = $this->db->table('job');

        $this->assertTrue($builder->where('name', 'Developer')->exists(false));
        $this->assertTrue($builder->exists());
    }

    public function testExistsHonorsLimitAndOffset(): void
    {
        $this->assertFalse(
            $this->db->table('job')
                ->orderBy('id')
                ->limit(1, 10)
                ->exists(),
        );
    }
}
