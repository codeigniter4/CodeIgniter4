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
final class UnionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testUnion(): void
    {
        $union = $this->db->table('user')
            ->limit(1)
            ->orderBy('id', 'ASC');
        $builder = $this->db->table('user');

        $builder->union($union)
            ->limit(1)
            ->orderBy('id', 'DESC');

        $result = $this->db->newQuery()
            ->fromSubquery($builder, 'q')
            ->orderBy('id', 'DESC')
            ->get();

        $this->assertSame(2, $result->getNumRows());

        $rows = $result->getResult();
        $this->assertSame(4, (int) $rows[0]->id);
        $this->assertSame(1, (int) $rows[1]->id);
    }

    public function testUnionAll(): void
    {
        $union   = $this->db->table('user');
        $builder = $this->db->table('user');

        $result = $builder->unionAll($union)->get();

        $this->assertSame(8, $result->getNumRows());
    }
}
