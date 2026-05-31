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
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ValuePluckTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testValueReturnsSqlInTestMode(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->value('name');

        $expectedSQL = 'SELECT "name" AS "CI_value" FROM "jobs" WHERE "id" > 3  LIMIT 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    public function testPluckReturnsSqlInTestMode(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->pluck('name');

        $expectedSQL = 'SELECT "name" AS "CI_value" FROM "jobs" WHERE "id" > 3';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    public function testPluckWithKeyReturnsSqlInTestMode(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->where('id >', 3)->pluck('name', 'id');

        $expectedSQL = 'SELECT "name" AS "CI_value", "id" AS "CI_key" FROM "jobs" WHERE "id" > 3';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
    }

    public function testValueIgnoresExistingSelectAndRestoresItWhenResetFalse(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->select('description')
            ->where('id >', 3)
            ->value('name', false);

        $expectedSQL = 'SELECT "name" AS "CI_value" FROM "jobs" WHERE "id" > 3  LIMIT 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
        $this->assertSame(
            'SELECT "description" FROM "jobs" WHERE "id" > 3',
            str_replace("\n", ' ', $builder->getCompiledSelect(false)),
        );
    }

    public function testPluckRestoresExistingSelectWhenResetFalse(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode();

        $answer = $builder->select('description')
            ->where('id >', 3)
            ->pluck('name', null, false);

        $expectedSQL = 'SELECT "name" AS "CI_value" FROM "jobs" WHERE "id" > 3';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $answer));
        $this->assertSame(
            'SELECT "description" FROM "jobs" WHERE "id" > 3',
            str_replace("\n", ' ', $builder->getCompiledSelect(false)),
        );
    }

    public function testValueAndPluckReturnEmptyResultsWhenQueryFails(): void
    {
        $db = new MockConnection([]);
        $db->shouldReturn('execute', false);

        $this->assertNull((new BaseBuilder('jobs', $db))->value('name'));
        $this->assertSame([], (new BaseBuilder('jobs', $db))->pluck('name'));
    }

    public function testValueRejectsEmptyColumnName(): void
    {
        $this->expectException(DataException::class);

        (new BaseBuilder('jobs', $this->db))->value('');
    }

    public function testValueRejectsRawSqlExpression(): void
    {
        $this->expectException(DataException::class);

        (new BaseBuilder('jobs', $this->db))->value('COUNT(*)');
    }

    public function testValueRejectsWildcardColumnName(): void
    {
        $this->expectException(DataException::class);

        (new BaseBuilder('jobs', $this->db))->value('jobs.*');
    }

    public function testPluckRejectsRawSqlKeyExpression(): void
    {
        $this->expectException(DataException::class);

        (new BaseBuilder('jobs', $this->db))->pluck('name', 'CONCAT(id, name)');
    }
}
