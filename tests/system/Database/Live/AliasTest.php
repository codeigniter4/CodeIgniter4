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
final class AliasTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testAlias(): void
    {
        $builder = $this->db->table('job j');

        $jobs = $builder
            ->where('j.name', 'Developer')
            ->get();

        $this->assertCount(1, $jobs->getResult());
    }
}
