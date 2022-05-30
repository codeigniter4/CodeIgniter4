<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use Config\Modules;
use Tests\Support\Filters\Customfilter;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class CodeIgniterTest extends CIUnitTestCase
{
    private CodeIgniter $codeigniter;
    protected $routes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $this->codeigniter = new MockCodeIgniter(new App());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (count(ob_list_handlers()) > 1) {
            ob_end_clean();
        }

        $this->resetServices();
    }

    public function testRunEmptyDefaultRoute()
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

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

    public function testRun404Override()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = Services::routes();
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Controllers\Hello::index');
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($routes);
        $output = ob_get_clean();

        $this->assertStringContainsString('Hello', $output);
    }

    public function testRun404OverrideControllerReturnsResponse()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = Services::routes();
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Controllers\Popcorn::pop');
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run($routes);
        $output = ob_get_clean();

        $this->assertStringContainsString('Oops', $output);
    }

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

    public function testControllersCanReturnString()
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static fn ($segment) => 'You want to see "' . esc($segment) . '" page.');
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('You want to see "about" page.', $output);
    }

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

    public function testControllersRunFilterByClassName()
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/about', static fn () => Services::request()->url, ['filter' => Customfilter::class]);

        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('http://hellowworld.com', $output);
    }

    public function testResponseConfigEmpty()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $response = Services::response(null, false);

        $this->assertInstanceOf(Response::class, $response);
    }

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

    public function testRunForceSecure()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $config = new App();

        $config->forceGlobalSecureRequests = true;

        $codeigniter = new MockCodeIgniter($config);
        $codeigniter->setContext('web');

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

    public function testStoresPreviousURL()
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $router = Services::router(null, Services::request(), false);
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();

        $this->assertArrayHasKey('_ci_previous_url', $_SESSION);
        $this->assertSame('http://example.com/index.php', $_SESSION['_ci_previous_url']);
    }

    public function testNotStoresPreviousURL()
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

        $this->assertArrayNotHasKey('_ci_previous_url', $_SESSION);
    }

    public function testNotStoresPreviousURLByCheckingContentType()
    {
        $_SERVER['argv'] = ['index.php', 'image'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/image';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('image', static function () {
            $response = Services::response();

            return $response->setContentType('image/jpeg', '');
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();

        $this->assertArrayNotHasKey('_ci_previous_url', $_SESSION);
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

    public function testRunCLIRoute()
    {
        $_SERVER['argv'] = ['index.php', 'cli'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/cli';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'CLI';

        $routes = Services::routes();
        $routes->cli('cli', '\Tests\Support\Controllers\Popcorn::index');

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Method Not Allowed', $output);
    }

    public function testSpoofRequestMethodCanUsePUT()
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        $_SERVER['REQUEST_URI']     = '/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        $_POST['_method'] = 'PUT';

        $routes = \Config\Services::routes();
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->post('/', 'Home::index');
        $routes->put('/', 'Home::index');

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();

        $this->assertSame('put', Services::request()->getMethod());
    }

    public function testSpoofRequestMethodCannotUseGET()
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        $_SERVER['REQUEST_URI']     = '/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        $_POST['_method'] = 'GET';

        $routes = \Config\Services::routes();
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->post('/', 'Home::index');
        $routes->get('/', 'Home::index');

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        ob_get_clean();

        $this->assertSame('post', Services::request()->getMethod());
    }
}
