<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class AliasTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testAlias()
    {
        $builder = $this->db->table('job j');

        $jobs = $builder
            ->where('j.name', 'Developer')
            ->get();

        $this->assertCount(1, $jobs->getResult());
    }

    //--------------------------------------------------------------------
}
