<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class LikeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testLikeDefault()
    {
        $job = $this->db->table('job')->like('name', 'veloper')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeBefore()
    {
        $job = $this->db->table('job')->like('name', 'veloper', 'before')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeAfter()
    {
        $job = $this->db->table('job')->like('name', 'Develop')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeBoth()
    {
        $job = $this->db->table('job')->like('name', 'veloper', 'both')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeCaseInsensitive()
    {
        $job = $this->db->table('job')->like('name', 'VELOPER', 'both', null, true)->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testOrLike()
    {
        $jobs = $this->db->table('job')->like('name', 'ian')
            ->orLike('name', 'veloper')
            ->get()
            ->getResult();

        $this->assertCount(3, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Politician', $jobs[1]->name);
        $this->assertSame('Musician', $jobs[2]->name);
    }

    public function testNotLike()
    {
        $jobs = $this->db->table('job')
            ->notLike('name', 'veloper')
            ->get()
            ->getResult();

        $this->assertCount(3, $jobs);
        $this->assertSame('Politician', $jobs[0]->name);
        $this->assertSame('Accountant', $jobs[1]->name);
        $this->assertSame('Musician', $jobs[2]->name);
    }

    public function testOrNotLike()
    {
        $jobs = $this->db->table('job')
            ->like('name', 'ian')
            ->orNotLike('name', 'veloper')
            ->get()
            ->getResult();

        $this->assertCount(3, $jobs);
        $this->assertSame('Politician', $jobs[0]->name);
        $this->assertSame('Accountant', $jobs[1]->name);
        $this->assertSame('Musician', $jobs[2]->name);
    }

    public function testLikeSpacesOrTabs()
    {
        $builder = $this->db->table('misc');
        $spaces  = $builder->like('value', '   ')->get()->getResult();
        $tabs    = $builder->like('value', "\t")->get()->getResult();

        $this->assertCount(1, $spaces);
        $this->assertCount(1, $tabs);
    }
}
