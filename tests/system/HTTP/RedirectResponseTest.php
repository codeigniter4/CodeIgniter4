<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\Validation\Validation;
use Config\App;
use Config\Modules;
use Config\Services;

/**
 * @internal
 */
final class RedirectResponseTest extends CIUnitTestCase
{
    /**
     * @var RouteCollection
     */
    protected $routes;
    protected $request;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->config          = new App();
        $this->config->baseURL = 'http://example.com/';

        $this->routes = new RouteCollection(Services::locator(), new Modules());
        Services::injectMock('routes', $this->routes);

        $this->request = new MockIncomingRequest($this->config, new URI('http://example.com'), null, new UserAgent());
        Services::injectMock('request', $this->request);
    }

    //--------------------------------------------------------------------

    public function testRedirectToFullURI()
    {
        $response = new RedirectResponse(new App());

        $response = $response->to('http://example.com/foo');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/foo', $response->getHeaderLine('Location'));
    }

    //--------------------------------------------------------------------

    public function testRedirectRoute()
    {
        $response = new RedirectResponse(new App());

        $this->routes->add('exampleRoute', 'Home::index');

        $response->route('exampleRoute');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/index.php/exampleRoute', $response->getHeaderLine('Location'));

        $this->routes->add('exampleRoute', 'Home::index', ['as' => 'home']);

        $response->route('home');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/index.php/exampleRoute', $response->getHeaderLine('Location'));
    }

    public function testRedirectRouteBad()
    {
        $this->expectException(HTTPException::class);

        $response = new RedirectResponse(new App());

        $this->routes->add('exampleRoute', 'Home::index');

        $response->route('differentRoute');
    }

    //--------------------------------------------------------------------

    public function testRedirectRelativeConvertsToFullURI()
    {
        $response = new RedirectResponse($this->config);

        $response = $response->to('/foo');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/index.php/foo', $response->getHeaderLine('Location'));
    }

    //--------------------------------------------------------------------

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testWithInput()
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

    //--------------------------------------------------------------------

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testWithValidationErrors()
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());

        $validation = $this->createMock(Validation::class);
        $validation->method('getErrors')->willReturn(['foo' => 'bar']);

        Services::injectMock('validation', $validation);

        $response->withInput();

        $this->assertArrayHasKey('_ci_validation_errors', $_SESSION);
    }

    //--------------------------------------------------------------------

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testWith()
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());

        $returned = $response->with('foo', 'bar');

        $this->assertSame($response, $returned);
        $this->assertArrayHasKey('foo', $_SESSION);
    }

    //--------------------------------------------------------------------

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testRedirectBack()
    {
        $_SERVER['HTTP_REFERER'] = 'http://somewhere.com';
        $this->request           = new MockIncomingRequest($this->config, new URI('http://somewhere.com'), null, new UserAgent());
        Services::injectMock('request', $this->request);

        $response = new RedirectResponse(new App());

        $returned = $response->back();
        $this->assertSame('http://somewhere.com', $returned->header('location')->getValue());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testRedirectBackMissing()
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());

        $returned = $response->back();

        $this->assertSame($response, $returned);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/2119
     */
    public function testRedirectRouteBaseUrl()
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/test/';
        Factories::injectMock('config', 'App', $config);

        $request = new MockIncomingRequest($config, new URI('http://example.com/test/'), null, new UserAgent());
        Services::injectMock('request', $request);

        $response = new RedirectResponse(new App());

        $this->routes->add('exampleRoute', 'Home::index');

        $response->route('exampleRoute');

        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('http://example.com/test/index.php/exampleRoute', $response->getHeaderLine('Location'));

        Factories::reset('config');
    }

    public function testWithCookies()
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
     * @preserveGlobalState  disabled
     */
    public function testWithCookiesWithEmptyCookies()
    {
        $_SESSION = [];

        $response = new RedirectResponse(new App());
        $response = $response->withCookies();

        $this->assertEmpty($response->getCookies());
    }

    public function testWithHeaders()
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

    public function testWithHeadersWithEmptyHeaders()
    {
        $_SESSION = [];

        $baseResponse = service('response');

        foreach (array_keys($baseResponse->headers()) as $key) {
            $baseResponse->removeHeader($key);
        }

        $response = new RedirectResponse(new App());
        $response = $response->withHeaders();

        $this->assertEmpty($baseResponse->headers());
    }
}
