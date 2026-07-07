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

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use CodeIgniter\Test\Mock\MockQuery;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class DeleteTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testDelete(): void
    {
        $builder = $this->db->table('jobs');

        $answer = $builder->testMode()->delete(['id' => 1], null, true);

        $expectedSQL   = 'DELETE FROM "jobs" WHERE "id" = :id:';
        $expectedBinds = [
            'id' => [
                1,
                true,
            ],
        ];

        $this->assertSameSql($expectedSQL, $answer);
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testGetCompiledDelete(): void
    {
        $builder = $this->db->table('jobs');

        $builder->where('id', 1);
        $sql = $builder->getCompiledDelete();

        $expectedSQL = <<<'EOL'
            DELETE FROM "jobs"
            WHERE "id" = 1
            EOL;
        $this->assertSame($expectedSQL, $sql);
    }

    public function testGetCompiledDeleteWithTableAlias(): void
    {
        $builder = $this->db->table('jobs j');

        $builder->where('id', 1);
        $sql = $builder->getCompiledDelete();

        $expectedSQL = <<<'EOL'
            DELETE FROM "jobs"
            WHERE "id" = 1
            EOL;
        $this->assertSame($expectedSQL, $sql);
    }

    public function testGetCompiledDeleteWithLimit(): void
    {
        $builder = $this->db->table('jobs');

        $sql = $builder->where('id', 1)->limit(10)->getCompiledDelete();

        $expectedSQL = <<<'EOL'
            DELETE FROM "jobs"
            WHERE "id" = 1 LIMIT 10
            EOL;
        $this->assertSame($expectedSQL, $sql);
    }

    public function testDeleteBatchEscapesWhereBinds(): void
    {
        $db      = new MockConnection([]);
        $builder = new BaseBuilder('jobs', $db);

        $data = [
            ['id' => 1, 'name' => 'Derek J'],
            ['id' => 2, 'name' => 'Ahmadinejad'],
        ];

        // A user-controlled value that would break out of the query if it
        // were substituted into the SQL unescaped.
        $malicious = "anything' OR '1'='1";

        $db->shouldReturn('execute', new class () {});
        $builder->setData($data, null, 'data')
            ->onConstraint(['id' => 'id'])
            ->where('jobs.name', $malicious)
            ->deleteBatch();

        $query = $db->getLastQuery();
        $this->assertInstanceOf(MockQuery::class, $query);

        // The bound value must be escaped and quoted as a single string literal:
        // it is wrapped in single quotes and the inner quotes are doubled, so it
        // cannot break out of the literal and inject SQL.
        $this->assertStringContainsString("'anything'' OR ''1''", $query->getQuery());
        $this->assertStringNotContainsString("= anything' OR '1'='1", $query->getQuery());
    }

    public function testDeleteBatchEscapesMultipleWhereBinds(): void
    {
        $db      = new MockConnection([]);
        $builder = new BaseBuilder('jobs', $db);

        $data = [
            ['id' => 1, 'name' => 'Derek J'],
            ['id' => 2, 'name' => 'Ahmadinejad'],
        ];

        $db->shouldReturn('execute', new class () {});
        $builder->setData($data, null, 'data')
            ->onConstraint(['id' => 'id'])
            ->where('jobs.name', "anything' OR '1'='1")
            ->where('jobs.id', 1)
            ->deleteBatch();

        $query = $db->getLastQuery();
        $this->assertInstanceOf(MockQuery::class, $query);

        $this->assertStringContainsString('"jobs"."name" = \'anything\'\' OR \'\'1\'\' = \'\'1\'', $query->getQuery());
        $this->assertStringContainsString('"jobs"."id" = 1', $query->getQuery());
    }

    public function testDeleteBatchEscapesWhereInBinds(): void
    {
        $db      = new MockConnection([]);
        $builder = new BaseBuilder('jobs', $db);

        $data = [
            ['id' => 1, 'name' => 'Derek J'],
            ['id' => 2, 'name' => 'Ahmadinejad'],
        ];

        $db->shouldReturn('execute', new class () {});
        $builder->setData($data, null, 'data')
            ->onConstraint(['id' => 'id'])
            ->whereIn('jobs.name', ["anything' OR '1'='1", 'Ahmadinejad'])
            ->deleteBatch();

        $query = $db->getLastQuery();
        $this->assertInstanceOf(MockQuery::class, $query);

        $this->assertStringContainsString("IN ('anything'' OR ''1''=''1','Ahmadinejad')", $query->getQuery());
        $this->assertStringNotContainsString("IN anything' OR '1'='1", $query->getQuery());
    }
}
