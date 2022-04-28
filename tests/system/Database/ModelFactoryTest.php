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

    public function testCreateSeparateInstances()
    {
        $basenameModel  = ModelFactory::get('JobModel', false);
        $namespaceModel = ModelFactory::get(JobModel::class, false);

        $this->assertInstanceOf(JobModel::class, $basenameModel);
        $this->assertInstanceOf(JobModel::class, $namespaceModel);
        $this->assertNotSame($basenameModel, $namespaceModel);
    }

    public function testCreateSharedInstance()
    {
        $basenameModel  = ModelFactory::get('JobModel', true);
        $namespaceModel = ModelFactory::get(JobModel::class, true);

        $this->assertSame($basenameModel, $namespaceModel);
    }

    public function testInjection()
    {
        ModelFactory::injectMock('Banana', new JobModel());

        $this->assertInstanceOf(JobModel::class, ModelFactory::get('Banana'));
    }

    public function testReset()
    {
        ModelFactory::injectMock('Banana', new JobModel());

        ModelFactory::reset();

        $this->assertNull(ModelFactory::get('Banana'));
    }

    public function testBasenameReturnsExistingNamespaceInstance()
    {
        ModelFactory::injectMock(UserModel::class, new JobModel());

        $basenameModel = ModelFactory::get('UserModel');

        $this->assertInstanceOf(JobModel::class, $basenameModel);
    }
}
