<?php namespace CodeIgniter\HTTP;

use Config\App;

/**
 * @backupGlobals enabled
 */
class IncomingRequestTest extends \CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;

	public function setUp()
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

	//--------------------------------------------------------------------

	public function testCanGrabGetVars()
	{
		$_GET['TEST'] = 5;

		$this->assertEquals(5, $this->request->getGet('TEST'));
		$this->assertNull($this->request->getGEt('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabPostVars()
	{
		$_POST['TEST'] = 5;

		$this->assertEquals(5, $this->request->getPost('TEST'));
		$this->assertNull($this->request->getPost('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabPostBeforeGet()
	{
		$_POST['TEST'] = 5;
		$_GET['TEST'] = 3;

		$this->assertEquals(5, $this->request->getPostGet('TEST'));
		$this->assertEquals(3, $this->request->getGetPost('TEST'));
	}

	//--------------------------------------------------------------------

    /**
     * @group single
     */
    public function testCanGetOldInput()
    {
        $_SESSION['_ci_old_input'] = [
            'get' => ['one' => 'two'],
            'post' => ['name' => 'foo']
        ];

        $this->assertEquals('foo', $this->request->getOldInput('name'));
        $this->assertEquals('two', $this->request->getOldInput('one'));
    }


	public function testCanGrabServerVars()
	{
		$server = $this->getPrivateProperty($this->request, 'globals');
		$server['server']['TEST'] = 5;
		$this->setPrivateProperty($this->request, 'globals', $server);

		$this->assertEquals(5, $this->request->getServer('TEST'));
		$this->assertNull($this->request->getServer('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabEnvVars()
	{
		$server = $this->getPrivateProperty($this->request, 'globals');
		$server['env']['TEST'] = 5;
		$this->setPrivateProperty($this->request, 'globals', $server);

		$this->assertEquals(5, $this->request->getEnv('TEST'));
		$this->assertNull($this->request->getEnv('TESTY'));
	}

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

	public function testSetLocaleSaves()
	{
		$config = new App();
		$config->supportedLocales = ['en', 'es'];
		$config->defaultLocale = 'es';
		$config->baseURL = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), null, new UserAgent());

		$request->setLocale('en');
		$this->assertEquals('en', $request->getLocale());
	}

	//--------------------------------------------------------------------

	public function testNegotiatesLocale()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es; q=1.0, en; q=0.5';

		$config = new App();
		$config->negotiateLocale = true;
		$config->supportedLocales = ['en', 'es'];
		$config->baseURL = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), null, new UserAgent());

		$this->assertEquals($config->defaultLocale, $request->getDefaultLocale());
		$this->assertEquals('es', $request->getLocale());
	}

	//--------------------------------------------------------------------

	public function testCanGrabGetRawJSON()
	{
		$json = '{"code":1, "message":"ok"}';

		$expected = [
			'code' => 1,
			'message' => 'ok'
		];

		$config = new App();
		$config->baseURL = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), $json, new UserAgent());

		$this->assertEquals($expected, $request->getJSON(true));
	}

	//--------------------------------------------------------------------

	public function testCanGrabGetRawInput()
	{
		$rawstring = 'username=admin001&role=administrator&usepass=0';

		$expected = [
			'username' => 'admin001',
			'role' => 'administrator',
			'usepass' => 0
		];

		$config = new App();
		$config->baseURL = 'http://example.com';

		$request = new IncomingRequest($config, new URI(), $rawstring, new UserAgent());

		$this->assertEquals($expected, $request->getRawInput());
	}

	//--------------------------------------------------------------------
}
