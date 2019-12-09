<?php
namespace CodeIgniter\HTTP;

use Config\App;
use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * @backupGlobals enabled
 */
class IncomingRequestTest extends \CIUnitTestCase
{

	/**
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;

	protected function setUp(): void
	{
		parent::setUp();

		$this->request = new IncomingRequest(new App(), new URI(), null, new UserAgent());

		$_POST = $_GET = $_SERVER = $_REQUEST = $_ENV = $_COOKIE = $_SESSION = [];
	}

	//--------------------------------------------------------------------

	public function testCanGrabRequestVars()
	{
		$_REQUEST['TEST'] = 5;

		$this->assertEquals(5, $this->request->getVar('TEST'));
		$this->assertNull($this->request->getVar('TESTY'));
	}

	public function testCanGrabGetVars()
	{
		$_GET['TEST'] = 5;

		$this->assertEquals(5, $this->request->getGet('TEST'));
		$this->assertNull($this->request->getGEt('TESTY'));
	}

	public function testCanGrabPostVars()
	{
		$_POST['TEST'] = 5;

		$this->assertEquals(5, $this->request->getPost('TEST'));
		$this->assertNull($this->request->getPost('TESTY'));
	}

	public function testCanGrabPostBeforeGet()
	{
		$_POST['TEST'] = 5;
		$_GET['TEST']  = 3;

		$this->assertEquals(5, $this->request->getPostGet('TEST'));
		$this->assertEquals(3, $this->request->getGetPost('TEST'));
	}

	//--------------------------------------------------------------------

	public function testNoOldInput()
	{
		$this->assertNull($this->request->getOldInput('name'));
	}

	public function testCanGetOldInput()
	{
		$_SESSION['_ci_old_input'] = [
			'get'  => ['one' => 'two'],
			'post' => ['name' => 'foo'],
		];

		$this->assertEquals('foo', $this->request->getOldInput('name'));
		$this->assertEquals('two', $this->request->getOldInput('one'));
	}

	public function testCanGetOldInputDotted()
	{
		$_SESSION['_ci_old_input'] = [
			'get'  => ['apple' => ['name' => 'two']],
			'post' => ['banana' => ['name' => 'foo']],
		];

		$this->assertEquals('foo', $this->request->getOldInput('banana.name'));
		$this->assertEquals('two', $this->request->getOldInput('apple.name'));
	}

	public function testMissingOldInput()
	{
		$_SESSION['_ci_old_input'] = [
			'get'  => ['apple' => ['name' => 'two']],
			'post' => ['banana' => ['name' => 'foo']],
		];

		$this->assertNull($this->request->getOldInput('pineapple.name'));
	}

	// Reference: https://github.com/codeigniter4/CodeIgniter4/issues/1492
	public function testCanGetOldInputArray()
	{
		$_SESSION['_ci_old_input'] = [
			'get'  => ['apple' => ['name' => 'two']],
			'post' => ['banana' => ['name' => 'foo']],
		];

		$this->assertEquals(['name' => 'two'], $this->request->getOldInput('apple'));
		$this->assertEquals(['name' => 'foo'], $this->request->getOldInput('banana'));
	}

	// Reference: https://github.com/codeigniter4/CodeIgniter4/issues/1492
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testCanSerializeOldArray()
	{
		$locations = [
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'SK' => 'Saskatchewan',
		];
		$session   = service('session');
		$session->set(['_ci_old_input' => ['post' => ['location' => $locations]]]);

		$this->assertEquals($locations, $this->request->getOldInput('location'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabServerVars()
	{
		$server                   = $this->getPrivateProperty($this->request, 'globals');
		$server['server']['TEST'] = 5;
		$this->setPrivateProperty($this->request, 'globals', $server);

		$this->assertEquals(5, $this->request->getServer('TEST'));
		$this->assertNull($this->request->getServer('TESTY'));
	}

	public function testCanGrabEnvVars()
	{
		$server                = $this->getPrivateProperty($this->request, 'globals');
		$server['env']['TEST'] = 5;
		$this->setPrivateProperty($this->request, 'globals', $server);

		$this->assertEquals(5, $this->request->getEnv('TEST'));
		$this->assertNull($this->request->getEnv('TESTY'));
	}

	public function testCanGrabCookieVars()
	{
		$_COOKIE['TEST'] = 5;

		$this->assertEquals(5, $this->request->getCookie('TEST'));
		$this->assertNull($this->request->getCookie('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testStoresDefaultLocale()
	{
		$config = new App();

		$this->assertEquals($config->defaultLocale, $this->request->getDefaultLocale());
		$this->assertEquals($config->defaultLocale, $this->request->getLocale());
	}

	public function testSetLocaleSaves()
	{
		$config                   = new App();
		$config->supportedLocales = [
			'en',
			'es',
		];
		$config->defaultLocale    = 'es';
		$config->baseURL          = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), null, new UserAgent());

		$request->setLocale('en');
		$this->assertEquals('en', $request->getLocale());
	}

	public function testSetBadLocale()
	{
		$config                   = new App();
		$config->supportedLocales = [
			'en',
			'es',
		];
		$config->defaultLocale    = 'es';
		$config->baseURL          = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), null, new UserAgent());

		$request->setLocale('xx');
		$this->assertEquals('es', $request->getLocale());
	}

	//--------------------------------------------------------------------

	public function testNegotiatesLocale()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es; q=1.0, en; q=0.5';

		$config                   = new App();
		$config->negotiateLocale  = true;
		$config->supportedLocales = [
			'en',
			'es',
		];
		$config->baseURL          = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), null, new UserAgent());

		$this->assertEquals($config->defaultLocale, $request->getDefaultLocale());
		$this->assertEquals('es', $request->getLocale());
	}

	// The negotiation tests below are not intended to exercise the HTTP\Negotiate class -
	// that is up to the NegotiateTest. These are only to make sure that the requests
	// flow through to the negotiator

	public function testNegotiatesNot()
	{
		$this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

		$this->expectException(Exceptions\HTTPException::class);
		$this->request->negotiate('something bogus', ['iso-8859-5', 'unicode-1-1']);
	}

	public function testNegotiatesCharset()
	{
		//      $_SERVER['HTTP_ACCEPT_CHARSET'] = 'iso-8859-5, unicode-1-1;q=0.8';
		$this->request->setHeader('Accept-Charset', 'iso-8859-5, unicode-1-1;q=0.8');

		$this->assertEquals(strtolower($this->request->config->charset), $this->request->negotiate('charset', ['iso-8859', 'unicode-1-2']));
	}

	public function testNegotiatesMedia()
	{
		$this->request->setHeader('Accept', 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
		$this->assertEquals('text/html', $this->request->negotiate('media', ['text/html', 'text/x-c', 'text/x-dvi', 'text/plain']));
	}

	public function testNegotiatesEncoding()
	{
		$this->request->setHeader('Accept-Encoding', 'gzip;q=1.0, identity; q=0.4, compress;q=0.5');
		$this->assertEquals('gzip', $this->request->negotiate('encoding', ['gzip', 'compress']));
	}

	public function testNegotiatesLanguage()
	{
		$this->request->setHeader('Accept-Language', 'da, en-gb;q=0.8, en;q=0.7');
		$this->assertEquals('en', $this->request->negotiate('language', ['en', 'da']));
	}

	//--------------------------------------------------------------------

	public function testCanGrabGetRawJSON()
	{
		$json = '{"code":1, "message":"ok"}';

		$expected = [
			'code'    => 1,
			'message' => 'ok',
		];

		$config          = new App();
		$config->baseURL = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), $json, new UserAgent());

		$this->assertEquals($expected, $request->getJSON(true));
	}

	public function testCanGrabGetRawInput()
	{
		$rawstring = 'username=admin001&role=administrator&usepass=0';

		$expected = [
			'username' => 'admin001',
			'role'     => 'administrator',
			'usepass'  => 0,
		];

		$config          = new App();
		$config->baseURL = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), $rawstring, new UserAgent());

		$this->assertEquals($expected, $request->getRawInput());
	}

	//--------------------------------------------------------------------

	public function testIsCLI()
	{
		// this should be the case in unit testing
		$this->assertTrue($this->request->isCLI());
	}

	public function testIsAJAX()
	{
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		$this->assertTrue($this->request->isAJAX());
	}

	//--------------------------------------------------------------------

	public function testIsSecure()
	{
		$_SERVER['HTTPS'] = 'on';
		$this->assertTrue($this->request->isSecure());
	}

	public function testIsSecureFrontEnd()
	{
		$_SERVER['HTTP_FRONT_END_HTTPS'] = 'on';
		$this->assertTrue($this->request->isSecure());
	}

	public function testIsSecureForwarded()
	{
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
		$this->assertTrue($this->request->isSecure());
	}

	//--------------------------------------------------------------------

	public function testUserAgent()
	{
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla';
		$config                     = new App();
		$request                    = new IncomingRequest($config, new URI(), null, new UserAgent());
		$this->assertEquals('Mozilla', $request->getUserAgent());
	}

	//--------------------------------------------------------------------

	public function testFileCollectionFactory()
	{
		$_FILES = [
			'userfile' => [
				'name'     => 'someFile.txt',
				'type'     => 'text/plain',
				'size'     => '124',
				'tmp_name' => '/tmp/myTempFile.txt',
				'error'    => 0,
			],
		];

		$files = $this->request->getFiles();
		$this->assertCount(1, $files);

		$file = array_shift($files);
		$this->assertInstanceOf(UploadedFile::class, $file);

		$this->assertEquals('someFile.txt', $file->getName());
		$this->assertEquals(124, $file->getSize());
	}

	//--------------------------------------------------------------------

	public function testGetFile()
	{
		$_FILES = [
			'userfile' => [
				'name'     => 'someFile.txt',
				'type'     => 'text/plain',
				'size'     => '124',
				'tmp_name' => '/tmp/myTempFile.txt',
				'error'    => 0,
			],
		];

		$gotit = $this->request->getFile('userfile');
		$this->assertEquals(124, $gotit->getSize());
	}

	//--------------------------------------------------------------------

	public function testSpoofing()
	{
		$this->request->setMethod('WINK');
		$this->assertEquals('wink', $this->request->getMethod());
	}

}
