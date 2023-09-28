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
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Router\Exceptions\RedirectException;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use Config\Cache;
use Config\Filters as FiltersConfig;
use Config\Modules;
use Config\Routing;
use Tests\Support\Filters\Customfilter;

/**
 * @runTestsInSeparateProcesses
 *
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
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

        $response = Services::response();
        $response->pretend();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
    }

    public function testRunEmptyDefaultRoute(): void
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    public function testOutputBufferingControl()
    {
        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        // 1 phpunit output buffering level
        $this->assertSame(1, ob_get_level());
    }

    public function testRunEmptyDefaultRouteReturnResponse(): void
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        $response = $this->codeigniter->useSafeOutput(true)->run(null, true);

        $this->assertStringContainsString('Welcome to CodeIgniter', $response->getBody());
    }

    public function testRunClosureRoute(): void
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static function ($segment): void {
            echo 'You want to see "' . esc($segment) . '" page.';
        });
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('You want to see "about" page.', $output);
    }

    public function testRun404Override(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = Services::routes();
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Controllers\Hello::index');
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run($routes);
        $output = ob_get_clean();

        $this->assertStringContainsString('Hello', $output);
    }

    public function testRun404OverrideControllerReturnsResponse(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = Services::routes();
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Controllers\Popcorn::pop');
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run($routes, true);

        $this->assertStringContainsString('Oops', $response->getBody());
        $this->assertSame(567, $response->getStatusCode());
    }

    public function testRun404OverrideReturnResponse(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = Services::routes();
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Controllers\Popcorn::pop');
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        $response = $this->codeigniter->useSafeOutput(true)->run($routes, true);

        $this->assertStringContainsString('Oops', $response->getBody());
    }

    public function testRun404OverrideByClosure(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = new RouteCollection(Services::locator(), new Modules(), new Routing());
        $routes->setAutoRoute(false);
        $routes->set404Override(static function (): void {
            echo '404 Override by Closure.';
        });
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run($routes);
        $output = ob_get_clean();

        $this->assertStringContainsString('404 Override by Closure.', $output);
    }

    public function testControllersCanReturnString(): void
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add(
            'pages/(:segment)',
            static fn ($segment) => 'You want to see "' . esc($segment) . '" page.'
        );
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('You want to see "about" page.', $output);
    }

    public function testControllersCanReturnResponseObject(): void
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static function ($segment) {
            $response = Services::response();
            $string   = "You want to see 'about' page.";

            return $response->setBody($string);
        });
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString("You want to see 'about' page.", $output);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6358
     */
    public function testControllersCanReturnDownloadResponseObject(): void
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static function ($segment) {
            $response = Services::response();

            return $response->download('some.txt', 'some text', true);
        });
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertSame('some text', $output);
    }

    public function testRunExecuteFilterByClassName(): void
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add(
            'pages/about',
            static fn () => Services::incomingrequest()->getBody(),
            ['filter' => Customfilter::class]
        );

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('http://hellowworld.com', $output);

        $this->resetServices();
    }

    public function testRegisterSameFilterTwiceWithDifferentArgument()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('"test-customfilter" already has arguments: null');

        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $routes = Services::routes();
        $routes->add(
            'pages/about',
            static fn () => Services::incomingrequest()->getBody(),
            // Set filter with no argument.
            ['filter' => 'test-customfilter']
        );

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        /** @var FiltersConfig $filterConfig */
        $filterConfig          = config('Filters');
        $filterConfig->filters = [
            // Set filter with argument.
            'test-customfilter:arg1' => [
                'before' => ['pages/*'],
            ],
        ];
        Services::filters($filterConfig);

        $this->codeigniter->run();

        $this->resetServices();
    }

    public function testDisableControllerFilters(): void
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add(
            'pages/about',
            static fn () => Services::incomingrequest()->getBody(),
            ['filter' => Customfilter::class]
        );
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->disableFilters();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('', $output);

        $this->resetServices();
    }

    public function testResponseConfigEmpty(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $response = Services::response(null, false);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRoutesIsEmpty(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $router = Services::router(null, Services::incomingrequest(), false);
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    public function testTransfersCorrectHTTPVersion(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2.0';

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');

        $this->assertSame('2.0', $response->getProtocolVersion());
    }

    public function testSupportsHttp3(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/3.0';

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');

        $this->assertSame('3.0', $response->getProtocolVersion());
    }

    public function testIgnoringErrorSuppressedByAt(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        ob_start();
        @unlink('inexistent-file');
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    public function testRunForceSecure(): void
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

        $response = $codeigniter->run(null, true);

        $this->assertSame('https://example.com/index.php/', $response->header('Location')->getValue());
    }

    public function testRunRedirectionWithNamed(): void
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/example';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/named', static function (): void {
        }, ['as' => 'name']);
        $routes->addRedirect('example', 'name');

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();
        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame('http://example.com/pages/named', $response->header('Location')->getValue());
    }

    public function testRunRedirectionWithURI(): void
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/example';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/uri', static function (): void {
        });
        $routes->addRedirect('example', 'pages/uri');

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();
        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame('http://example.com/pages/uri', $response->header('Location')->getValue());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3041
     */
    public function testRunRedirectionWithGET(): void
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/example';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'GET';

        // Inject mock router.
        $routes = Services::routes();
        // addRedirect() sets status code 302 by default.
        $routes->addRedirect('example', 'pages/notset');

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame('http://example.com/pages/notset', $response->header('Location')->getValue());
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testRunRedirectionWithGETAndHTTPCode301(): void
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/example';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'GET';

        // Inject mock router.
        $routes = Services::routes();
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame(301, $response->getStatusCode());
    }

    public function testRunRedirectionWithPOSTAndHTTPCode301(): void
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/example';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        // Inject mock router.
        $routes = Services::routes();
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame(301, $response->getStatusCode());
    }

    /**
     * test for deprecated \CodeIgniter\Router\Exceptions\RedirectException for backward compatibility
     */
    public function testRedirectExceptionDeprecated(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $routes = Services::routes();
        $routes->get('/', static function () {
            throw new RedirectException('redirect-exception', 503);
        });

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run($routes, true);

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame('http://example.com/redirect-exception', $response->getHeaderLine('Location'));
    }

    public function testStoresPreviousURL(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        // Inject mock router.
        $router = Services::router(null, Services::incomingrequest(), false);
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertArrayHasKey('_ci_previous_url', $_SESSION);
        $this->assertSame('http://example.com/index.php/', $_SESSION['_ci_previous_url']);
    }

    public function testNotStoresPreviousURL(): void
    {
        $_SERVER['argv'] = ['index.php', 'example'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/example';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'GET';

        // Inject mock router.
        $routes = Services::routes();
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertArrayNotHasKey('_ci_previous_url', $_SESSION);
    }

    public function testNotStoresPreviousURLByCheckingContentType(): void
    {
        $_SERVER['argv'] = ['index.php', 'image'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/image';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('image', static function () {
            $response = Services::response();

            return $response->setContentType('image/jpeg', '');
        });
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertArrayNotHasKey('_ci_previous_url', $_SESSION);
    }

    /**
     * The method after all test, reset Servces:: config
     * Can't use static::tearDownAfterClass. This will cause a buffer exception
     * need improve
     */
    public function testRunDefaultRoute(): void
    {
        $_SERVER['argv'] = ['index.php', '/'];
        $_SERVER['argc'] = 2;

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', $output);
    }

    public function testRunCLIRoute(): void
    {
        $_SERVER['argv'] = ['index.php', 'cli'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI']     = '/cli';
        $_SERVER['SCRIPT_NAME']     = 'public/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'CLI';

        $routes = Services::routes();
        $routes->cli('cli', '\Tests\Support\Controllers\Popcorn::index');

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Method Not Allowed', $output);
    }

    public function testSpoofRequestMethodCanUsePUT(): void
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        $_SERVER['REQUEST_URI']     = '/';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        $_POST['_method'] = 'PUT';

        $routes = \Config\Services::routes();
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->post('/', 'Home::index');
        $routes->put('/', 'Home::index');

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertSame('put', Services::incomingrequest()->getMethod());
    }

    public function testSpoofRequestMethodCannotUseGET(): void
    {
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['argc'] = 1;

        $_SERVER['REQUEST_URI']     = '/';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        $_POST['_method'] = 'GET';

        $routes = \Config\Services::routes();
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->post('/', 'Home::index');
        $routes->get('/', 'Home::index');

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertSame('post', Services::incomingrequest()->getMethod());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6281
     */
    public function testPageCacheSendSecureHeaders(): void
    {
        // Suppress command() output
        CITestStreamFilter::registration();
        CITestStreamFilter::addErrorFilter();
        CITestStreamFilter::addOutputFilter();

        // Clear Page cache
        command('cache:clear');

        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $routes = Services::routes();
        $routes->add('test', static function () {
            CodeIgniter::cache(3600);

            $response = Services::response();
            $string   = 'This is a test page. Elapsed time: {elapsed_time}';

            return $response->setBody($string);
        });
        $router = Services::router($routes, Services::incomingrequest());
        Services::injectMock('router', $router);

        /** @var FiltersConfig $filterConfig */
        $filterConfig                   = config('Filters');
        $filterConfig->globals['after'] = ['secureheaders'];
        Services::filters($filterConfig);

        // The first response to be cached.
        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('This is a test page', $output);
        $response = Services::response();
        $headers  = $response->headers();
        $this->assertArrayHasKey('X-Frame-Options', $headers);

        // The second response from the Page cache.
        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('This is a test page', $output);
        $response = Services::response();
        $headers  = $response->headers();
        $this->assertArrayHasKey('X-Frame-Options', $headers);

        // Clear Page cache
        command('cache:clear');

        // Remove stream filters
        CITestStreamFilter::removeErrorFilter();
        CITestStreamFilter::removeOutputFilter();
    }

    /**
     * @param array|bool $cacheQueryStringValue
     *
     * @dataProvider providePageCacheWithCacheQueryString
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/6410
     */
    public function testPageCacheWithCacheQueryString(
        $cacheQueryStringValue,
        int $expectedPagesInCache,
        array $testingUrls
    ): void {
        // Suppress command() output
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();
        CITestStreamFilter::addErrorFilter();

        // Create cache config with cacheQueryString value from the dataProvider
        $cacheConfig                   = config(Cache::class);
        $cacheConfig->cacheQueryString = $cacheQueryStringValue;

        // Clear cache before starting the test
        command('cache:clear');

        // Calculate amount of items in the cache before the test
        $cache             = \Config\Services::cache();
        $cacheStartCounter = count($cache->getCacheInfo());

        // Generate request to each URL from the testing array
        foreach ($testingUrls as $testingUrl) {
            $this->resetServices();
            $_SERVER['REQUEST_URI'] = '/' . $testingUrl;
            $_SERVER['SCRIPT_NAME'] = '/index.php';
            $this->codeigniter      = new MockCodeIgniter(new App());

            $routes    = Services::routes(true);
            $routePath = explode('?', $testingUrl)[0];
            $string    = 'This is a test page, to check cache configuration';
            $routes->add($routePath, static function () use ($string) {
                Services::responsecache()->setTtl(60);
                $response = Services::response();

                return $response->setBody($string);
            });

            // Inject router
            $router = Services::router($routes, Services::incomingrequest(null, false));
            Services::injectMock('router', $router);

            // Cache the page output using default caching function and $cacheConfig
            // with value from the data provider
            ob_start();
            $this->codeigniter->run();
            $output = ob_get_clean();

            $this->assertSame($string, $output);
        }

        // Calculate how much cached items exist in the cache after the test requests
        $cacheEndCounter = count($cache->getCacheInfo());
        $newPagesCached  = $cacheEndCounter - $cacheStartCounter;

        // Clear cache after the test
        command('cache:clear');

        // Check that amount of new items created in the cache matching expected value from the data provider
        $this->assertSame($expectedPagesInCache, $newPagesCached);

        // Remove stream filters
        CITestStreamFilter::removeOutputFilter();
        CITestStreamFilter::removeErrorFilter();
    }

    public static function providePageCacheWithCacheQueryString(): iterable
    {
        $testingUrls = [
            // URL #1
            'test',
            // URL #2
            'test?important_parameter=1',
            // URL #3
            'test?important_parameter=2',
            // URL #4
            'test?important_parameter=1&not_important_parameter=2',
            // URL #5
            'test?important_parameter=1&not_important_parameter=2&another_not_important_parameter=3',
        ];

        return [
            // We expect only 1 page in the cache, because when cacheQueryString
            // is set to false, all GET parameter should be ignored, and page URI
            // will be absolutely same "/test" string for all 5 requests
            '$cacheQueryString=false' => [false, 1, $testingUrls],
            // We expect all 5 pages in the cache, because when cacheQueryString
            // is set to true, all GET parameter should be processed as unique requests
            '$cacheQueryString=true' => [true, 5, $testingUrls],
            // We expect only 3 pages in the cache, because when cacheQueryString
            // is set to array with important parameters, we should ignore all
            // parameters thats not in the array. Only URL #1, URL #2 and URL #3
            // should be cached. URL #4 and URL #5 is duplication of URL #2
            // (with value ?important_parameter=1), so they should not be processed
            // as new unique requests and application should return already cached
            // page for URL #2
            '$cacheQueryString=array' => [['important_parameter'], 3, $testingUrls],
        ];
    }
}
