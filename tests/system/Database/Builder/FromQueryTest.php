<?php

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

/**
 * @internal
 */
final class FromQueryTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleFrom()
    {
        $builder = new BaseBuilder('test', $this->db);

        $sql = <<<'SQL'
                SELECT
                id,
                'text' name,
                IF(id=1,1,0) AS if_column,
                (SELECT MAX(id) FROM user LEFT JOIN job ON job.id = user.id WHERE id > 0) AS sub_query,
                `escape1`,
                'escape2',
                "escape3",
                'two word' AS `two words`
                FROM test_table
                INNER JOIN sometable ON sometable.id = test_table.id
                WHERE id IN (SELECT MAX(id) FROM user LEFT JOIN job ON job.id = user.id WHERE id > 0)
            SQL;

        $query = $builder->testMode()->fromQuery($sql)->insertBatch();

        $expected = 'INSERT INTO "test" ("id", "name", "if_column", "sub_query", "escape1", "escape2", "escape3", "two words")';

        $this->assertStringContainsString($expected, $query);
    }
}
