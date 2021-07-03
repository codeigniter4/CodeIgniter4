<?php

namespace CodeIgniter;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use Config\Modules;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class CodeIgniterTest extends CIUnitTestCase
{
    /**
     * @var CodeIgniter
     */
    protected $codeigniter;

    protected $routes;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();
        Services::reset();

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $this->codeigniter = new MockCodeIgniter(new App());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (count(ob_list_handlers()) > 1) {
            ob_end_clean();
        }
    }

    //--------------------------------------------------------------------

    public function testRunEmptyDefaultRoute()
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    //--------------------------------------------------------------------

    public function testRunClosureRoute()
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static function ($segment) {
            echo 'You want to see "' . esc($segment) . '" page.';
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('You want to see "about" page.', $output);
    }

    //--------------------------------------------------------------------

    public function testRun404Override()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = Services::routes();
        $routes->setAutoRoute(false);
        $routes->set404Override('Home::index');
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    //--------------------------------------------------------------------

    public function testRun404OverrideByClosure()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = new RouteCollection(Services::locator(), new Modules());
        $routes->setAutoRoute(false);
        $routes->set404Override(static function () {
            echo '404 Override by Closure.';
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($routes);
        $output = ob_get_clean();

        $this->assertStringContainsString('404 Override by Closure.', $output);
    }

    //--------------------------------------------------------------------

    public function testControllersCanReturnString()
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static function ($segment) {
            return 'You want to see "' . esc($segment) . '" page.';
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('You want to see "about" page.', $output);
    }

    //--------------------------------------------------------------------

    public function testControllersCanReturnResponseObject()
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static function ($segment) {
            $response = Services::response();
            $string = "You want to see 'about' page.";

            return $response->setBody($string);
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString("You want to see 'about' page.", $output);
    }

    //--------------------------------------------------------------------

    public function testResponseConfigEmpty()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $response = Services::response(null, false);

        $this->assertInstanceOf('\CodeIgniter\HTTP\Response', $response);
    }

    //--------------------------------------------------------------------

    public function testRoutesIsEmpty()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $router = Services::router(null, Services::request(), false);
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    public function testTransfersCorrectHTTPVersion()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2.0';

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');

        $this->assertSame('2.0', $response->getProtocolVersion());
    }

    public function testIgnoringErrorSuppressedByAt()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        ob_start();
        @unlink('inexistent-file');
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    //--------------------------------------------------------------------

    public function testRunForceSecure()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $config = new App();

        $config->forceGlobalSecureRequests = true;

        $codeigniter = new MockCodeIgniter($config);

        $this->getPrivateMethodInvoker($codeigniter, 'getRequestObject')();
        $this->getPrivateMethodInvoker($codeigniter, 'getResponseObject')();

        $response = $this->getPrivateProperty($codeigniter, 'response');
        $this->assertNull($response->header('Location'));

        ob_start();
        $codeigniter->useSafeOutput(true)->run();
        ob_get_clean();

        $this->assertSame('https://example.com/', $response->header('Location')->getValue());
    }

    public function testRunRedirectionWithNamed()
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/example';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/named', static function () {
        }, ['as' => 'name']);
        $routes->addRedirect('example', 'name');

        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();
        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame('http://example.com/pages/named', $response->header('Location')->getValue());
    }

    public function testRunRedirectionWithURI()
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/example';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/uri', static function () {
        });
        $routes->addRedirect('example', 'pages/uri');

        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();
        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame('http://example.com/pages/uri', $response->header('Location')->getValue());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3041
     */
    public function testRunRedirectionWithURINotSet()
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/example';

        // Inject mock router.
        $routes = Services::routes();
        $routes->addRedirect('example', 'pages/notset');

        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();
        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame('http://example.com/pages/notset', $response->header('Location')->getValue());
    }

    public function testRunRedirectionWithHTTPCode303()
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/example';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        // Inject mock router.
        $routes = Services::routes();
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame(303, $response->getStatusCode());
    }

    public function testRunRedirectionWithHTTPCode301()
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/example';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'GET';

        // Inject mock router.
        $routes = Services::routes();
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();
        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame(301, $response->getStatusCode());
    }

    /**
     * The method after all test, reset Servces:: config
     * Can't use static::tearDownAfterClass. This will cause a buffer exception
     * need improve
     */
    public function testRunDefaultRoute()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }
}
