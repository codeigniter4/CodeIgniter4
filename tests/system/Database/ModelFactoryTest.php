<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\UserModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class ModelFactoryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        ModelFactory::reset();
    }

    public function testCreateSeparateInstances(): void
    {
        $basenameModel  = ModelFactory::get('JobModel', false);
        $namespaceModel = ModelFactory::get(JobModel::class, false);

        $this->assertInstanceOf(JobModel::class, $basenameModel);
        $this->assertInstanceOf(JobModel::class, $namespaceModel);
        $this->assertNotSame($basenameModel, $namespaceModel);
    }

    public function testCreateSharedInstance(): void
    {
        $basenameModel  = ModelFactory::get('JobModel', true);
        $namespaceModel = ModelFactory::get(JobModel::class, true);

        $this->assertSame($basenameModel, $namespaceModel);
    }

    public function testInjection(): void
    {
        ModelFactory::injectMock('Banana', new JobModel());

        $this->assertInstanceOf(JobModel::class, ModelFactory::get('Banana'));
    }

    public function testReset(): void
    {
        ModelFactory::injectMock('Banana', new JobModel());

        ModelFactory::reset();

        $this->assertNull(ModelFactory::get('Banana'));
    }

    public function testBasenameDoesNotReturnExistingNamespaceInstance(): void
    {
        ModelFactory::injectMock(UserModel::class, new JobModel());

        $basenameModel = ModelFactory::get('UserModel');

        $this->assertInstanceOf(UserModel::class, $basenameModel);
    }
}
