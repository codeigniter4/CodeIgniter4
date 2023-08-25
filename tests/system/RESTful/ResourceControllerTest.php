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
use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Model;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockResourceController;
use Config\App;
use Psr\Log\NullLogger;
use Tests\Support\Models\UserModel;
use Tests\Support\RESTful\Worker;

/**
 * Exercise our ResourceController class.
 * We know the resource routing works, from RouterTest,
 * so we need to make sure that the methods routed to
 * return correct responses.
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState         disabled
 *
 * @internal
 *
 * @group SeparateProcess
 */
final class ResourceControllerTest extends CIUnitTestCase
{
    private CodeIgniter $codeigniter;

    /**
     * @var RouteCollection
     */
    protected $routes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices(true);
        $this->resetFactories();
    }

    private function createCodeigniter(): void
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        // Inject mock router.
        $this->routes = Services::routes();
        $this->routes->resource('work', ['controller' => '\\' . Worker::class]);
        Services::injectMock('routes', $this->routes);

        $config            = new App();
        $this->codeigniter = new MockCodeIgniter($config);

        $response = Services::response();
        $response->pretend();
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
        $_SERVER['argv'] = [
            'index.php',
            'work',
        ];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']    = '/work';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['index']), $error);
    }

    public function testResourceGetNew(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            'new',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/new';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['new']), $error);
    }

    public function testResourceGetEdit(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '1',
            'edit',
        ];
        $_SERVER['argc'] = 4;

        $_SERVER['REQUEST_URI']    = '/work/1/edit';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['edit']), $error);
    }

    public function testResourceGetOne(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '1',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/1';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['show']), $error);
    }

    public function testResourcePost(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
        ];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']    = '/work';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['create']), $error);
    }

    public function testResourcePatch(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/123';
        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['update']), $error);
    }

    public function testResourcePut(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/123';
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['update']), $error);
    }

    public function testResourceDelete(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/123';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $this->createCodeigniter();

        ob_start();
        $this->codeigniter->run($this->routes);
        $output = ob_get_clean();

        $error = json_decode($output)->messages->error;
        $this->assertStringContainsString(lang('RESTful.notImplemented', ['delete']), $error);
    }

    public function testModel(): void
    {
        $resource = new MockResourceController();
        $this->assertEmpty($resource->getModel());
        $this->assertEmpty($resource->getModelName());
    }

    public function testModelBogus(): void
    {
        $resource = new MockResourceController();

        $resource->setModel('Something');
        $this->assertEmpty($resource->getModel());
        $this->assertSame('Something', $resource->getModelName());
    }

    public function testModelByName(): void
    {
        $resource = new MockResourceController();
        $resource->setModel(UserModel::class);
        $this->assertInstanceOf(Model::class, $resource->getModel());
        $this->assertSame(UserModel::class, $resource->getModelName());
    }

    public function testModelByObject(): void
    {
        $resource = new MockResourceController();
        $model    = new UserModel();
        $resource->setModel($model);
        $this->assertInstanceOf(Model::class, $resource->getModel());

        // Note that the leading backslash is missing if we build it this way
        $this->assertSame(UserModel::class, $resource->getModelName());
    }

    public function testFormat(): void
    {
        $resource = new MockResourceController();
        $this->assertSame('json', $resource->getFormat());

        $resource->setFormat('Nonsense');
        $this->assertSame('json', $resource->getFormat());

        $resource->setFormat('xml');
        $this->assertSame('xml', $resource->getFormat());
    }

    public function testJSONFormatOutput(): void
    {
        $resource = new MockResourceController();

        $config = new App();
        $uri    = new URI();
        $agent  = new UserAgent();

        $request  = new IncomingRequest($config, $uri, '', $agent);
        $response = new Response($config);
        $logger   = new NullLogger();

        $resource->initController($request, $response, $logger);
        $resource->setFormat('json');

        $data = [
            'foo' => 'bar',
        ];

        $theResponse = $this->invoke($resource, 'respond', [$data]);
        $result      = $theResponse->getBody();

        $JSONFormatter = new JSONFormatter();
        $expected      = $JSONFormatter->format($data);

        $this->assertSame($expected, $result);
    }

    public function testXMLFormatOutput(): void
    {
        $resource = new MockResourceController();

        $config = new App();
        $uri    = new URI();
        $agent  = new UserAgent();

        $request  = new IncomingRequest($config, $uri, '', $agent);
        $response = new Response($config);
        $logger   = new NullLogger();

        $resource->initController($request, $response, $logger);
        $resource->setFormat('xml');

        $data = [
            'foo' => 'bar',
        ];

        $theResponse = $this->invoke($resource, 'respond', [$data]);
        $result      = $theResponse->getBody();

        $XMLFormatter = new XMLFormatter();
        $expected     = $XMLFormatter->format($data);

        $this->assertSame($expected, $result);
    }

    private function invoke(object $controller, string $method, array $args = [])
    {
        $method = $this->getPrivateMethodInvoker($controller, $method);

        return $method(...$args);
    }
}
