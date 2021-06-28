<?php

namespace CodeIgniter\Config;

use CodeIgniter\Autoloader\Autoloader;
use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Debug\Iterator;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Debug\Toolbar;
use CodeIgniter\Email\Email;
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
use Config\Exceptions;
use Tests\Support\Config\Services as Services;

/**
 * @internal
 */
final class ServicesTest extends CIUnitTestCase
{
    protected $config;
    protected $original;

    protected function setUp(): void
    {
        parent::setUp();

        $this->original = $_SERVER;
        $this->config   = new App();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->original;
        Services::reset();
    }

    public function testCanReplaceFrameworkServices()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Service originated from Tests\Support\Config\Services');

        Services::uri('testCanReplaceFrameworkServices');
    }

    public function testNewAutoloader()
    {
        $actual = Services::autoloader();
        $this->assertInstanceOf(Autoloader::class, $actual);
    }

    public function testNewUnsharedAutoloader()
    {
        $actual = Services::autoloader(false);
        $this->assertInstanceOf(Autoloader::class, $actual);
    }

    public function testNewFileLocator()
    {
        $actual = Services::locator();
        $this->assertInstanceOf(FileLocator::class, $actual);
    }

    public function testNewUnsharedFileLocator()
    {
        $actual = Services::locator(false);
        $this->assertInstanceOf(FileLocator::class, $actual);
    }

    public function testNewCurlRequest()
    {
        $actual = Services::curlrequest();
        $this->assertInstanceOf(CURLRequest::class, $actual);
    }

    public function testNewEmail()
    {
        $actual = Services::email();
        $this->assertInstanceOf(Email::class, $actual);
    }

    public function testNewUnsharedEmailWithEmptyConfig()
    {
        $actual = Services::email(null, false);
        $this->assertInstanceOf(Email::class, $actual);
    }

    public function testNewUnsharedEmailWithNonEmptyConfig()
    {
        $actual = Services::email(new \Config\Email(), false);
        $this->assertInstanceOf(Email::class, $actual);
    }

    public function testNewExceptions()
    {
        $actual = Services::exceptions(new Exceptions(), Services::request(), Services::response());
        $this->assertInstanceOf(\CodeIgniter\Debug\Exceptions::class, $actual);
    }

    public function testNewExceptionsWithNullConfig()
    {
        $actual = Services::exceptions(null, null, null, false);
        $this->assertInstanceOf(\CodeIgniter\Debug\Exceptions::class, $actual);
    }

    public function testNewIterator()
    {
        $actual = Services::iterator();
        $this->assertInstanceOf(Iterator::class, $actual);
    }

    public function testNewImage()
    {
        $actual = Services::image();
        $this->assertInstanceOf(ImageHandlerInterface::class, $actual);
    }

    //  public function testNewMigrationRunner()
    //  {
    //      //FIXME - docs aren't clear about setting this up to just make sure that the service
    //      // returns a MigrationRunner
    //      $config = new \Config\Migrations();
    //      $db = new \CodeIgniter\Database\MockConnection([]);
    //      $this->expectException('InvalidArgumentException');
    //      $actual = Services::migrations($config, $db);
    //      $this->assertInstanceOf(\CodeIgniter\Database\MigrationRunner::class, $actual);
    //  }
    //
    public function testNewNegotiatorWithNullConfig()
    {
        $actual = Services::negotiator(null);
        $this->assertInstanceOf(Negotiate::class, $actual);
    }

    public function testNewClirequest()
    {
        $actual = Services::clirequest(null);
        $this->assertInstanceOf(CLIRequest::class, $actual);
    }

    public function testNewUnsharedClirequest()
    {
        $actual = Services::clirequest(null, false);
        $this->assertInstanceOf(CLIRequest::class, $actual);
    }

    public function testNewLanguage()
    {
        $actual = Services::language();
        $this->assertInstanceOf(Language::class, $actual);
        $this->assertSame('en', $actual->getLocale());

        Services::language('la');
        $this->assertSame('la', $actual->getLocale());
    }

    public function testNewUnsharedLanguage()
    {
        $actual = Services::language(null, false);
        $this->assertInstanceOf(Language::class, $actual);
        $this->assertSame('en', $actual->getLocale());

        Services::language('la', false);
        $this->assertSame('en', $actual->getLocale());
    }

    public function testNewPager()
    {
        $actual = Services::pager(null);
        $this->assertInstanceOf(Pager::class, $actual);
    }

    public function testNewThrottlerFromShared()
    {
        $actual = Services::throttler();
        $this->assertInstanceOf(Throttler::class, $actual);
    }

    public function testNewThrottler()
    {
        $actual = Services::throttler(false);
        $this->assertInstanceOf(Throttler::class, $actual);
    }

    public function testNewToolbar()
    {
        $actual = Services::toolbar(null);
        $this->assertInstanceOf(Toolbar::class, $actual);
    }

    public function testNewUri()
    {
        $actual = Services::uri(null);
        $this->assertInstanceOf(URI::class, $actual);
    }

    public function testNewValidation()
    {
        $actual = Services::validation(null);
        $this->assertInstanceOf(Validation::class, $actual);
    }

    public function testNewViewcellFromShared()
    {
        $actual = Services::viewcell();
        $this->assertInstanceOf(Cell::class, $actual);
    }

    public function testNewViewcell()
    {
        $actual = Services::viewcell(false);
        $this->assertInstanceOf(Cell::class, $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testNewSession()
    {
        $actual = Services::session($this->config);
        $this->assertInstanceOf(Session::class, $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testNewSessionWithNullConfig()
    {
        $actual = Services::session(null, false);
        $this->assertInstanceOf(Session::class, $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testCallStatic()
    {
        // __callStatic should kick in for this but fail
        $actual = Services::SeSsIoNs(null, false);
        $this->assertNull($actual);
        // __callStatic should kick in for this
        $actual = Services::SeSsIoN(null, false);
        $this->assertInstanceOf(Session::class, $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testCallStaticDirectly()
    {
        //      $actual = \CodeIgniter\Config\Services::SeSsIoN(null, false); // original
        $actual = Services::__callStatic('SeSsIoN', [null, false]);
        $this->assertInstanceOf(Session::class, $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testMockInjection()
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testReset()
    {
        Services::injectMock('response', new MockResponse(new App()));
        $response = service('response');
        $this->assertInstanceOf(MockResponse::class, $response);

        Services::reset(true); // reset mocks & shared instances

        Services::injectMock('response', new MockResponse(new App()));
        $response2 = service('response');
        $this->assertInstanceOf(MockResponse::class, $response2);

        $this->assertTrue($response !== $response2);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testResetSingle()
    {
        Services::injectMock('response', new MockResponse(new App()));
        Services::injectMock('security', new MockSecurity(new App()));
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

    public function testFilters()
    {
        $result = Services::filters();
        $this->assertInstanceOf(Filters::class, $result);
    }

    public function testFormat()
    {
        $this->assertInstanceOf(Format::class, Services::format());
    }

    public function testUnsharedFormat()
    {
        $this->assertInstanceOf(Format::class, Services::format(null, false));
    }

    public function testHoneypot()
    {
        $result = Services::honeypot();
        $this->assertInstanceOf(Honeypot::class, $result);
    }

    public function testMigrations()
    {
        $result = Services::migrations();
        $this->assertInstanceOf(MigrationRunner::class, $result);
    }

    public function testParser()
    {
        $result = Services::parser();
        $this->assertInstanceOf(Parser::class, $result);
    }

    public function testRedirectResponse()
    {
        $result = Services::redirectResponse();
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testRoutes()
    {
        $result = Services::routes();
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testRouter()
    {
        $result = Services::router();
        $this->assertInstanceOf(Router::class, $result);
    }

    public function testSecurity()
    {
        $result = Services::security();
        $this->assertInstanceOf(Security::class, $result);
    }

    public function testTimer()
    {
        $result = Services::timer();
        $this->assertInstanceOf(Timer::class, $result);
    }

    public function testTypography()
    {
        $result = Services::typography();
        $this->assertInstanceOf(Typography::class, $result);
    }

    public function testServiceInstance()
    {
        rename(COMPOSER_PATH, COMPOSER_PATH . '.backup');
        $this->assertInstanceOf(\Config\Services::class, new \Config\Services());
        rename(COMPOSER_PATH . '.backup', COMPOSER_PATH);
    }
}
