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

namespace CodeIgniter\RESTful;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Services;
use CodeIgniter\Model;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockResourcePresenter;
use Config\App;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\UserModel;
use Tests\Support\RESTful\Worker2;

/**
 * Exercise our core ResourcePresenter class.
 * We know the resource routing works, from RouterTest,
 * so we need to make sure that the methods routed to
 * return correct responses.
 *
 * @internal
 */
#[Group('SeparateProcess')]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
final class ResourcePresenterTest extends CIUnitTestCase
{
    private CodeIgniter $codeigniter;

    /**
     * @var RouteCollection
     */
    protected $routes;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices(true);
        $this->resetFactories();

        Services::injectMock('superglobals', new Superglobals());
    }

    private function createCodeigniter(): void
    {
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');

        // Inject mock router.
        $this->routes = service('routes');
        $this->routes->presenter('work', ['controller' => '\\' . Worker2::class]);
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

    public function testResourceGet(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
        ]);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/work');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertSame(lang('RESTful.notImplemented', ['index']), $output);
    }

    public function testResourceShow(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
            'show',
            '1',
        ]);
        service('superglobals')->setServer('argc', 4);

        service('superglobals')->setServer('REQUEST_URI', '/work/show/1');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['show']), (string) $output);
    }

    public function testResourceNew(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
            'new',
        ]);
        service('superglobals')->setServer('argc', 3);

        service('superglobals')->setServer('REQUEST_URI', '/work/new');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['new']), (string) $output);
    }

    public function testResourceCreate(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
            'create',
        ]);
        service('superglobals')->setServer('argc', 3);

        service('superglobals')->setServer('REQUEST_URI', '/work/create');
        service('superglobals')->setServer('REQUEST_METHOD', 'POST');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['create']), (string) $output);
    }

    public function testResourceRemove(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
            'remove',
            '123',
        ]);
        service('superglobals')->setServer('argc', 3);

        service('superglobals')->setServer('REQUEST_URI', '/work/remove/123');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['remove']), (string) $output);
    }

    public function testResourceDelete(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
            'delete',
            '123',
        ]);
        service('superglobals')->setServer('argc', 3);

        service('superglobals')->setServer('REQUEST_URI', '/work/delete/123');
        service('superglobals')->setServer('REQUEST_METHOD', 'POST');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['delete']), (string) $output);
    }

    public function testResourceEdit(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
            'edit',
            '1',
            'edit',
        ]);
        service('superglobals')->setServer('argc', 4);

        service('superglobals')->setServer('REQUEST_URI', '/work/edit/1');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['edit']), (string) $output);
    }

    public function testResourceUpdate(): void
    {
        service('superglobals')->setServer('argv', [
            'index.php',
            'work',
            'update',
            '123',
        ]);
        service('superglobals')->setServer('argc', 4);

        service('superglobals')->setServer('REQUEST_URI', '/work/update/123');
        service('superglobals')->setServer('REQUEST_METHOD', 'POST');

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['update']), (string) $output);
    }

    public function testModel(): void
    {
        $resource = new MockResourcePresenter();
        $this->assertEmpty($resource->getModel());
        $this->assertEmpty($resource->getModelName());
    }

    public function testModelBogus(): void
    {
        $resource = new MockResourcePresenter();

        $resource->setModel('Something');
        $this->assertEmpty($resource->getModel());
        $this->assertSame('Something', $resource->getModelName());
    }

    public function testModelByName(): void
    {
        $resource = new MockResourcePresenter();
        $resource->setModel(UserModel::class);
        $this->assertInstanceOf(Model::class, $resource->getModel());
        $this->assertSame(UserModel::class, $resource->getModelName());
    }

    public function testModelByObject(): void
    {
        $resource = new MockResourcePresenter();
        $model    = new UserModel();
        $resource->setModel($model);
        $this->assertInstanceOf(Model::class, $resource->getModel());

        // Note that the leading backslash is missing if we build it this way
        $this->assertSame(UserModel::class, $resource->getModelName());
    }

    public function testChangeSetModelByObject(): void
    {
        $resource = new MockResourcePresenter();
        $resource->setModel(UserModel::class);
        $this->assertInstanceOf(Model::class, $resource->getModel());
        $this->assertSame(UserModel::class, $resource->getModelName());

        $model = new EntityModel();
        $resource->setModel($model);
        $this->assertInstanceOf(Model::class, $resource->getModel());
        $this->assertSame(EntityModel::class, $resource->getModelName());
    }

    public function testChangeSetModelByName(): void
    {
        $resource = new MockResourcePresenter();
        $resource->setModel(UserModel::class);
        $this->assertInstanceOf(Model::class, $resource->getModel());
        $this->assertSame(UserModel::class, $resource->getModelName());

        $resource->setModel(EntityModel::class);
        $this->assertInstanceOf(Model::class, $resource->getModel());
        $this->assertSame(EntityModel::class, $resource->getModelName());
    }
}
