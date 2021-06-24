<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class DeleteTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testDelete()
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

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testGetCompiledDelete()
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

    public function testGetCompiledDeleteWithLimit()
    {
        $builder = $this->db->table('jobs');

        $sql = $builder->where('id', 1)->limit(10)->getCompiledDelete();

        $expectedSQL = <<<'EOL'
            DELETE FROM "jobs"
            WHERE "id" = 1 LIMIT 10
            EOL;
        $this->assertSame($expectedSQL, $sql);
    }
}
