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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class BindingLifecycleTest extends CIUnitTestCase
{
    #[DataProvider('provideCompiledQuery')]
    public function testCompiledQueryClearsBindingsOnReset(string $method, string $sql): void
    {
        $builder = new BaseBuilder('jobs', new MockConnection([]));

        for ($id = 1; $id <= 3; $id++) {
            $this->prepareQuery($builder, $method, $id);

            $this->assertSameSql(sprintf($sql, $id), $builder->{$method}());
            $this->assertSame([], $builder->getBinds());
        }
    }

    #[DataProvider('provideCompiledQuery')]
    public function testCompiledQueryPreservesBindingsWithoutReset(string $method, string $sql): void
    {
        $builder = new BaseBuilder('jobs', new MockConnection([]));
        $this->prepareQuery($builder, $method, 1);
        $binds = $builder->getBinds();

        for ($i = 0; $i < 2; $i++) {
            $this->assertSameSql(sprintf($sql, 1), $builder->{$method}(false));
            $this->assertSame($binds, $builder->getBinds());
        }

        $this->assertSameSql(sprintf($sql, 1), $builder->{$method}());
        $this->assertSame([], $builder->getBinds());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideCompiledQuery(): iterable
    {
        yield 'select' => ['getCompiledSelect', 'SELECT * FROM "jobs" WHERE "id" = %d'];

        yield 'insert' => ['getCompiledInsert', 'INSERT INTO "jobs" ("name", "id") VALUES (\'O\'\'Reilly\', %d)'];

        yield 'update' => ['getCompiledUpdate', 'UPDATE "jobs" SET "name" = \'O\'\'Reilly\' WHERE "id" = %d'];

        yield 'delete' => ['getCompiledDelete', 'DELETE FROM "jobs" WHERE "id" = %d'];
    }

    private function prepareQuery(BaseBuilder $builder, string $method, int $id): void
    {
        if ($method === 'getCompiledInsert' || $method === 'getCompiledUpdate') {
            $builder->set('name', "O'Reilly");
        }

        if ($method === 'getCompiledInsert') {
            $builder->set('id', $id);
        } else {
            $builder->where('id', $id);
        }
    }
}
