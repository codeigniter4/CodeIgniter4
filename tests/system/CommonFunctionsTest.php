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

use CodeIgniter\Config\BaseService;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Session\Handlers\FileHandler;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\Test\Mock\MockSecurity;
use CodeIgniter\Test\Mock\MockSession;
use CodeIgniter\Test\TestLogger;
use Config\App;
use Config\Cookie;
use Config\DocTypes;
use Config\Logger;
use Config\Modules;
use Config\Routing;
use Config\Security as SecurityConfig;
use Config\Services;
use Config\Session as SessionConfig;
use Exception;
use Kint;
use RuntimeException;
use stdClass;
use Tests\Support\Models\JobModel;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group SeparateProcess
 */
final class CommonFunctionsTest extends CIUnitTestCase
{
    private ?App $config = null;
    private IncomingRequest $request;

    protected function setUp(): void
    {
        unset($_ENV['foo'], $_SERVER['foo']);
        $this->resetServices();

        parent::setUp();
    }

    public function testStringifyAttributes(): void
    {
        $this->assertSame(' class="foo" id="bar"', stringify_attributes(['class' => 'foo', 'id' => 'bar']));

        $atts        = new stdClass();
        $atts->class = 'foo';
        $atts->id    = 'bar';
        $this->assertSame(' class="foo" id="bar"', stringify_attributes($atts));

        $atts = new stdClass();
        $this->assertSame('', stringify_attributes($atts));

        $this->assertSame(' class="foo" id="bar"', stringify_attributes('class="foo" id="bar"'));

        $this->assertSame('', stringify_attributes([]));
    }

    public function testStringifyJsAttributes(): void
    {
        $this->assertSame('width=800,height=600', stringify_attributes(['width' => '800', 'height' => '600'], true));

        $atts         = new stdClass();
        $atts->width  = 800;
        $atts->height = 600;
        $this->assertSame('width=800,height=600', stringify_attributes($atts, true));
    }

    public function testEnvReturnsDefault(): void
    {
        $this->assertSame('baz', env('foo', 'baz'));
    }

    public function testEnvGetsFromSERVER(): void
    {
        $_SERVER['foo'] = 'bar';

        $this->assertSame('bar', env('foo', 'baz'));
    }

    public function testEnvGetsFromENV(): void
    {
        $_ENV['foo'] = 'bar';

        $this->assertSame('bar', env('foo', 'baz'));
    }

    public function testEnvBooleans(): void
    {
        $_ENV['p1'] = 'true';
        $_ENV['p2'] = 'false';
        $_ENV['p3'] = 'empty';
        $_ENV['p4'] = 'null';

        $this->assertTrue(env('p1'));
        $this->assertFalse(env('p2'));
        $this->assertEmpty(env('p3'));
        $this->assertNull(env('p4'));
    }

    private function createRouteCollection(): RouteCollection
    {
        return new RouteCollection(Services::locator(), new Modules(), new Routing());
    }

    public function testRedirectReturnsRedirectResponse(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $response = $this->createMock(Response::class);
        Services::injectMock('response', $response);

        $routes = $this->createRouteCollection();
        Services::injectMock('routes', $routes);

        $routes->add('home/base', 'Controller::index', ['as' => 'base']);
        $response->method('redirect')->willReturnArgument(0);

        $this->assertInstanceOf(RedirectResponse::class, redirect('base'));
    }

    public function testRedirectDefault(): void
    {
        $this->assertInstanceOf(RedirectResponse::class, redirect());
    }

    public function testRequestIncomingRequest(): void
    {
        Services::createRequest(new App());

        $request = request();

        $this->assertInstanceOf(IncomingRequest::class, $request);
    }

    public function testRequestCLIRequest(): void
    {
        Services::createRequest(new App(), true);

        $request = request();

        $this->assertInstanceOf(CLIRequest::class, $request);
    }

