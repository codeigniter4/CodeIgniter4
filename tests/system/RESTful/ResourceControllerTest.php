<?php

namespace CodeIgniter\RESTful;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Services;
use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockResourceController;
use Config\App;
use Psr\Log\NullLogger;
use Tests\Support\Models\UserModel;

/**
 * Exercise our ResourceController class.
 * We know the resource routing works, from RouterTest,
 * so we need to make sure that the methods routed to
 * return correct responses.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState         disabled
 *
 * @internal
 */
final class ResourceControllerTest extends CIUnitTestCase
{
    /**
     * @var CodeIgniter
     */
    protected $codeigniter;

    /**
     * @var \CodeIgniter\Router\RoutesCollection
     */
    protected $routes;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset();

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        // Inject mock router.
        $this->routes = Services::routes();
        $this->routes->resource('work', ['controller' => '\Tests\Support\RESTful\Worker']);
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

    //--------------------------------------------------------------------

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

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['index']), $output);
    }

    public function testResourceGetNew()
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

    public function testResourceGetEdit()
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

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['edit']), $output);
    }

    public function testResourceGetOne()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '1',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/1';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['show']), $output);
    }

    public function testResourcePost()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
        ];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']    = '/work';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['create']), $output);
    }

    public function testResourcePatch()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/123';
        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['patch']), $output);
    }

    public function testResourcePut()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/123';
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['put']), $output);
    }

    public function testResourceDelete()
    {
        $_SERVER['argv'] = [
            'index.php',
            'work',
            '123',
        ];
        $_SERVER['argc'] = 3;

        $_SERVER['REQUEST_URI']    = '/work/123';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($this->routes);
        $output = ob_get_clean();

        $this->assertStringContainsString(lang('RESTful.notImplemented', ['delete']), $output);
    }

    //--------------------------------------------------------------------
    public function testModel()
    {
        $resource = new MockResourceController();
        $this->assertEmpty($resource->getModel());
        $this->assertEmpty($resource->getModelName());
    }

    public function testModelBogus()
    {
        $resource = new MockResourceController();

        $resource->setModel('Something');
        $this->assertEmpty($resource->getModel());
        $this->assertSame('Something', $resource->getModelName());
    }

    public function testModelByName()
    {
        $resource = new MockResourceController();
        $resource->setModel('\Tests\Support\Models\UserModel');
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());
        $this->assertSame('\Tests\Support\Models\UserModel', $resource->getModelName());
    }

    public function testModelByObject()
    {
        $resource = new MockResourceController();
        $model    = new UserModel();
        $resource->setModel($model);
        $this->assertInstanceOf('CodeIgniter\Model', $resource->getModel());

        // Note that the leading backslash is missing if we build it this way
        $this->assertSame('Tests\Support\Models\UserModel', $resource->getModelName());
    }

    //--------------------------------------------------------------------
    public function testFormat()
    {
        $resource = new MockResourceController();
        $this->assertSame('json', $resource->getFormat());

        $resource->setFormat('Nonsense');
        $this->assertSame('json', $resource->getFormat());

        $resource->setFormat('xml');
        $this->assertSame('xml', $resource->getFormat());
    }

    //--------------------------------------------------------------------
    public function testJSONFormatOutput()
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

        $theResponse = $resource->respond($data);
        $result      = $theResponse->getBody();

        $JSONFormatter = new JSONFormatter();
        $expected      = $JSONFormatter->format($data);

        $this->assertSame($expected, $result);
    }

    //--------------------------------------------------------------------
    public function testXMLFormatOutput()
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

        $theResponse = $resource->respond($data);
        $result      = $theResponse->getBody();

        $XMLFormatter = new XMLFormatter();
        $expected     = $XMLFormatter->format($data);

        $this->assertSame($expected, $result);
    }
}
