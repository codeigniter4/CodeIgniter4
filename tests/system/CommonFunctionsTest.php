<?php

namespace CodeIgniter;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Session\Handlers\FileHandler;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\Test\Mock\MockSession;
use CodeIgniter\Test\TestLogger;
use Config\App;
use Config\Logger;
use Config\Modules;
use InvalidArgumentException;
use RuntimeException;
use stdClass;
use Tests\Support\Autoloader\FatalLocator;
use Tests\Support\Models\JobModel;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class CommonFunctionsTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $renderer = Services::renderer();
        $renderer->resetData();
        unset($_ENV['foo'], $_SERVER['foo']);
    }

    public function testStringifyAttributes()
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

    public function testStringifyJsAttributes()
    {
        $this->assertSame('width=800,height=600', stringify_attributes(['width' => '800', 'height' => '600'], true));

        $atts         = new stdClass();
        $atts->width  = 800;
        $atts->height = 600;
        $this->assertSame('width=800,height=600', stringify_attributes($atts, true));
    }

    public function testEnvReturnsDefault()
    {
        $this->assertSame('baz', env('foo', 'baz'));
    }

    public function testEnvGetsFromSERVER()
    {
        $_SERVER['foo'] = 'bar';

        $this->assertSame('bar', env('foo', 'baz'));
    }

    public function testEnvGetsFromENV()
    {
        $_ENV['foo'] = 'bar';

        $this->assertSame('bar', env('foo', 'baz'));
    }

    public function testEnvBooleans()
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

    public function testRedirectReturnsRedirectResponse()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $response = $this->createMock(Response::class);
        $routes   = new RouteCollection(
            Services::locator(),
            new Modules()
        );
        \CodeIgniter\Services::injectMock('response', $response);
        \CodeIgniter\Services::injectMock('routes', $routes);

        $routes->add('home/base', 'Controller::index', ['as' => 'base']);
        $response->method('redirect')->willReturnArgument(0);

        $this->assertInstanceOf(RedirectResponse::class, redirect('base'));
    }

    public function testRedirectDefault()
    {
        $this->assertInstanceOf(RedirectResponse::class, redirect());
    }

    public function testView()
    {
        $data = [
            'testString' => 'bar',
            'bar'        => 'baz',
        ];
        $expected = '<h1>bar</h1>';
        $this->assertStringContainsString($expected, view('\Tests\Support\View\Views\simple', $data));
    }

    public function testViewSavedData()
    {
        $data = [
            'testString' => 'bar',
            'bar'        => 'baz',
        ];
        $expected = '<h1>bar</h1>';
        $this->assertStringContainsString($expected, view('\Tests\Support\View\Views\simple', $data, ['saveData' => true]));
        $this->assertStringContainsString($expected, view('\Tests\Support\View\Views\simple'));
    }

    public function testViewCell()
    {
        $expected = 'Hello';
        $this->assertSame($expected, view_cell('\Tests\Support\View\SampleClass::hello'));
    }

    public function testEscapeWithDifferentEncodings()
    {
        $this->assertSame('&lt;x', esc('<x', 'html', 'utf-8'));
        $this->assertSame('&lt;x', esc('<x', 'html', 'iso-8859-1'));
        $this->assertSame('&lt;x', esc('<x', 'html', 'windows-1251'));
    }

    public function testEscapeBadContext()
    {
        $this->expectException(InvalidArgumentException::class);
        esc(['width' => '800', 'height' => '600'], 'bogus');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testSessionInstance()
    {
        $this->injectSessionMock();

        $this->assertInstanceOf(Session::class, session());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testSessionVariable()
    {
        $this->injectSessionMock();

        $_SESSION['notbogus'] = 'Hi there';

        $this->assertSame('Hi there', session('notbogus'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testSessionVariableNotThere()
    {
        $this->injectSessionMock();

        $_SESSION['bogus'] = 'Hi there';
        $this->assertNull(session('notbogus'));
    }

    public function testRouteTo()
    {
        // prime the pump
        $routes = service('routes');
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $this->assertSame('/path/string/to/13', route_to('myController::goto', 'string', 13));
    }

    public function testInvisible()
    {
        $this->assertSame('Javascript', remove_invisible_characters("Java\0script"));
    }

    public function testInvisibleEncoded()
    {
        $this->assertSame('Javascript', remove_invisible_characters('Java%0cscript'));
    }

    public function testAppTimezone()
    {
        $this->assertSame('America/Chicago', app_timezone());
    }

    public function testCSRFToken()
    {
        $this->assertSame('csrf_test_name', csrf_token());
    }

    public function testCSRFHeader()
    {
        $this->assertSame('X-CSRF-TOKEN', csrf_header());
    }

    public function testHash()
    {
        $this->assertSame(32, strlen(csrf_hash()));
    }

    public function testCSRFField()
    {
        $this->assertStringContainsString('<input type="hidden" ', csrf_field());
    }

    public function testCSRFMeta()
    {
        $this->assertStringContainsString('<meta name="X-CSRF-TOKEN" ', csrf_meta());
    }

    public function testModelNotExists()
    {
        $this->assertNull(model(UnexsistenceClass::class));
    }

    public function testModelExistsBasename()
    {
        $this->assertInstanceOf(JobModel::class, model('JobModel'));
    }

    public function testModelExistsClassname()
    {
        $this->assertInstanceOf(JobModel::class, model(JobModel::class));
    }

    public function testModelExistsAbsoluteClassname()
    {
        $this->assertInstanceOf(JobModel::class, model('\Tests\Support\Models\JobModel'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testOldInput()
    {
        $this->injectSessionMock();
        // setup from RedirectResponseTest...
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->config          = new App();
        $this->config->baseURL = 'http://example.com/';

        $this->routes = new RouteCollection(Services::locator(), new Modules());
        Services::injectMock('routes', $this->routes);

        $this->request = new MockIncomingRequest($this->config, new URI('http://example.com'), null, new UserAgent());
        Services::injectMock('request', $this->request);

        // setup & ask for a redirect...
        $_SESSION = [];
        $_GET     = ['foo' => 'bar'];
        $_POST    = [
            'bar'    => 'baz',
            'zibble' => serialize('fritz'),
        ];

        $response = new RedirectResponse(new App());
        $response->withInput();

        $this->assertSame('bar', old('foo')); // regular parameter
        $this->assertSame('doo', old('yabba dabba', 'doo')); // non-existing parameter
        $this->assertSame('fritz', old('zibble')); // serialized parameter
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1492
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testOldInputArray()
    {
        $this->injectSessionMock();
        // setup from RedirectResponseTest...
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->config          = new App();
        $this->config->baseURL = 'http://example.com/';

        $this->routes = new RouteCollection(Services::locator(), new Modules());
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

    public function testReallyWritable()
    {
        // cannot test fully on *nix
        $this->assertTrue(is_really_writable(WRITEPATH));
    }

    public function testSlashItem()
    {
        $this->assertSame('/', slash_item('cookiePath')); // slash already there
        $this->assertSame('', slash_item('cookieDomain')); // empty, so untouched
        $this->assertSame('en/', slash_item('defaultLocale')); // slash appended
    }

    protected function injectSessionMock()
    {
        $defaults = [
            'sessionDriver'            => 'CodeIgniter\Session\Handlers\FileHandler',
            'sessionCookieName'        => 'ci_session',
            'sessionExpiration'        => 7200,
            'sessionSavePath'          => null,
            'sessionMatchIP'           => false,
            'sessionTimeToUpdate'      => 300,
            'sessionRegenerateDestroy' => false,
            'cookieDomain'             => '',
            'cookiePrefix'             => '',
            'cookiePath'               => '/',
            'cookieSecure'             => false,
            'cookieSameSite'           => 'Lax',
        ];

        $appConfig = new App();

        foreach ($defaults as $key => $config) {
            $appConfig->{$key} = $config;
        }

        $session = new MockSession(new FileHandler($appConfig, '127.0.0.1'), $appConfig);
        $session->setLogger(new TestLogger(new Logger()));
        BaseService::injectMock('session', $session);
    }

    // Make sure cookies are set by RedirectResponse this way
    // See https://github.com/codeigniter4/CodeIgniter4/issues/1393
    public function testRedirectResponseCookies1()
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

    public function testTrace()
    {
        ob_start();
        trace();
        $content = ob_get_clean();

        $this->assertStringContainsString('Debug Backtrace', $content);
    }

    public function testViewNotSaveData()
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
     * @preserveGlobalState  disabled
     */
    public function testForceHttpsNullRequestAndResponse()
    {
        $this->assertNull(Services::response()->header('Location'));

        force_https();

        $this->assertSame('https://example.com/', Services::response()->header('Location')->getValue());
    }

    /**
     * @dataProvider dirtyPathsProvider
     */
    public function testCleanPathActuallyCleaningThePaths($input, $expected)
    {
        $this->assertSame($expected, clean_path($input));
    }

    public function dirtyPathsProvider()
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

    public function testHelperWithFatalLocatorThrowsException()
    {
        // Replace the locator with one that will fail if it is called
        $locator = new FatalLocator(Services::autoloader());
        Services::injectMock('locator', $locator);

        try {
            helper('baguette');
            $exception = false;
        } catch (RuntimeException $e) {
            $exception = true;
        }

        $this->assertTrue($exception);
        Services::reset();
    }

    public function testHelperLoadsOnce()
    {
        // Load it the first time
        helper('baguette');

        // Replace the locator with one that will fail if it is called
        $locator = new FatalLocator(Services::autoloader());
        Services::injectMock('locator', $locator);

        try {
            helper('baguette');
            $exception = false;
        } catch (RuntimeException $e) {
            $exception = true;
        }

        $this->assertFalse($exception);
        Services::reset();
    }

    public function testIsCli()
    {
        $this->assertIsBool(is_cli());
        $this->assertTrue(is_cli());
    }
}
