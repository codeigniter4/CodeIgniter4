<?php
namespace Config;

use Tests\Support\HTTP\MockResponse;

class ServicesTest extends \CIUnitTestCase
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
		$actual = Services::autoloader();
		$this->assertInstanceOf(\CodeIgniter\Autoloader\Autoloader::class, $actual);
	}

	public function testNewUnsharedAutoloader()
	{
		$actual = Services::autoloader(false);
		$this->assertInstanceOf(\CodeIgniter\Autoloader\Autoloader::class, $actual);
	}

	public function testNewFileLocator()
	{
		$actual = Services::locator();
		$this->assertInstanceOf(\CodeIgniter\Autoloader\FileLocator::class, $actual);
	}

	public function testNewUnsharedFileLocator()
	{
		$actual = Services::locator(false);
		$this->assertInstanceOf(\CodeIgniter\Autoloader\FileLocator::class, $actual);
	}

	public function testNewCurlRequest()
	{
		$actual = Services::curlrequest();
		$this->assertInstanceOf(\CodeIgniter\HTTP\CURLRequest::class, $actual);
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
		$this->assertInstanceOf(\CodeIgniter\Debug\Iterator::class, $actual);
	}

	public function testNewImage()
	{
		$actual = Services::image();
		$this->assertInstanceOf(\CodeIgniter\Images\ImageHandlerInterface::class, $actual);
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
		$this->assertInstanceOf(\CodeIgniter\HTTP\Negotiate::class, $actual);
	}

	public function testNewClirequest()
	{
		$actual = Services::clirequest(null);
		$this->assertInstanceOf(\CodeIgniter\HTTP\CLIRequest::class, $actual);
	}

	public function testNewUnsharedClirequest()
	{
		$actual = Services::clirequest(null, false);
		$this->assertInstanceOf(\CodeIgniter\HTTP\CLIRequest::class, $actual);
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
		$actual = Services::pager(null);
		$this->assertInstanceOf(\CodeIgniter\Pager\Pager::class, $actual);
	}

	public function testNewThrottlerFromShared()
	{
		$actual = Services::throttler();
		$this->assertInstanceOf(\CodeIgniter\Throttle\Throttler::class, $actual);
	}

	public function testNewThrottler()
	{
		$actual = Services::throttler(false);
		$this->assertInstanceOf(\CodeIgniter\Throttle\Throttler::class, $actual);
	}

	public function testNewToolbar()
	{
		$actual = Services::toolbar(null);
		$this->assertInstanceOf(\CodeIgniter\Debug\Toolbar::class, $actual);
	}

	public function testNewUri()
	{
		$actual = Services::uri(null);
		$this->assertInstanceOf(\CodeIgniter\HTTP\URI::class, $actual);
	}

	public function testNewValidation()
	{
		$actual = Services::validation(null);
		$this->assertInstanceOf(\CodeIgniter\Validation\Validation::class, $actual);
	}

	public function testNewViewcellFromShared()
	{
		$actual = Services::viewcell();
		$this->assertInstanceOf(\CodeIgniter\View\Cell::class, $actual);
	}

	public function testNewViewcell()
	{
		$actual = Services::viewcell(false);
		$this->assertInstanceOf(\CodeIgniter\View\Cell::class, $actual);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testNewSession()
	{
		$actual = Services::session($this->config);
		$this->assertInstanceOf(\CodeIgniter\Session\Session::class, $actual);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testNewSessionWithNullConfig()
	{
		$actual = Services::session(null, false);
		$this->assertInstanceOf(\CodeIgniter\Session\Session::class, $actual);
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
		//      $actual = \CodeIgniter\Config\Services::SeSsIoN(null, false); // original
		$actual = \CodeIgniter\Config\Services::__callStatic('SeSsIoN', [null, false]);
		$this->assertInstanceOf(\CodeIgniter\Session\Session::class, $actual);
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
		$result = Services::filters();
		$this->assertInstanceOf(\CodeIgniter\Filters\Filters::class, $result);
	}

	public function testHoneypot()
	{
		$result = Services::honeypot();
		$this->assertInstanceOf(\CodeIgniter\Honeypot\Honeypot::class, $result);
	}

	public function testMigrations()
	{
		$result = Services::migrations();
		$this->assertInstanceOf(\CodeIgniter\Database\MigrationRunner::class, $result);
	}

	public function testParser()
	{
		$result = Services::parser();
		$this->assertInstanceOf(\CodeIgniter\View\Parser::class, $result);
	}

	public function testRedirectResponse()
	{
		$result = Services::redirectResponse();
		$this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
	}

	public function testRoutes()
	{
		$result = Services::routes();
		$this->assertInstanceOf(\CodeIgniter\Router\RouteCollection::class, $result);
	}

	public function testRouter()
	{
		$result = Services::router();
		$this->assertInstanceOf(\CodeIgniter\Router\Router::class, $result);
	}

	public function testSecurity()
	{
		$result = Services::security();
		$this->assertInstanceOf(\CodeIgniter\Security\Security::class, $result);
	}

	public function testTimer()
	{
		$result = Services::timer();
		$this->assertInstanceOf(\CodeIgniter\Debug\Timer::class, $result);
	}

	public function testTypography()
	{
		$result = Services::typography();
		$this->assertInstanceOf(\CodeIgniter\Typography\Typography::class, $result);
	}

}
