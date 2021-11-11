<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\RESTful;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockResourcePresenter;
use Config\App;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\UserModel;

/**
 * Exercise our core ResourcePresenter class.
 * We know the resource routing works, from RouterTest,
 * so we need to make sure that the methods routed to
 * return correct responses.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState         disabled
 *
 * @internal
 */
final class ResourcePresenterTest extends CIUnitTestCase
{
    /**
     * @var CodeIgniter
     */
    protected $codeigniter;

    /**
     * @var RouteCollection
     */
    protected $routes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        // Inject mock router.
        $this->routes = Services::routes();
        $this->routes->presenter('work', ['controller' => 'Tests\Support\RESTful\Worker2']);
        Services::injectMock('routes', $this->routes);

        $config            = new App();
        $this->codeigniter = new MockCodeIgniter($config);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (count(ob_list_handlers()) > 1) {
            ob_end_clean();
        }
    }

    public function testResourceGet()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
        ];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']    = '/work';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertSame(lang('RESTful.notImplemented', ['index']), $output);
    }

    public function testResourceShow()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'show',
            '1',
        ];
        $_SERVER['argc'] = 4;

        $_SERVER['REQUEST_URI']    = '/work/show/1';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['show']), $output);
    }

    public function testResourceNew()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'new',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/new';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['new']), $output);
    }

    public function testResourceCreate()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'create',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/create';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['create']), $output);
    }

    public function testResourceRemove()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'remove',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/remove/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['remove']), $output);
    }

    public function testResourceDelete()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'delete',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/delete/123';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['delete']), $output);
    }

    public function testResourceEdit()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'edit',
            '1',
            'edit',
        ];
        $_SERVER['argc'] = 4;

        $_SERVER['REQUEST_URI']    = '/work/edit/1';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['edit']), $output);
    }

    public function testResourceUpdate()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'update',
            '123',
        ];
        $_SERVER['argc'] = 4;

        $_SERVER['REQUEST_URI']    = '/work/update/123';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['update']), $output);
    }

    public function testModel()
    {
        $resource = new MockResourcePresenter();
        $this->assertEmpty($resource->getModel());
        $this->assertEmpty($resource->getModelName());
    }

    public function testModelBogus()
    {
        $resource = new MockResourcePresenter();

        $resource->setModel('Something');
        $this->assertEmpty($resource->getModel());
        $this->assertSame('Something', $resource->getModelName());
    }

    public function testModelByName()
    {
        $resource = new MockResourcePresenter();
        $resource->setModel('\Tests\Support\Models\UserModel');
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());
        $this->assertSame('\Tests\Support\Models\UserModel', $resource->getModelName());
    }

    public function testModelByObject()
    {
        $resource = new MockResourcePresenter();
        $model    = new UserModel();
        $resource->setModel($model);
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());

        // Note that the leading backslash is missing if we build it this way
        $this->assertSame('Tests\Support\Models\UserModel', $resource->getModelName());
    }

    public function testChangeSetModelByObject()
    {
        $resource = new MockResourcePresenter();
        $resource->setModel('\Tests\Support\Models\UserModel');
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());
        $this->assertSame('\Tests\Support\Models\UserModel', $resource->getModelName());

        $model = new EntityModel();
        $resource->setModel($model);
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());
        $this->assertSame('Tests\Support\Models\EntityModel', $resource->getModelName());
    }

    public function testChangeSetModelByName()
    {
        $resource = new MockResourcePresenter();
        $resource->setModel('\Tests\Support\Models\UserModel');
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());
        $this->assertSame('\Tests\Support\Models\UserModel', $resource->getModelName());

        $resource->setModel('\Tests\Support\Models\EntityModel');
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());
        $this->assertSame('\Tests\Support\Models\EntityModel', $resource->getModelName());
    }
}
