<?php
namespace CodeIgniter\HTTP;

use Config\App;

/**
 * This should be the same as RequestTest,
 * except also testing the methods added by CLIRequest
 *
 * @backupGlobals enabled
 */
class CLIRequestTest extends \CodeIgniter\Test\CIUnitTestCase
{

	/**
	 * @var \CodeIgniter\HTTP\Request
	 */
	protected $request;

	protected function setUp(): void
	{
		parent::setUp();

		$this->request = new CLIRequest(new App());
		$_POST         = [];
		$_GET          = [];
	}

	//--------------------------------------------------------------------

	public function testParsingSegments()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
			'-foo',
			'bar',
		];
		$_SERVER['argc'] = 6;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$segments = [
			'users',
			'21',
			'profile',
		];
		$this->assertEquals($segments, $this->request->getSegments());
	}

	public function testParsingOptions()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
			'-foo',
			'bar',
		];
		$_SERVER['argc'] = 6;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$options = ['foo' => 'bar'];
		$this->assertEquals($options, $this->request->getOptions());
	}

	public function testParsingOptionDetails()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
			'-foo',
			'bar',
		];
		$_SERVER['argc'] = 6;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$this->assertEquals('bar', $this->request->getOption('foo'));
		$this->assertNull($this->request->getOption('notthere'));
	}

	public function testParsingOptionString()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
			'-foo',
			'bar',
			'-baz',
			'queue some stuff',
		];
		$_SERVER['argc'] = 8;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$expected = '-foo bar -baz "queue some stuff"';
		$this->assertEquals($expected, $this->request->getOptionString());
	}

	public function testParsingNoOptions()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
		];
		$_SERVER['argc'] = 4;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$expected = '';
		$this->assertEquals($expected, $this->request->getOptionString());
	}

	public function testParsingPath()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
			'-foo',
			'bar',
		];
		$_SERVER['argc'] = 6;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$expected = 'users/21/profile';
		$this->assertEquals($expected, $this->request->getPath());
	}

	public function testParsingMalformed()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'pro-file',
			'-foo',
			'bar',
			'-baz',
			'queue some stuff',
		];
		$_SERVER['argc'] = 8;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$expectedOptions = '-foo bar -baz "queue some stuff"';
		$expectedPath    = 'users/21';
		$this->assertEquals($expectedOptions, $this->request->getOptionString());
		$this->assertEquals($expectedPath, $this->request->getPath());
	}

	public function testParsingMalformed2()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
			'-foo',
			'oops-bar',
			'-baz',
			'queue some stuff',
		];
		$_SERVER['argc'] = 8;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$expectedOptions = '-foo oops-bar -baz "queue some stuff"';
		$expectedPath    = 'users/21/profile';
		$this->assertEquals($expectedOptions, $this->request->getOptionString());
		$this->assertEquals($expectedPath, $this->request->getPath());
	}

	public function testParsingMalformed3()
	{
		$_SERVER['argv'] = [
			'index.php',
			'users',
			'21',
			'profile',
			'-foo',
			'oops',
			'bar',
			'-baz',
			'queue some stuff',
		];
		$_SERVER['argc'] = 9;

		// reinstantiate it to force parsing
		$this->request = new CLIRequest(new App());

		$expectedOptions = '-foo oops -baz "queue some stuff"';
		$expectedPath    = 'users/21/profile';
		$this->assertEquals($expectedOptions, $this->request->getOptionString());
		$this->assertEquals($expectedPath, $this->request->getPath());
	}

	//--------------------------------------------------------------------

	public function testFetchGlobalsSingleValue()
	{
		$_POST['foo'] = 'bar';
		$_GET['bar']  = 'baz';

		$this->assertEquals('bar', $this->request->fetchGlobal('post', 'foo'));
		$this->assertEquals('baz', $this->request->fetchGlobal('get', 'bar'));
	}

	public function testFetchGlobalsReturnsNullWhenNotFound()
	{
		$this->assertNull($this->request->fetchGlobal('post', 'foo'));
	}

	public function testFetchGlobalsFiltersValues()
	{
		$this->request->setGlobal('post', [
			'foo' => 'bar<script>',
			'bar' => 'baz',
		]);

		$this->assertEquals('bar%3Cscript%3E', $this->request->fetchGlobal('post', 'foo', FILTER_SANITIZE_ENCODED));
		$this->assertEquals('baz', $this->request->fetchGlobal('post', 'bar'));
	}

	public function testFetchGlobalsWithFilterFlag()
	{
		$this->request->setGlobal('post', [
			'foo' => '`bar<script>',
			'bar' => 'baz',
		]);

		$this->assertEquals('bar%3Cscript%3E', $this->request->fetchGlobal('post', 'foo', FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
		$this->assertEquals('baz', $this->request->fetchGlobal('post', 'bar'));
	}

	public function testFetchGlobalReturnsAllWhenEmpty()
	{
		$post = [
			'foo' => 'bar',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz',
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals($post, $this->request->fetchGlobal('post'));
	}

	public function testFetchGlobalFiltersAllValues()
	{
		$post = [
			'foo' => 'bar<script>',
			'bar' => 'baz<script>',
			'xxx' => 'yyy<script>',
			'yyy' => 'zzz<script>',
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
			'xxx' => 'yyy%3Cscript%3E',
			'yyy' => 'zzz%3Cscript%3E',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', null, FILTER_SANITIZE_ENCODED));
	}

	public function testFetchGlobalFilterWithFlagAllValues()
	{
		$post = [
			'foo' => '`bar<script>',
			'bar' => '`baz<script>',
			'xxx' => '`yyy<script>',
			'yyy' => '`zzz<script>',
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
			'xxx' => 'yyy%3Cscript%3E',
			'yyy' => 'zzz%3Cscript%3E',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', null, FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
	}

	public function testFetchGlobalReturnsSelectedKeys()
	{
		$post = [
			'foo' => 'bar',
			'bar' => 'baz',
			'xxx' => 'yyy',
			'yyy' => 'zzz',
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar',
			'bar' => 'baz',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', ['foo', 'bar']));
	}

	public function testFetchGlobalFiltersSelectedValues()
	{
		$post = [
			'foo' => 'bar<script>',
			'bar' => 'baz<script>',
			'xxx' => 'yyy<script>',
			'yyy' => 'zzz<script>',
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', ['foo', 'bar'], FILTER_SANITIZE_ENCODED));
	}

	public function testFetchGlobalFilterWithFlagSelectedValues()
	{
		$post = [
			'foo' => '`bar<script>',
			'bar' => '`baz<script>',
			'xxx' => '`yyy<script>',
			'yyy' => '`zzz<script>',
		];
		$this->request->setGlobal('post', $post);
		$expected = [
			'foo' => 'bar%3Cscript%3E',
			'bar' => 'baz%3Cscript%3E',
		];

		$this->assertEquals($expected, $this->request->fetchGlobal('post', ['foo', 'bar'], FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/353
	 */
	public function testFetchGlobalReturnsArrayValues()
	{
		$post = [
			'ANNOUNCEMENTS' => [
				1 => [
					'DETAIL' => 'asdf',
				],
				2 => [
					'DETAIL' => 'sdfg',
				],
			],
			'submit'        => 'SAVE',
		];
		$this->request->setGlobal('post', $post);
		$result = $this->request->fetchGlobal('post');

		$this->assertEquals($post, $result);
		$this->assertIsArray($result['ANNOUNCEMENTS']);
		$this->assertCount(2, $result['ANNOUNCEMENTS']);
	}

	public function testFetchGlobalWithArrayTop()
	{
		$post = [
			'clients' => [
				'address' => [
					'zipcode' => 90210,
				],
			],
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(['address' => ['zipcode' => 90210]], $this->request->fetchGlobal('post', 'clients'));
	}

	public function testFetchGlobalWithArrayChildNumeric()
	{
		$post = [
			'clients' => [
				[
					'address' => [
						'zipcode' => 90210,
					],
				],
				[
					'address' => [
						'zipcode' => 60610,
					],
				],
			],
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(['zipcode' => 60610], $this->request->fetchGlobal('post', 'clients[1][address]'));
	}

	public function testFetchGlobalWithArrayChildElement()
	{
		$post = [
			'clients' => [
				'address' => [
					'zipcode' => 90210,
				],
			],
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(['zipcode' => 90210], $this->request->fetchGlobal('post', 'clients[address]'));
		$this->assertEquals(null, $this->request->fetchGlobal('post', 'clients[zipcode]'));
	}

	public function testFetchGlobalWithKeylessArrayChildElement()
	{
		$post = [
			'clients' => [
				'address' => [
					'zipcode' => 90210,
				],
				'stuff'   => [['a']],
			],
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals([['a']], $this->request->fetchGlobal('post', 'clients[stuff]'));
	}

	public function testFetchGlobalWithArrayLastElement()
	{
		$post = [
			'clients' => [
				'address' => [
					'zipcode' => 90210,
				],
			],
		];
		$this->request->setGlobal('post', $post);

		$this->assertEquals(90210, $this->request->fetchGlobal('post', 'clients[address][zipcode]'));
	}

	public function testFetchGlobalWithEmptyNotation()
	{
		$expected = [
			[
				'address' => [
					'zipcode' => 90210,
				],
			],
			[
				'address' => [
					'zipcode' => 60610,
				],
			],
		];
		$post     = [
			'clients' => $expected,
		];
		$this->request->setGlobal('post', $post);

		//      echo var_dump($this->request->fetchGlobal('post', 'clients[][zipcode]'));
		$this->assertEquals($expected, $this->request->fetchGlobal('post', 'clients[]'));
	}

	//--------------------------------------------------------------------

	public function ipAddressChecks()
	{
		return [
			'empty'         => [
				false,
				'',
			],
			'zero'          => [
				false,
				0,
			],
			'large_ipv4'    => [
				false,
				'256.256.256.999',
				'ipv4',
			],
			'good_ipv4'     => [
				true,
				'100.100.100.0',
				'ipv4',
			],
			'good_default'  => [
				true,
				'100.100.100.0',
			],
			'zeroed_ipv4'   => [
				true,
				'0.0.0.0',
			],
			'large_ipv6'    => [
				false,
				'h123:0000:0000:0000:0000:0000:0000:0000',
				'ipv6',
			],
			'good_ipv6'     => [
				true,
				'2001:0db8:85a3:0000:0000:8a2e:0370:7334',
			],
			'confused_ipv6' => [
				false,
				'255.255.255.255',
				'ipv6',
			],
		];
	}

	/**
	 * @dataProvider ipAddressChecks
	 */
	public function testValidIPAddress($expected, $address, $type = null)
	{
		$this->assertEquals($expected, $this->request->isValidIP($address, $type));
	}

	//--------------------------------------------------------------------

	public function testGetIPAddressDefault()
	{
		$this->assertEquals('0.0.0.0', $this->request->getIPAddress());
	}

	public function testGetIPAddressNormal()
	{
		$expected               = '123.123.123.123';
		$_SERVER['REMOTE_ADDR'] = $expected;
		$this->request          = new Request(new App());
		$this->assertEquals($expected, $this->request->getIPAddress());
		// call a second time to exercise the initial conditional block in getIPAddress()
		$this->assertEquals($expected, $this->request->getIPAddress());
	}

	public function testGetIPAddressThruProxy()
	{
		$expected                        = '123.123.123.123';
		$_SERVER['REMOTE_ADDR']          = '10.0.1.200';
		$config                          = new App();
		$config->proxyIPs                = '10.0.1.200,192.168.5.0/24';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
		$this->request                   = new Request($config);

		// we should see the original forwarded address
		$this->assertEquals($expected, $this->request->getIPAddress());
	}

	public function testGetIPAddressThruProxyInvalid()
	{
		$expected                        = '123.456.23.123';
		$_SERVER['REMOTE_ADDR']          = '10.0.1.200';
		$config                          = new App();
		$config->proxyIPs                = '10.0.1.200,192.168.5.0/24';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
		$this->request                   = new Request($config);

		// spoofed address invalid
		$this->assertEquals('10.0.1.200', $this->request->getIPAddress());
	}

	public function testGetIPAddressThruProxyNotWhitelisted()
	{
		$expected                        = '123.456.23.123';
		$_SERVER['REMOTE_ADDR']          = '10.10.1.200';
		$config                          = new App();
		$config->proxyIPs                = '10.0.1.200,192.168.5.0/24';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
		$this->request                   = new Request($config);

		// spoofed address invalid
		$this->assertEquals('10.10.1.200', $this->request->getIPAddress());
	}

	public function testGetIPAddressThruProxySubnet()
	{
		$expected                        = '123.123.123.123';
		$_SERVER['REMOTE_ADDR']          = '192.168.5.21';
		$config                          = new App();
		$config->proxyIPs                = ['192.168.5.0/24'];
		$_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
		$this->request                   = new Request($config);

		// we should see the original forwarded address
		$this->assertEquals($expected, $this->request->getIPAddress());
	}

	public function testGetIPAddressThruProxyOutofSubnet()
	{
		$expected                        = '123.123.123.123';
		$_SERVER['REMOTE_ADDR']          = '192.168.5.21';
		$config                          = new App();
		$config->proxyIPs                = ['192.168.5.0/28'];
		$_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;
		$this->request                   = new Request($config);

		// we should see the original forwarded address
		$this->assertEquals('192.168.5.21', $this->request->getIPAddress());
	}

	//FIXME getIPAddress should have more testing, to 100% code coverage

	//--------------------------------------------------------------------

	public function testMethodReturnsRightStuff()
	{
		// Defaults method to CLI now.
		$this->assertEquals('cli', $this->request->getMethod());
		$this->assertEquals('CLI', $this->request->getMethod(true));
	}

}