    public function testResponse(): void
    {
        $response = response();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSolidusElement(): void
    {
        $this->assertSame('', _solidus());
    }

    public function testSolidusElementXHTML(): void
    {
        $this->disableHtml5();

        $this->assertSame(' /', _solidus());

        $this->enableHtml5();
    }

    private function disableHtml5()
    {
        $doctypes        = new DocTypes();
        $doctypes->html5 = false;
        _solidus($doctypes);
    }

    private function enableHtml5()
    {
        $doctypes = new DocTypes();
        _solidus($doctypes);
    }

    public function testView(): void
    {
        $data = [
            'testString' => 'bar',
            'bar'        => 'baz',
        ];
        $expected = '<h1>bar</h1>';
        $this->assertStringContainsString($expected, view('\Tests\Support\View\Views\simple', $data));
    }

    public function testViewSavedData(): void
    {
        $data = [
            'testString' => 'bar',
            'bar'        => 'baz',
        ];
        $expected = '<h1>bar</h1>';
        $this->assertStringContainsString($expected, view('\Tests\Support\View\Views\simple', $data, ['saveData' => true]));
        $this->assertStringContainsString($expected, view('\Tests\Support\View\Views\simple'));
    }

    public function testViewCell(): void
    {
        $expected = 'Hello';
        $this->assertSame($expected, view_cell('\Tests\Support\View\SampleClass::hello'));
    }

    public function testEscapeWithDifferentEncodings(): void
    {
        $this->assertSame('&lt;x', esc('<x', 'html', 'utf-8'));
        $this->assertSame('&lt;x', esc('<x', 'html', 'iso-8859-1'));
        $this->assertSame('&lt;x', esc('<x', 'html', 'windows-1251'));
    }

    public function testEscapeBadContext(): void
    {
        $this->expectException('InvalidArgumentException');
        esc(['width' => '800', 'height' => '600'], 'bogus');
    }

    public function testEscapeBadContextZero(): void
    {
        $this->expectException('InvalidArgumentException');
        esc('<script>', '0');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSessionInstance(): void
    {
        $this->injectSessionMock();

        $this->assertInstanceOf(Session::class, session());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSessionVariable(): void
    {
        $this->injectSessionMock();

        $_SESSION['notbogus'] = 'Hi there';

        $this->assertSame('Hi there', session('notbogus'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSessionVariableNotThere(): void
    {
        $this->injectSessionMock();

        $_SESSION['bogus'] = 'Hi there';
        $this->assertNull(session('notbogus'));
    }

    public function testRouteTo(): void
    {
        // prime the pump
        $routes = service('routes');
        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $this->assertSame('/path/string/to/13', route_to('myController::goto', 'string', 13));
    }

    public function testRouteToInCliWithoutLocaleInRoute(): void
    {
        Services::createRequest(new App(), true);
        $routes = service('routes');
        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $this->assertSame('/path/string/to/13', route_to('myController::goto', 'string', 13));
    }

    public function testRouteToInCliWithLocaleInRoute(): void
    {
        Services::createRequest(new App(), true);
        $routes = service('routes');
        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('{locale}/path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'path-to']);

        $this->assertSame(
            '/en/path/string/to/13',
            route_to('path-to', 'string', 13, 'en')
        );
    }

    public function testRouteToWithUnsupportedLocale(): void
    {
        Services::createRequest(new App(), false);
        $routes = service('routes');
        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('{locale}/path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'path-to']);

        $this->assertSame(
            '/en/path/string/to/13',
            route_to('path-to', 'string', 13, 'invalid')
        );
    }

    public function testInvisible(): void
    {
        $this->assertSame('Javascript', remove_invisible_characters("Java\0script"));
    }

    public function testInvisibleEncoded(): void
    {
        $this->assertSame('Javascript', remove_invisible_characters('Java%0cscript'));
    }

    public function testAppTimezone(): void
    {
        $this->assertSame('UTC', app_timezone());
    }

    public function testCSRFToken(): void
    {
        Services::injectMock('security', new MockSecurity(new SecurityConfig()));

        $this->assertSame('csrf_test_name', csrf_token());
    }

    public function testCSRFHeader(): void
    {
        $this->assertSame('X-CSRF-TOKEN', csrf_header());
    }

    public function testHash(): void
    {
        $this->assertSame(32, strlen(csrf_hash()));
    }

    public function testCSRFField(): void
    {
        $this->assertStringContainsString('<input type="hidden" ', csrf_field());
    }

    public function testCSRFMeta(): void
    {
        $this->assertStringContainsString('<meta name="X-CSRF-TOKEN" ', csrf_meta());
    }

    public function testModelNotExists(): void
    {
        $this->assertNull(model(UnexsistenceClass::class));
    }

    public function testModelExistsBasename(): void
    {
        $this->assertInstanceOf(JobModel::class, model('JobModel'));
    }

    public function testModelExistsClassname(): void
    {
        $this->assertInstanceOf(JobModel::class, model(JobModel::class));
    }

    public function testModelExistsAbsoluteClassname(): void
    {
        $this->assertInstanceOf(JobModel::class, model(JobModel::class));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOldInput(): void
    {
        $this->injectSessionMock();
        // setup from RedirectResponseTest...
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->config          = new App();
        $this->config->baseURL = 'http://example.com/';

        $this->routes = $this->createRouteCollection();
        Services::injectMock('routes', $this->routes);

        $this->request = new MockIncomingRequest($this->config, new URI('http://example.com'), null, new UserAgent());
        Services::injectMock('request', $this->request);

        // setup & ask for a redirect...
        $_SESSION = [];
        $_GET     = ['foo' => 'bar'];
        $_POST    = [
            'bar'    => 'baz',
            'zibble' => 'fritz',
        ];

        $response = new RedirectResponse(new App());
        $response->withInput();

        $this->assertSame('bar', old('foo')); // regular parameter
        $this->assertSame('doo', old('yabba dabba', 'doo')); // non-existing parameter
        $this->assertSame('fritz', old('zibble'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOldInputSerializeData(): void
    {
        $this->injectSessionMock();
        // setup from RedirectResponseTest...
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->config          = new App();
        $this->config->baseURL = 'http://example.com/';

        $this->routes = $this->createRouteCollection();
        Services::injectMock('routes', $this->routes);

        $this->request = new MockIncomingRequest($this->config, new URI('http://example.com'), null, new UserAgent());
        Services::injectMock('request', $this->request);

        // setup & ask for a redirect...
        $_SESSION = [];
        $_GET     = [];
        $_POST    = [
            'zibble' => serialize('fritz'),
        ];

        $response = new RedirectResponse(new App());
        $response->withInput();

        // serialized parameters are only HTML-escaped.
        $this->assertSame('s:5:&quot;fritz&quot;;', old('zibble'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1492
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOldInputArray(): void
    {
        $this->injectSessionMock();
        // setup from RedirectResponseTest...
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->config          = new App();
        $this->config->baseURL = 'http://example.com/';

        $this->routes = $this->createRouteCollection();
        Services::injectMock('routes', $this->routes);

        $this->request = new MockIncomingRequest($this->config, new URI('http://example.com'), null, new UserAgent());
        Services::injectMock('request', $this->request);

        $locations = [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'SK' => 'Saskatchewan',
        ];

        // setup & ask for a redirect...
        $_SESSION = [];
        $_GET     = [];
        $_POST    = ['location' => $locations];

        $response = new RedirectResponse(new App());
        $response->withInput();

        $this->assertSame($locations, old('location'));
    }

    public function testReallyWritable(): void
    {
        // cannot test fully on *nix
        $this->assertTrue(is_really_writable(WRITEPATH));
    }

    public function testSlashItem(): void
    {
        $this->assertSame('en/', slash_item('defaultLocale')); // en
        $this->assertSame('', slash_item('negotiateLocale')); // false
    }

    public function testSlashItemOnInexistentItem(): void
    {
        $this->assertNull(slash_item('foo'));
        $this->assertNull(slash_item('bar'));
        $this->assertNull(slash_item('cookieDomains'));
        $this->assertNull(slash_item('indices'));
    }

    public function testSlashItemThrowsErrorOnNonStringableItem(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot convert "Config\\App::$supportedLocales" of type "array" to type "string".');

        slash_item('supportedLocales');
    }

    protected function injectSessionMock(): void
    {
        $sessionConfig = new SessionConfig();

        $defaults = [
            'driver'            => FileHandler::class,
            'cookieName'        => 'ci_session',
            'expiration'        => 7200,
            'savePath'          => '',
            'matchIP'           => false,
            'timeToUpdate'      => 300,
            'regenerateDestroy' => false,
        ];

        foreach ($defaults as $key => $config) {
            $sessionConfig->{$key} = $config;
        }

        $cookie = new Cookie();

        foreach ([
            'prefix'   => '',
            'domain'   => '',
            'path'     => '/',
            'secure'   => false,
            'samesite' => 'Lax',
        ] as $key => $value) {
            $cookie->{$key} = $value;
        }
        Factories::injectMock('config', 'Cookie', $cookie);

        $session = new MockSession(new FileHandler($sessionConfig, '127.0.0.1'), $sessionConfig);
        $session->setLogger(new TestLogger(new Logger()));
        BaseService::injectMock('session', $session);
    }

    // Make sure cookies are set by RedirectResponse this way
    // See https://github.com/codeigniter4/CodeIgniter4/issues/1393
    public function testRedirectResponseCookies1(): void
    {
        $loginTime = time();

        $routes = service('routes');
        $routes->add('user/login', 'Auth::verify', ['as' => 'login']);

        $answer1 = redirect()->route('login')
            ->setCookie('foo', 'onething', YEAR)
            ->setCookie('login_time', $loginTime, YEAR);

        $this->assertTrue($answer1->hasCookie('foo', 'onething'));
        $this->assertTrue($answer1->hasCookie('login_time'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTrace(): void
    {
        ob_start();
        trace();
        $content = ob_get_clean();

        $this->assertStringContainsString('Debug Backtrace', $content);
    }

    public function testViewNotSaveData(): void
    {
        $data = [
            'testString' => 'bar',
            'bar'        => 'baz',
        ];
        $this->assertStringContainsString('<h1>bar</h1>', view('\Tests\Support\View\Views\simples', $data, ['saveData' => false]));
        $this->assertStringContainsString('<h1>is_not</h1>', view('\Tests\Support\View\Views\simples'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForceHttpsNullRequestAndResponse(): void
    {
        $this->assertNull(Services::response()->header('Location'));
        Services::response()->setCookie('force', 'cookie');
        Services::response()->setHeader('Force', 'header');
        Services::response()->setBody('default body');

        try {
            force_https();
        } catch (Exception $e) {
            $this->assertInstanceOf(RedirectException::class, $e);
            $this->assertSame(
                'https://example.com/index.php/',
                $e->getResponse()->header('Location')->getValue()
            );
            $this->assertFalse($e->getResponse()->hasCookie('force'));
            $this->assertSame('header', $e->getResponse()->getHeaderLine('Force'));
            $this->assertSame('', $e->getResponse()->getBody());
            $this->assertSame(307, $e->getResponse()->getStatusCode());
        }

        $this->expectException(RedirectException::class);
        force_https();
    }

    /**
     * @dataProvider provideCleanPathActuallyCleaningThePaths
     *
     * @param mixed $input
     * @param mixed $expected
     */
    public function testCleanPathActuallyCleaningThePaths($input, $expected): void
    {
        $this->assertSame($expected, clean_path($input));
    }

    public static function provideCleanPathActuallyCleaningThePaths(): iterable
    {
        $ds = DIRECTORY_SEPARATOR;

        return [
            [
                ROOTPATH . 'spark',
                'ROOTPATH' . $ds . 'spark',
            ],
            [
                APPPATH . 'Config' . $ds . 'App.php',
                'APPPATH' . $ds . 'Config' . $ds . 'App.php',
            ],
            [
                SYSTEMPATH . 'CodeIgniter.php',
                'SYSTEMPATH' . $ds . 'CodeIgniter.php',
            ],
            [
                VENDORPATH . 'autoload.php',
                'VENDORPATH' . $ds . 'autoload.php',
            ],
            [
                FCPATH . 'index.php',
                'FCPATH' . $ds . 'index.php',
            ],
        ];
    }

    public function testIsCli(): void
    {
        $this->assertIsBool(is_cli());
        $this->assertTrue(is_cli());
    }

    public function testDWithCSP(): void
    {
        $this->resetServices();

        /** @var App $config */
        $config             = config('App');
        $config->CSPEnabled = true;

        // Initialize Kint
        $app = new MockCodeIgniter($config);
        $app->initialize();

        $cliDetection        = Kint::$cli_detection;
        Kint::$cli_detection = false;

        $this->expectOutputRegex('/<script class="kint-rich-script" nonce="[0-9a-z]{24}">/u');
        d('string');

        // Restore settings
        Kint::$cli_detection = $cliDetection;
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTraceWithCSP(): void
    {
        $this->resetServices();

        /** @var App $config */
        $config             = config('App');
        $config->CSPEnabled = true;

        // Initialize Kint
        $app = new MockCodeIgniter($config);
        $app->initialize();

        Kint::$cli_detection = false;

        $this->expectOutputRegex('/<style class="kint-rich-style" nonce="[0-9a-z]{24}">/u');
        trace();
    }

    public function testCspStyleNonce(): void
    {
        $config             = config('App');
        $config->CSPEnabled = true;

        $this->assertStringStartsWith('nonce="', csp_style_nonce());
    }

    public function testCspScriptNonce(): void
    {
        $config             = config('App');
        $config->CSPEnabled = true;

        $this->assertStringStartsWith('nonce="', csp_script_nonce());
    }

    public function testLangOnCLI(): void
    {
        Services::createRequest(new App(), true);

        $message = lang('CLI.generator.fileCreate', ['TestController.php']);

        $this->assertSame('File created: TestController.php', $message);

        $this->resetServices();
    }

    public function testIsWindows(): void
    {
        $this->assertSame(strpos(php_uname(), 'Windows') !== false, is_windows());
        $this->assertSame(defined('PHP_WINDOWS_VERSION_MAJOR'), is_windows());
    }

    public function testIsWindowsUsingMock(): void
    {
        is_windows(true);
        $this->assertTrue(is_windows());
        $this->assertNotFalse(is_windows());

        is_windows(false);
        $this->assertFalse(is_windows());
        $this->assertNotTrue(is_windows());

        is_windows(null);
        $this->assertSame(strpos(php_uname(), 'Windows') !== false, is_windows());
        $this->assertSame(defined('PHP_WINDOWS_VERSION_MAJOR'), is_windows());
    }
}
