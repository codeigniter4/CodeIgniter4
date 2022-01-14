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

use CodeIgniter\Exceptions\ModelException;
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
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('You cannot use `getCompiledInsert()` in `Tests\Support\Models\UserObjModel`.');

        $this->createModel(UserObjModel::class);

        $sql = $this->model
            ->set('name', 'Mark')
            ->set('email', 'mark@example.com')
            ->getCompiledInsert();
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5549
     */
    public function testGetCompiledUpdate(): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('You cannot use `getCompiledUpdate()` in `Tests\Support\Models\UserObjModel`.');

        $this->createModel(UserObjModel::class);

        $sql = $this->model
            ->set('name', 'Mark')
            ->set('email', 'mark@example.com')
            ->getCompiledUpdate();
    }
}
