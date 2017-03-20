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
		$this->request = new IncomingRequest(new App(), new URI());

		$_POST = $_GET = $_SERVER = $_REQUEST = $_ENV = $_COOKIE = $_SESSION = [];
	}

	//--------------------------------------------------------------------

	public function testCanGrabRequestVars()
	{
		$_REQUEST['TEST'] = 5;

		$this->assertEquals(5, $this->request->getVar('TEST'));
		$this->assertEquals(null, $this->request->getVar('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabGetVars()
	{
		$_GET['TEST'] = 5;

		$this->assertEquals(5, $this->request->getGet('TEST'));
		$this->assertEquals(null, $this->request->getGEt('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabPostVars()
	{
		$_POST['TEST'] = 5;

		$this->assertEquals(5, $this->request->getPost('TEST'));
		$this->assertEquals(null, $this->request->getPost('TESTY'));
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
		$_SERVER['TEST'] = 5;

		$this->assertEquals(5, $this->request->getServer('TEST'));
		$this->assertEquals(null, $this->request->getServer('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabEnvVars()
	{
		$_ENV['TEST'] = 5;

		$this->assertEquals(5, $this->request->getEnv('TEST'));
		$this->assertEquals(null, $this->request->getEnv('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testCanGrabCookieVars()
	{
		$_COOKIE['TEST'] = 5;

		$this->assertEquals(5, $this->request->getCookie('TEST'));
		$this->assertEquals(null, $this->request->getCookie('TESTY'));
	}

	//--------------------------------------------------------------------

	public function testFetchGlobalReturnsSingleValue()
	{
		$_POST = [
			'foo' => 'bar',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz'
		];

		$this->assertEquals('baz', $this->request->getPost('bar'));
	}

	//--------------------------------------------------------------------

    /**
     * @see https://github.com/bcit-ci/CodeIgniter4/issues/353
     */
    public function testGetPostReturnsArrayValues()
    {
        $_POST = [
            'ANNOUNCEMENTS' => [
                1 => [
                    'DETAIL' => 'asdf'
                ],
                2 => [
                    'DETAIL' => 'sdfg'
                ]
            ],
            'submit' => 'SAVE'
        ];

        $result = $this->request->getPost();

        $this->assertEquals($_POST, $result);
        $this->assertTrue(is_array($result['ANNOUNCEMENTS']));
        $this->assertEquals(2, count($result['ANNOUNCEMENTS']));
    }

    //--------------------------------------------------------------------

	public function testFetchGlobalFiltersValue()
	{
		$_POST = [
			'foo' => 'bar<script>',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz'
		];

		$this->assertEquals('bar%3Cscript%3E', $this->request->getPost('foo', FILTER_SANITIZE_ENCODED));
	}

	//--------------------------------------------------------------------

	public function testFetchGlobalReturnsAllWhenEmpty()
	{
		$post = [
			'foo' => 'bar',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz'
		];
		$_POST = $post;

		$this->assertEquals($post, $this->request->getPost());
	}

	//--------------------------------------------------------------------

	public function testFetchGlobalFiltersAllValues()
	{
		$_POST = [
			'foo' => 'bar<script>',
			'bar' => 'baz<script>',
			'xxx' => 'yyy<script>',
			'yyy' => 'zzz<script>'
		];
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
			'xxx' => 'yyy%3Cscript%3E',
			'yyy' => 'zzz%3Cscript%3E'
		];

		$this->assertEquals($expected, $this->request->getPost(null, FILTER_SANITIZE_ENCODED));
	}

	//--------------------------------------------------------------------

	public function testFetchGlobalReturnsSelectedKeys()
	{
		$_POST = [
			'foo' => 'bar',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz'
		];
		$expected = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$this->assertEquals($expected, $this->request->getPost(['foo', 'bar']));
	}

	//--------------------------------------------------------------------

	public function testFetchGlobalFiltersSelectedValues()
	{
		$_POST = [
			'foo' => 'bar<script>',
			'bar' => 'baz<script>',
			'xxx' => 'yyy<script>',
			'yyy' => 'zzz<script>'
		];
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
		];

		$this->assertEquals($expected, $this->request->getPost(['foo', 'bar'], FILTER_SANITIZE_ENCODED));
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
		$this->request->setLocale('en');

		$this->assertEquals('en', $this->request->getLocale());
	}

	//--------------------------------------------------------------------

	public function testNegotiatesLocale()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es; q=1.0, en; q=0.5';

		$config = new App();
		$config->negotiateLocale = true;
		$config->supportedLocales = ['en', 'es'];

		$request = new IncomingRequest($config, new URI());

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

		$request = new IncomingRequest(new App(), new URI(), $json);
		
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
		$request = new IncomingRequest(new App(), new URI(), $rawstring);

		$this->assertEquals($expected, $request->getRawInput());
	}

	//--------------------------------------------------------------------
}
