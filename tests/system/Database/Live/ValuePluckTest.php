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
final class ValuePluckTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testValueReturnsFirstColumnValue(): void
    {
        $name = $this->db->table('job')
            ->where('id', 1)
            ->value('name');

        $this->assertSame('Developer', $name);
    }

    public function testValueReturnsNullWhenNoRowMatches(): void
    {
        $name = $this->db->table('job')
            ->where('name', 'Superstar')
            ->value('name');

        $this->assertNull($name);
    }

    public function testValueHonorsOrderingLimitAndOffset(): void
    {
        $name = $this->db->table('job')
            ->orderBy('id', 'ASC')
            ->limit(2, 1)
            ->value('name');

        $this->assertSame('Politician', $name);
    }

    public function testPluckReturnsColumnValues(): void
    {
        $names = $this->db->table('job')
            ->orderBy('id', 'ASC')
            ->pluck('name');

        $this->assertSame(['Developer', 'Politician', 'Accountant', 'Musician'], $names);
    }

    public function testPluckReturnsKeyedColumnValues(): void
    {
        $names = $this->db->table('job')
            ->orderBy('id', 'ASC')
            ->pluck('name', 'id');

        $this->assertSame([
            1 => 'Developer',
            2 => 'Politician',
            3 => 'Accountant',
            4 => 'Musician',
        ], $names);
    }
}
