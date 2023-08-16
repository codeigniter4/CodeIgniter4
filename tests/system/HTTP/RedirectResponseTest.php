<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\Validation\Validation;
use Config\App;
use Config\Modules;
use Config\Routing;
use Config\Services;

/**
 * @internal
 *
 * @group SeparateProcess
 */
final class RedirectResponseTest extends CIUnitTestCase
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    private MockIncomingRequest $request;
    private App $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->config          = new App();
        $this->config->baseURL = 'http://example.com/';

        $this->routes = new RouteCollection(Services::locator(), new Modules(), new Routing());
        Services::injectMock('routes', $this->routes);

        $this->request = new MockIncomingRequest(
            $this->config,
            new SiteURI($this->config),
            null,
            new UserAgent()
        );
        Services::injectMock('request', $this->request);
    }

    public function testRedirectToFullURI(): void
    {
        $response = new RedirectResponse(new App());

        $response = $response->to('http://example.com/foo');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/foo', $response->getHeaderLine('Location'));
    }

    public function testRedirectRoute(): void
    {
        $response = new RedirectResponse(new App());

        $this->routes->add('exampleRoute', 'Home::index');

        $response->route('exampleRoute');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/index.php/exampleRoute', $response->getHeaderLine('Location'));

        $this->routes->add('exampleRoute2', 'Home::index', ['as' => 'home']);

        $response->route('home');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/index.php/exampleRoute2', $response->getHeaderLine('Location'));
    }

    public function testRedirectRouteBadNamedRoute(): void
    {
        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage('The route for "differentRoute" cannot be found.');

        $response = new RedirectResponse(new App());

        $this->routes->add('exampleRoute', 'Home::index');

        $response->route('differentRoute');
    }

    public function testRedirectRouteBadControllerMethod(): void
    {
        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage('The route for "Bad::badMethod" cannot be found.');

        $response = new RedirectResponse(new App());

        $this->routes->add('exampleRoute', 'Home::index');

        $response->route('Bad::badMethod');
    }

    public function testRedirectRelativeConvertsToFullURI(): void
    {
        $response = new RedirectResponse($this->config);

        $response = $response->to('/foo');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/index.php/foo', $response->getHeaderLine('Location'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWithInput(): void
    {
        $_SESSION = [];
        $_GET     = ['foo' => 'bar'];
        $_POST    = ['bar' => 'baz'];

        $response = new RedirectResponse(new App());

        $returned = $response->withInput();

        $this->assertSame($response, $returned);
        $this->assertArrayHasKey('_ci_old_input', $_SESSION);
        $this->assertSame('bar', $_SESSION['_ci_old_input']['get']['foo']);
        $this->assertSame('baz', $_SESSION['_ci_old_input']['post']['bar']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWithValidationErrors(): void
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());

        $validation = $this->createMock(Validation::class);
        $validation->method('getErrors')->willReturn(['foo' => 'bar']);

        Services::injectMock('validation', $validation);

        $response->withInput();

        $this->assertArrayHasKey('_ci_validation_errors', $_SESSION);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWith(): void
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());

        $returned = $response->with('foo', 'bar');

        $this->assertSame($response, $returned);
        $this->assertArrayHasKey('foo', $_SESSION);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRedirectBack(): void
    {
        $_SERVER['HTTP_REFERER'] = 'http://somewhere.com';

        $this->request = new MockIncomingRequest($this->config, new SiteURI($this->config), null, new UserAgent());
        Services::injectMock('request', $this->request);

        $response = new RedirectResponse(new App());

        $returned = $response->back();
        $this->assertSame('http://somewhere.com', $returned->header('location')->getValue());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRedirectBackMissing(): void
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());

        $returned = $response->back();

        $this->assertSame($response, $returned);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2119
     */
    public function testRedirectRouteBaseUrl(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/test/';
        Factories::injectMock('config', 'App', $config);

        $request = new MockIncomingRequest($config, new SiteURI($config), null, new UserAgent());
        Services::injectMock('request', $request);

        $response = new RedirectResponse(new App());

        $this->routes->add('exampleRoute', 'Home::index');

        $response->route('exampleRoute');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/test/index.php/exampleRoute', $response->getHeaderLine('Location'));

        Factories::reset('config');
    }

    public function testWithCookies(): void
    {
        $_SESSION = [];

        $baseResponse = Services::response();
        $baseResponse->setCookie('foo', 'bar');

        $response = new RedirectResponse(new App());
        $this->assertFalse($response->hasCookie('foo', 'bar'));

        $response = $response->withCookies();
        $this->assertTrue($response->hasCookie('foo', 'bar'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWithCookiesWithEmptyCookies(): void
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());
        $response = $response->withCookies();

        $this->assertEmpty($response->getCookies());
    }

    public function testWithHeaders(): void
    {
        $_SESSION = [];

        $baseResponse = service('response');
        $baseResponse->setHeader('foo', 'bar');

        $response = new RedirectResponse(new App());
        $this->assertFalse($response->hasHeader('foo'));

        $response = $response->withHeaders();

        foreach ($baseResponse->headers() as $name => $header) {
            $this->assertTrue($response->hasHeader($name));
            $this->assertSame($header->getValue(), $response->header($name)->getValue());
        }
    }

    public function testWithHeadersWithEmptyHeaders(): void
    {
        $_SESSION = [];

        $baseResponse = service('response');

        foreach (array_keys($baseResponse->headers()) as $key) {
            $baseResponse->removeHeader($key);
        }

        $response = new RedirectResponse(new App());
        $response->withHeaders();

        $this->assertEmpty($baseResponse->headers());
    }
}
