<?php

namespace Config;

use CodeIgniter\Format\Format;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockResponse;

class ServicesTest extends CIUnitTestCase
{

	protected $config;
	protected $original;

	protected function setUp(): void
	{
		parent::setUp();

		$this->original = $_SERVER;
		//      $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es; q=1.0, en; q=0.5';
		$this->config = new App();
		//      $this->config->negotiateLocale = true;
		//      $this->config->supportedLocales = ['en', 'es'];
	}

	public function tearDown(): void
	{
		$_SERVER = $this->original;
	}

	public function testNewAutoloader()
	{
		$this->assertInstanceOf(\CodeIgniter\Autoloader\Autoloader::class, Services::autoloader());
	}

	public function testNewUnsharedAutoloader()
	{
		$this->assertInstanceOf(\CodeIgniter\Autoloader\Autoloader::class, Services::autoloader(false));
	}

	public function testNewFileLocator()
	{
		$this->assertInstanceOf(\CodeIgniter\Autoloader\FileLocator::class, Services::locator());
	}

	public function testNewUnsharedFileLocator()
	{
		$this->assertInstanceOf(\CodeIgniter\Autoloader\FileLocator::class, Services::locator(false));
	}

	public function testNewCurlRequest()
	{
		$this->assertInstanceOf(\CodeIgniter\HTTP\CURLRequest::class, Services::curlrequest());
	}

	public function testNewEmail()
	{
		$this->assertInstanceOf(\CodeIgniter\Email\Email::class, Services::email());
	}

	public function testNewUnsharedEmailWithEmptyConfig()
	{
		$this->assertInstanceOf(\CodeIgniter\Email\Email::class, Services::email(null, false));
	}

	public function testNewUnsharedEmailWithNonEmptyConfig()
	{
		$this->assertInstanceOf(\CodeIgniter\Email\Email::class, Services::email(new \Config\Email(), false));
	}

	public function testNewExceptions()
	{
		$this->assertInstanceOf(\CodeIgniter\Debug\Exceptions::class, Services::exceptions(new Exceptions(), Services::request(), Services::response()));
	}

	public function testNewExceptionsWithNullConfig()
	{
		$this->assertInstanceOf(\CodeIgniter\Debug\Exceptions::class, Services::exceptions(null, null, null, false));
	}

	public function testNewIterator()
	{
		$this->assertInstanceOf(\CodeIgniter\Debug\Iterator::class, Services::iterator());
	}

	public function testNewImage()
	{
		$this->assertInstanceOf(\CodeIgniter\Images\ImageHandlerInterface::class, Services::image());
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
		$this->assertInstanceOf(\CodeIgniter\HTTP\Negotiate::class, Services::negotiator(null));
	}

	public function testNewClirequest()
	{
		$this->assertInstanceOf(\CodeIgniter\HTTP\CLIRequest::class, Services::clirequest(null));
	}

	public function testNewUnsharedClirequest()
	{
		$this->assertInstanceOf(\CodeIgniter\HTTP\CLIRequest::class, Services::clirequest(null, false));
	}

	public function testNewLanguage()
	{
		$actual = Services::language();
		$this->assertInstanceOf(\CodeIgniter\Language\Language::class, $actual);
		$this->assertEquals('en', $actual->getLocale());

		Services::language('la');
		$this->assertEquals('la', $actual->getLocale());
	}

	public function testNewUnsharedLanguage()
	{
		$actual = Services::language(null, false);
		$this->assertInstanceOf(\CodeIgniter\Language\Language::class, $actual);
		$this->assertEquals('en', $actual->getLocale());

		Services::language('la', false);
		$this->assertEquals('en', $actual->getLocale());
	}

	public function testNewPager()
	{
		$this->assertInstanceOf(\CodeIgniter\Pager\Pager::class, Services::pager(null));
	}

	public function testNewThrottlerFromShared()
	{
		$this->assertInstanceOf(\CodeIgniter\Throttle\Throttler::class, Services::throttler());
	}

	public function testNewThrottler()
	{
		$this->assertInstanceOf(\CodeIgniter\Throttle\Throttler::class, Services::throttler(false));
	}

	public function testNewToolbar()
	{
		$this->assertInstanceOf(\CodeIgniter\Debug\Toolbar::class, Services::toolbar(null));
	}

	public function testNewUri()
	{
		$this->assertInstanceOf(\CodeIgniter\HTTP\URI::class, Services::uri(null));
	}

	public function testNewValidation()
	{
		$this->assertInstanceOf(\CodeIgniter\Validation\Validation::class, Services::validation(null));
	}

	public function testNewViewcellFromShared()
	{
		$this->assertInstanceOf(\CodeIgniter\View\Cell::class, Services::viewcell());
	}

	public function testNewViewcell()
	{
		$this->assertInstanceOf(\CodeIgniter\View\Cell::class, Services::viewcell(false));
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testNewSession()
	{
		$this->assertInstanceOf(\CodeIgniter\Session\Session::class, Services::session(new \Config\Session()));
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testNewSessionWithNullConfig()
	{
		$this->assertInstanceOf(\CodeIgniter\Session\Session::class, Services::session(null, false));
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testCallStatic()
	{
		// __callStatic should kick in for this but fail
		$actual = \CodeIgniter\Config\Services::SeSsIoNs(null, false);
		$this->assertNull($actual);
		// __callStatic should kick in for this
		$actual = \CodeIgniter\Config\Services::SeSsIoN(null, false);
		$this->assertInstanceOf(\CodeIgniter\Session\Session::class, $actual);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testCallStaticDirectly()
	{
		$this->assertInstanceOf(\CodeIgniter\Session\Session::class, \CodeIgniter\Config\Services::__callStatic('SeSsIoN', [null, false]));
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

		$this->assertEquals($response, $response2);
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

	public function testFilters()
	{
		$this->assertInstanceOf(\CodeIgniter\Filters\Filters::class, Services::filters());
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
		$this->assertInstanceOf(\CodeIgniter\Honeypot\Honeypot::class, Services::honeypot());
	}

	public function testMigrations()
	{
		$this->assertInstanceOf(\CodeIgniter\Database\MigrationRunner::class, Services::migrations());
	}

	public function testParser()
	{
		$this->assertInstanceOf(\CodeIgniter\View\Parser::class, Services::parser());
	}

	public function testRedirectResponse()
	{
		$this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, Services::redirectResponse());
	}

	public function testRoutes()
	{
		$this->assertInstanceOf(\CodeIgniter\Router\RouteCollection::class, Services::routes());
	}

	public function testRouter()
	{
		$this->assertInstanceOf(\CodeIgniter\Router\Router::class, Services::router());
	}

	public function testSecurity()
	{
		$this->assertInstanceOf(\CodeIgniter\Security\Security::class, Services::security());
	}

	public function testTimer()
	{
		$this->assertInstanceOf(\CodeIgniter\Debug\Timer::class, Services::timer());
	}

	public function testTypography()
	{
		$this->assertInstanceOf(\CodeIgniter\Typography\Typography::class, Services::typography());
	}

	public function testServiceInstance()
	{
		rename(COMPOSER_PATH, COMPOSER_PATH . '.backup');
		$this->assertInstanceOf(\Config\Services::class, new \Config\Services());
		rename(COMPOSER_PATH . '.backup', COMPOSER_PATH);
	}

}
