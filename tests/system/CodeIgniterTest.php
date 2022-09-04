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
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use Config\Cache;
use Config\Filters;
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
            $string   = "You want to see 'about' page.";

            return $response->setBody($string);
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString("You want to see 'about' page.", $output);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6358
     */
    public function testControllersCanReturnDownloadResponseObject()
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/(:segment)', static function ($segment) {
            $response = Services::response();

            return $response->download('some.txt', 'some text', true);
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertSame('some text', $output);
    }

    public function testControllersRunFilterByClassName()
    {
        $_SERVER['argv'] = ['index.php', 'pages/about'];
        $_SERVER['argc'] = 2;

        $_SERVER['REQUEST_URI'] = '/pages/about';

        // Inject mock router.
        $routes = Services::routes();
        $routes->add('pages/about', static fn () => Services::request()->getBody(), ['filter' => Customfilter::class]);

        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('http://hellowworld.com', $output);

        $this->resetServices();
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

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6281
     */
    public function testPageCacheSendSecureHeaders()
    {
        // Suppress command() output
        CITestStreamFilter::$buffer = '';
        $outputStreamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $errorStreamFilter          = stream_filter_append(STDERR, 'CITestStreamFilter');

        // Clear Page cache
        command('cache:clear');

        $_SERVER['REQUEST_URI'] = '/test';

        $routes = Services::routes();
        $routes->add('test', static function () {
            CodeIgniter::cache(3600);

            $response = Services::response();
            $string   = 'This is a test page. Elapsed time: {elapsed_time}';

            return $response->setBody($string);
        });
        $router = Services::router($routes, Services::request());
        Services::injectMock('router', $router);

        /** @var Filters $filterConfig */
        $filterConfig                   = config('Filters');
        $filterConfig->globals['after'] = ['secureheaders'];
        Services::filters($filterConfig);

        // The first response to be cached.
        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('This is a test page', $output);
        $response = Services::response();
        $headers  = $response->headers();
        $this->assertArrayHasKey('X-Frame-Options', $headers);

        // The second response from the Page cache.
        ob_start();
        $this->codeigniter->useSafeOutput(true)->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('This is a test page', $output);
        $response = Services::response();
        $headers  = $response->headers();
        $this->assertArrayHasKey('X-Frame-Options', $headers);

        // Clear Page cache
        command('cache:clear');

        // Remove stream fliters
        stream_filter_remove($outputStreamFilter);
        stream_filter_remove($errorStreamFilter);
    }

    /**
     * @param array|bool $cacheQueryStringValue
     *
     * @dataProvider cacheQueryStringProvider
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/6410
     */
    public function testPageCacheWithCacheQueryString($cacheQueryStringValue, int $expectedPagesInCache, array $testingUrls)
    {
        // Suppress command() output
        CITestStreamFilter::$buffer = '';
        $outputStreamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $errorStreamFilter          = stream_filter_append(STDERR, 'CITestStreamFilter');

        // Create cache config with cacheQueryString value from the dataProvider
        $cacheConfig                   = new Cache();
        $cacheConfig->cacheQueryString = $cacheQueryStringValue;

        // Clear cache before starting the test
        command('cache:clear');

        // Calculate amount of items in the cache before the test
        $cache             = \Config\Services::cache();
        $cacheStartCounter = count($cache->getCacheInfo());

        // Generate request to each URL from the testing array
        foreach ($testingUrls as $testingUrl) {
            $_SERVER['REQUEST_URI'] = '/' . $testingUrl;
            $routes                 = Services::routes(true);
            $routes->add($testingUrl, static function () {
                CodeIgniter::cache(0); // Dont cache the page in the run() function because CodeIgniter class will create default $cacheConfig and overwrite settings from the dataProvider
                $response = Services::response();
                $string   = 'This is a test page, to check cache configuration';

                return $response->setBody($string);
            });

            // Inject router
            $router = Services::router($routes, Services::request(null, false));
            Services::injectMock('router', $router);

            // Cache the page output using default caching function and $cacheConfig with value from the data provider
            $this->codeigniter->useSafeOutput(true)->run();
            $this->codeigniter->cachePage($cacheConfig); // Cache the page using our own $cacheConfig confugration
        }

        // Calculate how much cached items exist in the cache after the test requests
        $cacheEndCounter = count($cache->getCacheInfo());
        $newPagesCached  = $cacheEndCounter - $cacheStartCounter;

        // Clear cache after the test
        command('cache:clear');

        // Check that amount of new items created in the cache matching expected value from the data provider
        $this->assertSame($expectedPagesInCache, $newPagesCached);

        // Remove stream filters
        stream_filter_remove($outputStreamFilter);
        stream_filter_remove($errorStreamFilter);
    }

    public function cacheQueryStringProvider(): array
    {
        $testingUrls = [
            'test', // URL #1
            'test?important_parameter=1', // URL #2
            'test?important_parameter=2',  // URL #3
            'test?important_parameter=1&not_important_parameter=2', // URL #4
            'test?important_parameter=1&not_important_parameter=2&another_not_important_parameter=3', // URL #5
        ];

        return [
            '$cacheQueryString=false' => [false, 1, $testingUrls], // We expect only 1 page in the cache, because when cacheQueryString is set to false, all GET parameter should be ignored, and page URI will be absolutely same "/test" string for all 5 requests
            '$cacheQueryString=true'  => [true, 5, $testingUrls], // We expect all 5 pages in the cache, because when cacheQueryString is set to true, all GET parameter should be processed as unique requests
            '$cacheQueryString=array' => [['important_parameter'], 3, $testingUrls], // We expect only 3 pages in the cache, because when cacheQueryString is set to array with important parameters, we should ignore all parameters thats not in the array. Only URL #1, URL #2 and URL #3 should be cached. URL #4 and URL #5 is duplication of URL #2 (with value ?important_parameter=1), so they should not be processed as new unique requests and application should return already cached page for URL #2
        ];
    }
}
