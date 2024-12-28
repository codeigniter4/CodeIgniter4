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

use CodeIgniter\Database\RawSql;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class LikeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testLikeDefault(): void
    {
        $job = $this->db->table('job')->like('name', 'veloper')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeBefore(): void
    {
        $job = $this->db->table('job')->like('name', 'veloper', 'before')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeAfter(): void
    {
        $job = $this->db->table('job')->like('name', 'Develop')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeBoth(): void
    {
        $job = $this->db->table('job')->like('name', 'veloper', 'both')->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    public function testLikeCaseInsensitive(): void
    {
        $job = $this->db->table('job')->like('name', 'VELOPER', 'both', null, true)->get();
        $job = $job->getRow();

        $this->assertSame(1, (int) $job->id);
        $this->assertSame('Developer', $job->name);
    }

    #[DataProvider('provideMultibyteCharacters')]
    public function testLikeCaseInsensitiveWithMultibyteCharacter(string $match, string $result): void
    {
        $wai = $this->db->table('without_auto_increment')->like('value', $match, 'both', null, true)->get();
        $wai = $wai->getRow();

        $this->assertSame($result, $wai->key);
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function provideMultibyteCharacters(): iterable
    {
        yield from [
            'polish'    => ['ŁĄ', 'multibyte characters pl'],
            'farsi'     => ['خٌوب', 'multibyte characters fa'],
            'bengali'   => ['টাইপ', 'multibyte characters bn'],
            'korean'    => ['캐스팅', 'multibyte characters ko'],
            'malayalam' => ['ടൈപ്പ്', 'multibyte characters ml'],
        ];
    }

    public function testOrLike(): void
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

    public function testNotLike(): void
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

    public function testOrNotLike(): void
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

    public function testLikeSpacesOrTabs(): void
    {
        $builder = $this->db->table('misc');
        $spaces  = $builder->like('value', '   ')->get()->getResult();
        $tabs    = $builder->like('value', "\t")->get()->getResult();

        $this->assertCount(1, $spaces);
        $this->assertCount(1, $tabs);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/7268
     */
    public function testLikeRawSqlAndCountAllResultsAndGet(): void
    {
        $builder = $this->db->table('job');

        $key = $this->db->DBDriver === 'OCI8' ? new RawSql('"name"') : new RawSql('name');

        $builder->like($key, 'Developer');
        $count   = $builder->countAllResults(false);
        $results = $builder->get()->getResult();

        $this->assertSame(1, $count);
        $this->assertSame('Developer', $results[0]->name);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/7268
     */
    public function testLikeRawSqlAndGetAndCountAllResults(): void
    {
        $builder = $this->db->table('job');

        $key = $this->db->DBDriver === 'OCI8' ? new RawSql('"name"') : new RawSql('name');

        $builder->like($key, 'Developer');
        $results = $builder->get(null, 0, false)->getResult();
        $count   = $builder->countAllResults();

        $this->assertSame(1, $count);
        $this->assertSame('Developer', $results[0]->name);
    }
}
