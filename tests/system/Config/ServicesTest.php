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

namespace CodeIgniter\Config;

use AfterAutoloadModule\Test;
use CodeIgniter\Autoloader\Autoloader;
use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Debug\Iterator;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Debug\Toolbar;
use CodeIgniter\Email\Email;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Filters\Filters;
use CodeIgniter\Format\Format;
use CodeIgniter\Honeypot\Honeypot;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Negotiate;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Images\ImageHandlerInterface;
use CodeIgniter\Language\Language;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Router\Router;
use CodeIgniter\Security\Security;
use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockResponse;
use CodeIgniter\Test\Mock\MockSecurity;
use CodeIgniter\Throttle\Throttler;
use CodeIgniter\Typography\Typography;
use CodeIgniter\Validation\Validation;
use CodeIgniter\View\Cell;
use CodeIgniter\View\Parser;
use Config\App;
use Config\Database as DatabaseConfig;
use Config\Exceptions;
use Config\Security as SecurityConfig;
use Config\Session as ConfigSession;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Tests\Support\Config\Services;

/**
 * @internal
 */
#[Group('SeparateProcess')]
final class ServicesTest extends CIUnitTestCase
{
    private array $original;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();

        $this->original = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->original;
        $this->resetServices();
    }

    public function testCanReplaceFrameworkServices(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service originated from Tests\Support\Config\Services');

        Services::uri('testCanReplaceFrameworkServices');
    }

    public function testNewAutoloader(): void
    {
        $actual = Services::autoloader();
        $this->assertInstanceOf(Autoloader::class, $actual);
    }

    public function testNewUnsharedAutoloader(): void
    {
        $actual = Services::autoloader(false);
        $this->assertInstanceOf(Autoloader::class, $actual);
    }

    public function testNewFileLocator(): void
    {
        $actual = Services::locator();
        $this->assertInstanceOf(FileLocator::class, $actual);
    }

    public function testNewUnsharedFileLocator(): void
    {
        $actual = Services::locator(false);
        $this->assertInstanceOf(FileLocator::class, $actual);
    }

    public function testNewCurlRequest(): void
    {
        $actual = Services::curlrequest();
        $this->assertInstanceOf(CURLRequest::class, $actual);
    }

    public function testNewEmail(): void
    {
        $actual = Services::email();
        $this->assertInstanceOf(Email::class, $actual);
    }

    public function testNewUnsharedEmailWithEmptyConfig(): void
    {
        $actual = Services::email(null, false);
        $this->assertInstanceOf(Email::class, $actual);
    }

    public function testNewUnsharedEmailWithNonEmptyConfig(): void
    {
        $actual = Services::email(new \Config\Email(), false);
        $this->assertInstanceOf(Email::class, $actual);
    }

    public function testNewExceptions(): void
    {
        $actual = Services::exceptions(new Exceptions());
        $this->assertInstanceOf(\CodeIgniter\Debug\Exceptions::class, $actual);
    }

    public function testNewExceptionsWithNullConfig(): void
    {
        $actual = Services::exceptions(null, false);
        $this->assertInstanceOf(\CodeIgniter\Debug\Exceptions::class, $actual);
    }

    public function testNewIterator(): void
    {
        $actual = Services::iterator();
        $this->assertInstanceOf(Iterator::class, $actual);
    }

    public function testNewImage(): void
    {
        $actual = Services::image();
        $this->assertInstanceOf(ImageHandlerInterface::class, $actual);
    }

    public function testNewNegotiatorWithNullConfig(): void
    {
        $actual = Services::negotiator(null);
        $this->assertInstanceOf(Negotiate::class, $actual);
    }

    public function testNewClirequest(): void
    {
        $actual = Services::clirequest(null);
        $this->assertInstanceOf(CLIRequest::class, $actual);
    }

    public function testNewUnsharedClirequest(): void
    {
        $actual = Services::clirequest(null, false);
        $this->assertInstanceOf(CLIRequest::class, $actual);
    }

    public function testNewLanguage(): void
    {
        $actual = Services::language();
        $this->assertInstanceOf(Language::class, $actual);
        $this->assertSame('en', $actual->getLocale());

        Services::language('la');
        $this->assertSame('la', $actual->getLocale());
    }

    public function testNewUnsharedLanguage(): void
    {
        $actual = Services::language(null, false);
        $this->assertInstanceOf(Language::class, $actual);
        $this->assertSame('en', $actual->getLocale());

        Services::language('la', false);
        $this->assertSame('en', $actual->getLocale());
    }

    public function testNewPager(): void
    {
        $actual = Services::pager(null);
        $this->assertInstanceOf(Pager::class, $actual);
    }

    public function testNewThrottlerFromShared(): void
    {
        $actual = Services::throttler();
        $this->assertInstanceOf(Throttler::class, $actual);
    }

    public function testNewThrottler(): void
    {
        $actual = Services::throttler(false);
        $this->assertInstanceOf(Throttler::class, $actual);
    }

    public function testNewToolbar(): void
    {
        $actual = service('toolbar', null);
        $this->assertInstanceOf(Toolbar::class, $actual);
    }

    public function testNewUri(): void
    {
        $actual = Services::uri(null);
        $this->assertInstanceOf(URI::class, $actual);
    }

    public function testNewValidation(): void
    {
        $actual = Services::validation(null);
        $this->assertInstanceOf(Validation::class, $actual);
    }

    public function testNewViewcellFromShared(): void
    {
        $actual = Services::viewcell();
        $this->assertInstanceOf(Cell::class, $actual);
    }

    public function testNewViewcell(): void
    {
        $actual = Services::viewcell(false);
        $this->assertInstanceOf(Cell::class, $actual);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testNewSession(): void
    {
        $actual = Services::session();
        $this->assertInstanceOf(Session::class, $actual);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testNewSessionWithNullConfig(): void
    {
        $actual = Services::session(null, false);
        $this->assertInstanceOf(Session::class, $actual);
    }

    #[DataProvider('provideNewSessionWithInvalidHandler')]
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testNewSessionWithInvalidHandler(string $driver): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid session handler "%s" provided.', $driver));

        $config = new ConfigSession();

        $config->driver = $driver;
        Services::session($config, false);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideNewSessionWithInvalidHandler(): iterable
    {
        yield 'just a string' => ['file'];

        yield 'inexistent class' => ['Foo'];

        yield 'other class' => [self::class];
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testNewSessionWithInvalidDatabaseHandler(): void
    {
        $driver = config(DatabaseConfig::class)->tests['DBDriver'];

        if (in_array($driver, ['MySQLi', 'Postgre'], true)) {
            $this->markTestSkipped('This test case does not work with MySQLi and Postgre');
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid session database handler "%s" provided. Only "MySQLi" and "Postgre" are supported.', $driver));

        $config = new ConfigSession();

        $config->driver = DatabaseHandler::class;
        Services::session($config, false);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testCallStatic(): void
    {
        // __callStatic should kick in for this but fail
        $actual = Services::SeSsIoNs(null, false);
        $this->assertNull($actual);
        // __callStatic should kick in for this
        $actual = Services::SeSsIoN(null, false);
        $this->assertInstanceOf(Session::class, $actual);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testCallStaticDirectly(): void
    {
        //      $actual = \CodeIgniter\Config\Services::SeSsIoN(null, false); // original
        $actual = Services::__callStatic('SeSsIoN', [null, false]);
        $this->assertInstanceOf(Session::class, $actual);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testMockInjection(): void
    {
        Services::injectMock('response', new MockResponse(new App()));
        $response = service('response');
        $this->assertInstanceOf(MockResponse::class, $response);

        Services::injectMock('response', new MockResponse(new App()));
        $response2 = service('response');
        $this->assertInstanceOf(MockResponse::class, $response2);

        Services::injectMock('response', $response);
        $response3 = service('response');
        $this->assertInstanceOf(MockResponse::class, $response3);

        $this->assertNotSame($response, $response2);
        $this->assertSame($response, $response3);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testReset(): void
    {
        Services::injectMock('response', new MockResponse(new App()));
        $response = service('response');
        $this->assertInstanceOf(MockResponse::class, $response);

        Services::reset(true); // reset mocks & shared instances

        Services::injectMock('response', new MockResponse(new App()));
        $response2 = service('response');
        $this->assertInstanceOf(MockResponse::class, $response2);

        $this->assertNotSame($response2, $response);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testResetSingle(): void
    {
        Services::injectMock('response', new MockResponse(new App()));
        Services::injectMock('security', new MockSecurity(new SecurityConfig()));
        $response = service('response');
        $security = service('security');
        $this->assertInstanceOf(MockResponse::class, $response);
        $this->assertInstanceOf(MockSecurity::class, $security);

        Services::resetSingle('response');

        $response2 = service('response');
        $security2 = service('security');
        $this->assertNotInstanceOf(MockResponse::class, $response2);
        $this->assertInstanceOf(MockSecurity::class, $security2);

        $this->assertNotSame($response, $response2);
        $this->assertSame($security, $security2);
    }

    public function testResetSingleCaseInsensitive(): void
    {
        Services::injectMock('response', new MockResponse(new App()));
        $someService = service('response');
        $this->assertInstanceOf(MockResponse::class, $someService);

        Services::resetSingle('Response');
        $someService = service('response');
        $this->assertNotInstanceOf(MockResponse::class, $someService);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testResetServiceCache(): void
    {
        Services::injectMock('response', new MockResponse(new App()));
        $response = service('response');
        $this->assertInstanceOf(MockResponse::class, $response);
        service('response')->setStatusCode(200);

        Services::autoloader()->addNamespace(
            'AfterAutoloadModule',
            SUPPORTPATH . '_AfterAutoloadModule/',
        );
        Services::resetServicesCache();

        $response = service('response');
        $this->assertInstanceOf(MockResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $test = service('test');
        $this->assertInstanceOf(Test::class, $test);
    }

    public function testFilters(): void
    {
        $result = Services::filters();
        $this->assertInstanceOf(Filters::class, $result);
    }

    public function testFormat(): void
    {
        $this->assertInstanceOf(Format::class, service('format'));
    }

    public function testUnsharedFormat(): void
    {
        $this->assertInstanceOf(Format::class, Services::format(null, false));
    }

    public function testHoneypot(): void
    {
        $result = Services::honeypot();
        $this->assertInstanceOf(Honeypot::class, $result);
    }

    public function testMigrations(): void
    {
        $result = Services::migrations();
        $this->assertInstanceOf(MigrationRunner::class, $result);
    }

    public function testParser(): void
    {
        $result = Services::parser();
        $this->assertInstanceOf(Parser::class, $result);
    }

    public function testRedirectResponse(): void
    {
        $result = Services::redirectResponse();
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testRoutes(): void
    {
        $result = Services::routes();
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testRouter(): void
    {
        $result = Services::router();
        $this->assertInstanceOf(Router::class, $result);
    }

    public function testSecurity(): void
    {
        Services::injectMock('security', new MockSecurity(new SecurityConfig()));

        $result = Services::security();
        $this->assertInstanceOf(Security::class, $result);
    }

    public function testTimer(): void
    {
        $result = Services::timer();
        $this->assertInstanceOf(Timer::class, $result);
    }

    public function testTypography(): void
    {
        $result = Services::typography();
        $this->assertInstanceOf(Typography::class, $result);
    }

    public function testServiceInstance(): void
    {
        rename(COMPOSER_PATH, COMPOSER_PATH . '.backup');
        $this->assertInstanceOf(\Config\Services::class, new \Config\Services());
        rename(COMPOSER_PATH . '.backup', COMPOSER_PATH);
    }
}
