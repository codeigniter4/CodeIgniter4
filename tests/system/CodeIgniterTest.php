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

namespace CodeIgniter;

use App\Controllers\Home;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use Config\Cache;
use Config\Filters as FiltersConfig;
use Config\Modules;
use Config\Routing;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Tests\Support\Filters\Customfilter;
use Tests\Support\Filters\RedirectFilter;
use Tests\Support\Router\Filters\TestAttributeFilter;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
#[RunTestsInSeparateProcesses]
final class CodeIgniterTest extends CIUnitTestCase
{
    private CodeIgniter $codeigniter;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        Services::injectMock('superglobals', new Superglobals());

        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');

        $this->codeigniter = new MockCodeIgniter(new App());

        $response = service('response');
        $response->pretend();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
    }

    public function testRunEmptyDefaultRoute(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php']);
        $superglobals->setServer('argc', 1);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', (string) $output);
    }

    public function testOutputBufferingControl(): void
    {
        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        // 1 phpunit output buffering level
        $this->assertSame(1, ob_get_level());
    }

    public function testRunEmptyDefaultRouteReturnResponse(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php']);
        $superglobals->setServer('argc', 1);

        $response = $this->codeigniter->run(null, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertStringContainsString('Welcome to CodeIgniter', (string) $response->getBody());
    }

    public function testRunClosureRoute(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php', 'pages/about']);
        $superglobals->setServer('argc', 2);
        $superglobals->setServer('REQUEST_URI', '/pages/about');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add('pages/(:segment)', static function ($segment): void {
            echo 'You want to see "' . esc($segment) . '" page.';
        });
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('You want to see "about" page.', (string) $output);
    }

    /**
     * @psalm-suppress UndefinedClass
     */
    public function testRun404Override(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_METHOD', 'GET');
        $superglobals->setServer('REQUEST_URI', '/pages/about');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Errors::show404');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run($routes);
        $output = ob_get_clean();

        $this->assertStringContainsString("Can't find a route for 'GET: pages/about'.", (string) $output);
        $this->assertSame(404, response()->getStatusCode());
    }

    public function testRun404OverrideControllerReturnsResponse(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php', '/']);
        $superglobals->setServer('argc', 2);

        // Inject mock router.
        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Controllers\Popcorn::pop');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run($routes, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertStringContainsString('Oops', (string) $response->getBody());
        $this->assertSame(567, $response->getStatusCode());
    }

    public function testRun404OverrideReturnResponse(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php', '/']);
        $superglobals->setServer('argc', 2);

        // Inject mock router.
        $routes = service('routes');
        $routes->setAutoRoute(false);
        $routes->set404Override('Tests\Support\Controllers\Popcorn::pop');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $response = $this->codeigniter->run($routes, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertStringContainsString('Oops', (string) $response->getBody());
    }

    public function testRun404OverrideByClosure(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php', '/']);
        $superglobals->setServer('argc', 2);

        // Inject mock router.
        $routes = new RouteCollection(service('locator'), new Modules(), new Routing());
        $routes->setAutoRoute(false);
        $routes->set404Override(static function (): void {
            echo '404 Override by Closure.';
        });
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run($routes);
        $output = ob_get_clean();

        $this->assertStringContainsString('404 Override by Closure.', (string) $output);
        $this->assertSame(404, response()->getStatusCode());
    }

    public function testControllersCanReturnString(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php', 'pages/about']);
        $superglobals->setServer('argc', 2);
        $superglobals->setServer('REQUEST_URI', '/pages/about');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add(
            'pages/(:segment)',
            static fn ($segment): string => 'You want to see "' . esc($segment) . '" page.',
        );
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('You want to see "about" page.', (string) $output);
    }

    public function testControllersCanReturnResponseObject(): void
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('argv', ['index.php', 'pages/about']);
        $superglobals->setServer('argc', 2);
        $superglobals->setServer('REQUEST_URI', '/pages/about');
        $superglobals->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add('pages/(:segment)', static function ($segment) {
            $response = service('response');
            $string   = "You want to see 'about' page.";

            return $response->setBody($string);
        });
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString("You want to see 'about' page.", (string) $output);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6358
     */
    public function testControllersCanReturnDownloadResponseObject(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', 'pages/about'])
            ->setServer('argc', 2)
            ->setServer('REQUEST_URI', '/pages/about')
            ->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add('pages/(:segment)', static function ($segment) {
            $response = service('response');

            return $response->download('some.txt', 'some text', true);
        });
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertSame('some text', $output);
    }

    public function testRunExecuteFilterByClassName(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', 'pages/about'])
            ->setServer('argc', 2)
            ->setServer('REQUEST_URI', '/pages/about')
            ->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add(
            'pages/about',
            static fn () => service('incomingrequest')->getBody(),
            ['filter' => Customfilter::class],
        );

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('http://hellowworld.com', (string) $output);

        $this->resetServices();
    }

    public function testRegisterSameFilterTwiceWithDifferentArgument(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', 'pages/about'])
            ->setServer('argc', 2)
            ->setServer('REQUEST_URI', '/pages/about')
            ->setServer('SCRIPT_NAME', '/index.php');

        $routes = service('routes');
        $routes->add(
            'pages/about',
            static fn () => service('incomingrequest')->getBody(),
            // Set filter with no argument.
            ['filter' => 'test-customfilter'],
        );

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        /** @var FiltersConfig $filterConfig */
        $filterConfig          = config('Filters');
        $filterConfig->filters = [
            // Set filter with argument.
            'test-customfilter:arg1' => [
                'before' => ['pages/*'],
            ],
        ];
        service('filters', $filterConfig);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('http://hellowworld.comhttp://hellowworld.com', (string) $output);

        $this->resetServices();
    }

    public function testDisableControllerFilters(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', 'pages/about'])
            ->setServer('argc', 2)
            ->setServer('REQUEST_URI', '/pages/about')
            ->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add(
            'pages/about',
            static fn () => service('incomingrequest')->getBody(),
            ['filter' => Customfilter::class],
        );
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->disableFilters();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('', (string) $output);

        $this->resetServices();
    }

    public function testResponseConfigEmpty(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', '/'])
            ->setServer('argc', 2);

        $response = service('response', null, false);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRoutesIsEmpty(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', '/'])
            ->setServer('argc', 2);

        // Inject mock router.
        $router = service('router', null, service('incomingrequest'), false);
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', (string) $output);
    }

    public function testTransfersCorrectHTTPVersion(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', '/'])
            ->setServer('argc', 2)
            ->setServer('SERVER_PROTOCOL', 'HTTP/2.0');

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');

        $this->assertSame('2.0', $response->getProtocolVersion());
    }

    public function testSupportsHttp3(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', '/'])
            ->setServer('argc', 2)
            ->setServer('SERVER_PROTOCOL', 'HTTP/3.0');

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');

        $this->assertSame('3.0', $response->getProtocolVersion());
    }

    public function testIgnoringErrorSuppressedByAt(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', '/'])
            ->setServer('argc', 2);

        ob_start();
        @unlink('inexistent-file');
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', (string) $output);
    }

    public function testRunForceSecure(): void
    {
        service('superglobals')
            ->setServer('argv', ['index.php', '/'])
            ->setServer('argc', 2);

        $filterConfig                       = config(FiltersConfig::class);
        $filterConfig->required['before'][] = 'forcehttps';

        $config                            = config(App::class);
        $config->forceGlobalSecureRequests = true;

        $codeigniter = new MockCodeIgniter($config);
        $codeigniter->setContext('web');

        self::getPrivateMethodInvoker($codeigniter, 'getRequestObject')();
        self::getPrivateMethodInvoker($codeigniter, 'getResponseObject')();

        $response = $this->getPrivateProperty($codeigniter, 'response');
        $this->assertNull($response->header('Location'));

        $response = $codeigniter->run(null, true);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertSame('https://example.com/index.php/', $response->header('Location')->getValue());
    }

    public function testRunRedirectionWithNamed(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'example']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/example');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add('pages/named', static function (): void {
        }, ['as' => 'name']);
        $routes->addRedirect('example', 'name');

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();
        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame('http://example.com/pages/named', $response->header('Location')->getValue());
    }

    public function testRunRedirectionWithURI(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'example']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/example');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add('pages/uri', static function (): void {
        });
        $routes->addRedirect('example', 'pages/uri');

        $router = service('router', $routes, service('incomingrequest'));
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
        service('superglobals')->setServer('argv', ['index.php', 'example']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/example');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        // Inject mock router.
        $routes = service('routes');
        // addRedirect() sets status code 302 by default.
        $routes->addRedirect('example', 'pages/notset');

        $router = service('router', $routes, service('incomingrequest'));
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
        service('superglobals')->setServer('argv', ['index.php', 'example']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/example');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        // Inject mock router.
        $routes = service('routes');
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame(301, $response->getStatusCode());
    }

    public function testRunRedirectionWithPOSTAndHTTPCode301(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'example']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/example');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('REQUEST_METHOD', 'POST');

        // Inject mock router.
        $routes = service('routes');
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $response = $this->getPrivateProperty($this->codeigniter, 'response');
        $this->assertSame(301, $response->getStatusCode());
    }

    public function testStoresPreviousURL(): void
    {
        service('superglobals')->setServer('argv', ['index.php', '/']);
        service('superglobals')->setServer('argc', 2);

        // Inject mock router.
        $router = service('router', null, service('incomingrequest'), false);
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertArrayHasKey('_ci_previous_url', $_SESSION);
        $this->assertSame('http://example.com/index.php/', $_SESSION['_ci_previous_url']);
    }

    public function testNotStoresPreviousURL(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'example']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/example');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        // Inject mock router.
        $routes = service('routes');
        $routes->addRedirect('example', 'pages/notset', 301);

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertArrayNotHasKey('_ci_previous_url', $_SESSION);
    }

    public function testNotStoresPreviousURLByCheckingContentType(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'image']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/image');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->add('image', static function () {
            $response = service('response');

            return $response->setContentType('image/jpeg', '');
        });
        $router = service('router', $routes, service('incomingrequest'));
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
        service('superglobals')->setServer('argv', ['index.php', '/']);
        service('superglobals')->setServer('argc', 2);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to CodeIgniter', (string) $output);
    }

    public function testRunCLIRoute(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'cli']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/cli');
        service('superglobals')->setServer('SCRIPT_NAME', 'public/index.php');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('REQUEST_METHOD', 'CLI');

        $routes = service('routes');
        $routes->cli('cli', '\Tests\Support\Controllers\Popcorn::index');

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Method Not Allowed', (string) $output);
    }

    public function testSpoofRequestMethodCanUsePUT(): void
    {
        service('superglobals')->setServer('argv', ['index.php']);
        service('superglobals')->setServer('argc', 1);

        service('superglobals')->setServer('REQUEST_URI', '/');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('REQUEST_METHOD', 'POST');

        service('superglobals')->setPost('_method', Method::PUT);

        $routes = service('routes');
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->post('/', 'Home::index');
        $routes->put('/', 'Home::index');

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertSame(Method::PUT, service('incomingrequest')->getMethod());
    }

    public function testSpoofRequestMethodCannotUseGET(): void
    {
        service('superglobals')->setServer('argv', ['index.php']);
        service('superglobals')->setServer('argc', 1);

        service('superglobals')->setServer('REQUEST_URI', '/');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('SERVER_PROTOCOL', 'HTTP/1.1');
        service('superglobals')->setServer('REQUEST_METHOD', 'POST');

        service('superglobals')->setPost('_method', 'GET');

        $routes = service('routes');
        $routes->setDefaultNamespace('App\Controllers');
        $routes->resetRoutes();
        $routes->post('/', 'Home::index');
        $routes->get('/', 'Home::index');

        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        $this->assertSame('POST', service('incomingrequest')->getMethod());
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

        service('superglobals')->setServer('REQUEST_URI', '/test');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        $routes = service('routes');
        $routes->add('test', static function () {
            CodeIgniter::cache(3600);

            $response = service('response');
            $string   = 'This is a test page. Elapsed time: {elapsed_time}';

            return $response->setBody($string);
        });
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        /** @var FiltersConfig $filterConfig */
        $filterConfig                   = config('Filters');
        $filterConfig->globals['after'] = ['secureheaders'];
        service('filters', $filterConfig);

        // The first response to be cached.
        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('This is a test page', (string) $output);
        $response = service('response');
        $headers  = $response->headers();
        $this->assertArrayHasKey('X-Frame-Options', $headers);

        // The second response from the Page cache.
        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('This is a test page', (string) $output);
        $response = service('response');
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
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/6410
     */
    #[DataProvider('providePageCacheWithCacheQueryString')]
    public function testPageCacheWithCacheQueryString(
        $cacheQueryStringValue,
        int $expectedPagesInCache,
        array $testingUrls,
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
            service('superglobals')->setServer('REQUEST_URI', '/' . $testingUrl);
            service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
            $this->codeigniter = new MockCodeIgniter(new App());

            $routes    = service('routes', true);
            $routePath = explode('?', $testingUrl)[0];
            $string    = 'This is a test page, to check cache configuration';
            $routes->add($routePath, static function () use ($string) {
                service('responsecache')->setTtl(60);
                $response = service('response');

                return $response->setBody($string);
            });

            // Inject router
            $router = service('router', $routes, service('incomingrequest', null, false));
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

    /**
     * See https://github.com/codeigniter4/CodeIgniter4/issues/7205
     */
    public function testRunControllerNotFoundBeforeFilter(): void
    {
        service('superglobals')->setServer('argv', ['index.php']);
        service('superglobals')->setServer('argc', 1);

        service('superglobals')->setServer('REQUEST_URI', '/cannotFound');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router.
        $routes = service('routes');
        $routes->setAutoRoute(true);

        // Inject the before filter.
        $filterConfig                            = config('Filters');
        $filterConfig->aliases['redirectFilter'] = RedirectFilter::class;
        $filterConfig->globals['before']         = ['redirectFilter'];
        service('filters', $filterConfig);

        $this->expectException(PageNotFoundException::class);

        $this->codeigniter->run($routes);
    }

    public function testStartControllerPermitsInvoke(): void
    {
        $this->setPrivateProperty($this->codeigniter, 'benchmark', new Timer());
        $this->setPrivateProperty($this->codeigniter, 'controller', '\\' . Home::class);

        // Set up the request and router
        $request = service('incomingrequest');
        $this->setPrivateProperty($this->codeigniter, 'request', $request);

        $routes = service('routes');
        $router = service('router', $routes, $request);
        $this->setPrivateProperty($this->codeigniter, 'router', $router);

        $startController = self::getPrivateMethodInvoker($this->codeigniter, 'startController');

        $this->setPrivateProperty($this->codeigniter, 'method', '__invoke');
        $startController();

        // No PageNotFoundException
        $this->assertTrue(true);
    }

    public function testRouteAttributeCacheIntegration(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/cached']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/cached');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        // Clear cache before test
        cache()->clean();

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/cached', '\Tests\Support\Router\Controllers\AttributeController::cached');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        // First request - should cache
        ob_start();
        $this->codeigniter->run();
        $output1 = ob_get_clean();

        $this->assertStringContainsString('Cached content at', (string) $output1);

        // Extract timestamp from first response
        preg_match('/Cached content at (\d+)/', (string) $output1, $matches1);
        $time1 = $matches1[1] ?? null;

        // Wait a moment to ensure time would be different if not cached
        sleep(1);

        // Second request - should return cached version with same timestamp
        $this->resetServices();
        service('superglobals')->setServer('argv', ['index.php', 'attribute/cached']);
        service('superglobals')->setServer('argc', 2);
        service('superglobals')->setServer('REQUEST_URI', '/attribute/cached');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');
        $this->codeigniter = new MockCodeIgniter(new App());

        $routes = service('routes');
        $routes->get('attribute/cached', '\Tests\Support\Router\Controllers\AttributeController::cached');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output2 = ob_get_clean();

        preg_match('/Cached content at (\d+)/', (string) $output2, $matches2);
        $time2 = $matches2[1] ?? null;

        // Timestamps should be EXACTLY the same (cached response)
        $this->assertSame($time1, $time2, 'Expected cached response with identical timestamp');

        // Clear cache after test
        cache()->clean();
    }

    public function testRouteAttributeFilterIntegration(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/filtered']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/filtered');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Register the test filter
        $filterConfig                                 = config('Filters');
        $filterConfig->aliases['testAttributeFilter'] = TestAttributeFilter::class;
        service('filters', $filterConfig);

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/filtered', '\Tests\Support\Router\Controllers\AttributeController::filtered');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        // Verify filter ran before (modified request body) and after (appended to response)
        $this->assertStringContainsString('Filtered: before_filter_ran:', (string) $output);
        $this->assertStringContainsString(':after_filter_ran', (string) $output);
    }

    public function testRouteAttributeFilterWithParamsIntegration(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/filteredWithParams']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/filteredWithParams');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Register the test filter
        $filterConfig                                 = config('Filters');
        $filterConfig->aliases['testAttributeFilter'] = TestAttributeFilter::class;
        service('filters', $filterConfig);

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/filteredWithParams', '\Tests\Support\Router\Controllers\AttributeController::filteredWithParams');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        // Verify filter ran before (modified request body) and after (appended to response)
        $this->assertStringContainsString('Filtered: before_filter_ran(arg1,arg2):', (string) $output);
        $this->assertStringContainsString(':after_filter_ran(arg1,arg2)', (string) $output);
    }

    public function testRouteAttributeRestrictIntegration(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/restricted']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/restricted');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/restricted', '\Tests\Support\Router\Controllers\AttributeController::restricted');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        // Should allow access since we're in the current ENVIRONMENT
        $this->assertStringContainsString('Access granted', (string) $output);
    }

    public function testRouteAttributeRestrictThrowsException(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/restricted']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/shouldBeRestricted');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/shouldBeRestricted', '\Tests\Support\Router\Controllers\AttributeController::shouldBeRestricted');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        // Should throw PageNotFoundException because we're not in 'production'
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Current environment is not allowed.');

        $this->codeigniter->run();
    }

    public function testRouteAttributeMultipleAttributesIntegration(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/multiple']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/multiple');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Register the test filter
        $filterConfig                                 = config('Filters');
        $filterConfig->aliases['testAttributeFilter'] = TestAttributeFilter::class;
        service('filters', $filterConfig);

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/multiple', '\Tests\Support\Router\Controllers\AttributeController::multipleAttributes');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        // Verify both Restrict and Filter attributes worked
        $this->assertStringContainsString('Multiple: before_filter_ran:', (string) $output);
        $this->assertStringContainsString(':after_filter_ran', (string) $output);
    }

    public function testRouteAttributeNoAttributesIntegration(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/none']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/none');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/none', '\Tests\Support\Router\Controllers\AttributeController::noAttributes');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        ob_start();
        $this->codeigniter->run();
        $output = ob_get_clean();

        // Should work normally with no attribute processing
        $this->assertStringContainsString('No attributes', (string) $output);
    }

    public function testRouteAttributeCustomCacheKeyIntegration(): void
    {
        service('superglobals')->setServer('argv', ['index.php', 'attribute/customkey']);
        service('superglobals')->setServer('argc', 2);

        service('superglobals')->setServer('REQUEST_URI', '/attribute/customkey');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        // Clear cache before test
        cache()->clean();

        // Inject mock router
        $routes = service('routes');
        $routes->get('attribute/customkey', '\Tests\Support\Router\Controllers\AttributeController::customCacheKey');
        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        // First request
        ob_start();
        $this->codeigniter->run();
        ob_get_clean();

        // Verify custom cache key was used
        $cached = cache('custom_cache_key');
        $this->assertNotNull($cached);
        $this->assertIsArray($cached);
        $this->assertArrayHasKey('body', $cached);
        $this->assertStringContainsString('Custom key content at', (string) $cached['body']);

        // Clear cache after test
        cache()->clean();
    }

    public function testRouteAttributesDisabledInConfig(): void
    {
        service('superglobals')->setServer('REQUEST_URI', '/attribute/filtered');
        service('superglobals')->setServer('SCRIPT_NAME', '/index.php');
        service('superglobals')->setServer('REQUEST_METHOD', 'GET');

        // Disable route attributes in config BEFORE creating CodeIgniter instance
        $routing                          = config('routing');
        $routing->useControllerAttributes = false;
        Factories::injectMock('config', 'routing', $routing);

        // Register the test filter (even though attributes are disabled,
        // we need it registered to avoid FilterException)
        $filterConfig                                 = config('Filters');
        $filterConfig->aliases['testAttributeFilter'] = TestAttributeFilter::class;
        service('filters', $filterConfig);

        $routes = service('routes');
        $routes->setAutoRoute(false);

        // We're testing that a route defined normally will work,
        // but the attributes on the controller method won't be processed
        $routes->get('attribute/filtered', '\Tests\Support\Router\Controllers\AttributeController::filtered');

        $router = service('router', $routes, service('incomingrequest'));
        Services::injectMock('router', $router);

        $config      = new App();
        $codeigniter = new MockCodeIgniter($config);

        ob_start();
        $codeigniter->run($routes);
        $output = ob_get_clean();

        // When useRouteAttributes is false, the filter attributes should NOT be processed
        // So the filter should not have run
        $this->assertStringNotContainsString('before_filter_ran', (string) $output);
        $this->assertStringNotContainsString('after_filter_ran', (string) $output);
        // But the controller method should still execute
        $this->assertStringContainsString('Filtered', (string) $output);
    }

    public function testResetForWorkerMode(): void
    {
        $config      = new App();
        $codeigniter = new MockCodeIgniter($config);

        $this->setPrivateProperty($codeigniter, 'request', service('request'));
        $this->setPrivateProperty($codeigniter, 'response', service('response'));
        $this->setPrivateProperty($codeigniter, 'output', 'test output');

        $this->assertNotNull($this->getPrivateProperty($codeigniter, 'request'));
        $this->assertNotNull($this->getPrivateProperty($codeigniter, 'response'));
        $this->assertNotNull($this->getPrivateProperty($codeigniter, 'output'));

        $codeigniter->resetForWorkerMode();

        $this->assertNull($this->getPrivateProperty($codeigniter, 'request'));
        $this->assertNull($this->getPrivateProperty($codeigniter, 'response'));
        $this->assertNull($this->getPrivateProperty($codeigniter, 'router'));
        $this->assertNull($this->getPrivateProperty($codeigniter, 'controller'));
        $this->assertNull($this->getPrivateProperty($codeigniter, 'method'));
        $this->assertNull($this->getPrivateProperty($codeigniter, 'output'));
    }
}
