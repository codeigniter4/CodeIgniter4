<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Models;

use CodeIgniter\Model;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use Tests\Support\Models\UserObjModel;

/**
 * @internal
 */
final class GetCompiledModelTest extends CIUnitTestCase
{
    /**
     * @var Model
     */
    private $model;

    /**
     * Create an instance of Model for use in testing.
     */
    private function createModel(string $modelName): Model
    {
        $db          = new MockConnection([]);
        $this->model = new $modelName($db);

        return $this->model;
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5549
     */
    public function testGetCompiledInsert(): void
    {
        $this->createModel(UserObjModel::class);

        $sql = $this->model
            ->set('name', 'Mark')
            ->set('email', 'mark@example.com')
            ->getCompiledInsert();

        $this->assertSame(
            <<<'SQL'
                INSERT INTO "user" ("name", "email") VALUES ('Mark', 'mark@example.com')
                SQL,
            $sql
        );
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5549
     */
    public function testGetCompiledUpdate(): void
    {
        $this->createModel(UserObjModel::class);

        $sql = $this->model
            ->set('name', 'Mark')
            ->set('email', 'mark@example.com')
            ->getCompiledUpdate();

        $this->assertSame(
            <<<'SQL'
                UPDATE "user" SET "name" = 'Mark', "email" = 'mark@example.com'
                SQL,
            $sql
        );
    }
}
