<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Services;
use Config\App;
use Tests\Support\HTTP\MockCURLRequest;

class CURLRequestTest extends \CIUnitTestCase
{
	/**
	 * @var MockCURLRequest
	 */
	protected $request;

	protected function setUp(): void
	{
		parent::setUp();

		Services::reset();
		$this->request = $this->getRequest();
	}

	protected function getRequest(array $options = [])
	{
		$uri = isset($options['base_uri']) ? new URI($options['base_uri']) : new URI();

		return new MockCURLRequest(($app = new App()), $uri, new Response($app), $options);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1029
	 */
	public function testGetRemembersBaseURI()
	{
		$request = $this->getRequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
		]);

		$request->get('products');

		$options = $request->curl_options;

		$this->assertEquals('http://www.foo.com/api/v1/products', $options[CURLOPT_URL]);
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1029
	 */
	public function testGetRemembersBaseURIWithHelperMethod()
	{
		$request = Services::curlrequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
		]);

		$uri = $this->getPrivateProperty($request, 'baseURI');
		$this->assertEquals('www.foo.com', $uri->getHost());
		$this->assertEquals('/api/v1/', $uri->getPath());
	}

	//--------------------------------------------------------------------

	public function testSendReturnsResponse()
	{
		$output = 'Howdy Stranger.';

		$response = $this->request->setOutput($output)
				->send('get', 'http://example.com');

		$this->assertInstanceOf('CodeIgniter\\HTTP\\Response', $response);
		$this->assertEquals($output, $response->getBody());
	}

	//--------------------------------------------------------------------

	public function testGetSetsCorrectMethod()
	{
		$this->request->get('http://example.com');

		$this->assertEquals('get', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('GET', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testDeleteSetsCorrectMethod()
	{
		$this->request->delete('http://example.com');

		$this->assertEquals('delete', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('DELETE', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testHeadSetsCorrectMethod()
	{
		$this->request->head('http://example.com');

		$this->assertEquals('head', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('HEAD', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testOptionsSetsCorrectMethod()
	{
		$this->request->options('http://example.com');

		$this->assertEquals('options', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('OPTIONS', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testOptionsBaseURIOption()
	{
		$options = [
			'base_uri' => 'http://www.foo.com/api/v1/',
		];
		$request = $this->getRequest($options);

		$this->assertEquals('http://www.foo.com/api/v1/', $request->getBaseURI());
	}

	public function testOptionsBaseURIOverride()
	{
		$options = [
			'base_uri' => 'http://www.foo.com/api/v1/',
			'baseURI'  => 'http://bogus/com',
		];
		$request = $this->getRequest($options);

		$this->assertEquals('http://bogus/com', $request->getBaseURI());
	}

	//--------------------------------------------------------------------

	public function testOptionsHeaders()
	{
		$options = [
			'base_uri' => 'http://www.foo.com/api/v1/',
			'headers'  => ['fruit' => 'apple'],
		];
		$request = $this->getRequest([]);
		$this->assertNull($request->getHeader('fruit'));

		$request = $this->getRequest($options);
		$this->assertEquals('apple', $request->getHeader('fruit')->getValue());
	}

	//--------------------------------------------------------------------

	/**
	 * @backupGlobals enabled
	 */
	public function testOptionHeadersUsingPopulate()
	{
		$_SERVER['HTTP_HOST']            = 'site1.com';
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';

		$options = [
			'base_uri' => 'http://www.foo.com/api/v1/',
		];

		$request = $this->getRequest($options);
		$request->get('example');
		// we fill the Accept-Language header from _SERVER when no headers are defined for the request
		$this->assertEquals('en-US', $request->getHeader('Accept-Language')->getValue());
		// but we skip Host header - since it would corrupt the request
		$this->assertNull($request->getHeader('Host'));
	}

	/**
	 * @backupGlobals enabled
	 */
	public function testOptionHeadersNotUsingPopulate()
	{
		$_SERVER['HTTP_HOST']            = 'site1.com';
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';

		$options = [
			'base_uri' => 'http://www.foo.com/api/v1/',
			'headers'  => ['Host' => 'www.foo.com'],
		];
		$request = $this->getRequest($options);
		$request->get('example');
		// if headers for the request are defined we use them
		$this->assertNull($request->getHeader('Accept-Language'));
		$this->assertEquals('www.foo.com', $request->getHeader('Host')->getValue());
	}

	//--------------------------------------------------------------------

	public function testOptionsDelay()
	{
		$options = [
			'delay'   => 2000,
			'headers' => ['fruit' => 'apple'],
		];
		$request = $this->getRequest([]);
		$this->assertEquals(0.0, $request->getDelay());

		$request = $this->getRequest($options);
		$this->assertEquals(2.0, $request->getDelay());
	}

	//--------------------------------------------------------------------

	public function testPatchSetsCorrectMethod()
	{
		$this->request->patch('http://example.com');

		$this->assertEquals('patch', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('PATCH', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPostSetsCorrectMethod()
	{
		$this->request->post('http://example.com');

		$this->assertEquals('post', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('POST', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPutSetsCorrectMethod()
	{
		$this->request->put('http://example.com');

		$this->assertEquals('put', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('PUT', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testCustomMethodSetsCorrectMethod()
	{
		$this->request->request('custom', 'http://example.com');

		$this->assertEquals('custom', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('CUSTOM', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testRequestMethodGetsSanitized()
	{
		$this->request->request('<script>Custom</script>', 'http://example.com');

		$this->assertEquals('custom', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('CUSTOM', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testRequestSetsBasicCurlOptions()
	{
		$this->request->request('get', 'http://example.com');

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_URL, $options);
		$this->assertEquals('http://example.com', $options[CURLOPT_URL]);

		$this->assertArrayHasKey(CURLOPT_RETURNTRANSFER, $options);
		$this->assertTrue($options[CURLOPT_RETURNTRANSFER]);

		$this->assertArrayHasKey(CURLOPT_HEADER, $options);
		$this->assertTrue($options[CURLOPT_HEADER]);

		$this->assertArrayHasKey(CURLOPT_FRESH_CONNECT, $options);
		$this->assertTrue($options[CURLOPT_FRESH_CONNECT]);

		$this->assertArrayHasKey(CURLOPT_TIMEOUT_MS, $options);
		$this->assertEquals(0.0, $options[CURLOPT_TIMEOUT_MS]);

		$this->assertArrayHasKey(CURLOPT_CONNECTTIMEOUT_MS, $options);
		$this->assertEquals(150 * 1000, $options[CURLOPT_CONNECTTIMEOUT_MS]);
	}

	//--------------------------------------------------------------------

	public function testAuthBasicOption()
	{
		$this->request->request('get', 'http://example.com', [
			'auth' => [
				'username',
				'password',
			],
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_USERPWD, $options);
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
		$this->assertEquals(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
	}

	//--------------------------------------------------------------------

	public function testAuthBasicOptionExplicit()
	{
		$this->request->request('get', 'http://example.com', [
			'auth' => [
				'username',
				'password',
				'basic',
			],
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_USERPWD, $options);
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
		$this->assertEquals(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
	}

	//--------------------------------------------------------------------

	public function testAuthDigestOption()
	{
		$this->request->request('get', 'http://example.com', [
			'auth' => [
				'username',
				'password',
				'digest',
			],
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_USERPWD, $options);
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
		$this->assertEquals(CURLAUTH_DIGEST, $options[CURLOPT_HTTPAUTH]);
	}

	//--------------------------------------------------------------------

	public function testSetAuthBasic()
	{
		$this->request->setAuth('username', 'password')->get('http://example.com');

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_USERPWD, $options);
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
		$this->assertEquals(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
	}

	public function testSetAuthDigest()
	{
		$this->request->setAuth('username', 'password', 'digest')->get('http://example.com');

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_USERPWD, $options);
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertArrayHasKey(CURLOPT_HTTPAUTH, $options);
		$this->assertEquals(CURLAUTH_DIGEST, $options[CURLOPT_HTTPAUTH]);
	}

	//--------------------------------------------------------------------

	public function testCertOption()
	{
		$file = __FILE__;

		$this->request->request('get', 'http://example.com', [
			'cert' => $file,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_SSLCERT, $options);
		$this->assertEquals($file, $options[CURLOPT_SSLCERT]);
	}

	public function testCertOptionWithPassword()
	{
		$file = __FILE__;

		$this->request->request('get', 'http://example.com', [
			'cert' => [
				$file,
				'password',
			],
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_SSLCERT, $options);
		$this->assertEquals($file, $options[CURLOPT_SSLCERT]);

		$this->assertArrayHasKey(CURLOPT_SSLCERTPASSWD, $options);
		$this->assertEquals('password', $options[CURLOPT_SSLCERTPASSWD]);
	}

	public function testMissingCertOption()
	{
		$file = 'something_obviously_bogus';
		$this->expectException(Exceptions\HTTPException::class);

		$this->request->request('get', 'http://example.com', [
			'cert' => $file,
		]);
	}

	//--------------------------------------------------------------------

	public function testSSLVerification()
	{
		$file = __FILE__;

		$this->request->request('get', 'http://example.com', [
			'verify'  => 'yes',
			'ssl_key' => $file,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CAINFO, $options);
		$this->assertEquals($file, $options[CURLOPT_CAINFO]);

		$this->assertArrayHasKey(CURLOPT_SSL_VERIFYPEER, $options);
		$this->assertEquals(1, $options[CURLOPT_SSL_VERIFYPEER]);
	}

	public function testSSLWithBadKey()
	{
		$file = 'something_obviously_bogus';
		$this->expectException(Exceptions\HTTPException::class);

		$this->request->request('get', 'http://example.com', [
			'verify'  => 'yes',
			'ssl_key' => $file,
		]);
	}

	//--------------------------------------------------------------------

	public function testDebugOptionTrue()
	{
		$this->request->request('get', 'http://example.com', [
			'debug' => true,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_VERBOSE, $options);
		$this->assertEquals(1, $options[CURLOPT_VERBOSE]);

		$this->assertArrayHasKey(CURLOPT_STDERR, $options);
		$this->assertTrue(is_resource($options[CURLOPT_STDERR]));
	}

	public function testDebugOptionFalse()
	{
		$this->request->request('get', 'http://example.com', [
			'debug' => false,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayNotHasKey(CURLOPT_VERBOSE, $options);
		$this->assertArrayNotHasKey(CURLOPT_STDERR, $options);
	}

	public function testDebugOptionFile()
	{
		$file = SUPPORTPATH . 'Files/baker/banana.php';

		$this->request->request('get', 'http://example.com', [
			'debug' => $file,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_VERBOSE, $options);
		$this->assertEquals(1, $options[CURLOPT_VERBOSE]);

		$this->assertArrayHasKey(CURLOPT_STDERR, $options);
		$this->assertTrue(is_resource($options[CURLOPT_STDERR]));
	}

	//--------------------------------------------------------------------

	public function testDecodeContent()
	{
		$this->request->setHeader('Accept-Encoding', 'cobol');
		$this->request->request('get', 'http://example.com', [
			'decode_content' => true,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_ENCODING, $options);
		$this->assertEquals('cobol', $options[CURLOPT_ENCODING]);
	}

	public function testDecodeContentWithoutAccept()
	{
		//      $this->request->setHeader('Accept-Encoding', 'cobol');
		$this->request->request('get', 'http://example.com', [
			'decode_content' => true,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_ENCODING, $options);
		$this->assertEquals('', $options[CURLOPT_ENCODING]);
		$this->assertArrayHasKey(CURLOPT_HTTPHEADER, $options);
		$this->assertEquals('Accept-Encoding', $options[CURLOPT_HTTPHEADER]);
	}

	//--------------------------------------------------------------------

	public function testAllowRedirectsOptionFalse()
	{
		$this->request->request('get', 'http://example.com', [
			'allow_redirects' => false,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
		$this->assertEquals(0, $options[CURLOPT_FOLLOWLOCATION]);

		$this->assertArrayNotHasKey(CURLOPT_MAXREDIRS, $options);
		$this->assertArrayNotHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
	}

	public function testAllowRedirectsOptionTrue()
	{
		$this->request->request('get', 'http://example.com', [
			'allow_redirects' => true,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
		$this->assertEquals(1, $options[CURLOPT_FOLLOWLOCATION]);

		$this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
		$this->assertEquals(5, $options[CURLOPT_MAXREDIRS]);
		$this->assertArrayHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
		$this->assertEquals(CURLPROTO_HTTP | CURLPROTO_HTTPS, $options[CURLOPT_REDIR_PROTOCOLS]);
	}

	public function testAllowRedirectsOptionDefaults()
	{
		$this->request->request('get', 'http://example.com', [
			'allow_redirects' => true,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
		$this->assertEquals(1, $options[CURLOPT_FOLLOWLOCATION]);

		$this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
		$this->assertArrayHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
	}

	public function testAllowRedirectsArray()
	{
		$this->request->request('get', 'http://example.com', [
			'allow_redirects' => ['max' => 2],
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
		$this->assertEquals(1, $options[CURLOPT_FOLLOWLOCATION]);

		$this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
		$this->assertEquals(2, $options[CURLOPT_MAXREDIRS]);
	}

	//--------------------------------------------------------------------

	public function testSendWithQuery()
	{
		$request = $this->getRequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
			'query'    => ['name' => 'Henry'],
		]);

		$request->get('products');

		$options = $request->curl_options;

		$this->assertEquals('http://www.foo.com/api/v1/products?name=Henry', $options[CURLOPT_URL]);
	}

	//--------------------------------------------------------------------
	public function testSendWithDelay()
	{
		$request = $this->getRequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
			'delay'    => 1000,
		]);

		$request->get('products');

		// we still need to check the code coverage to make sure this was done
		$this->assertEquals(1.0, $request->getDelay());
	}

	//--------------------------------------------------------------------
	public function testSendContinued()
	{
		$request = $this->getRequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
			'delay'    => 1000,
		]);

		$request->setOutput("HTTP/1.1 100 Continue\x0d\x0a\x0d\x0aHi there");
		$response = $request->get('answer');
		$this->assertEquals('Hi there', $response->getBody());
	}

	//--------------------------------------------------------------------
	public function testSplitResponse()
	{
		$request = $this->getRequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
			'delay'    => 1000,
		]);

		$request->setOutput("Accept: text/html\x0d\x0a\x0d\x0aHi there");
		$response = $request->get('answer');
		$this->assertEquals('Hi there', $response->getBody());
	}

	//--------------------------------------------------------------------
	public function testApplyBody()
	{
		$request = $this->getRequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
			'delay'    => 1000,
		]);

		$request->setBody('name=George');
		$request->setOutput('Hi there');
		$response = $request->post('answer');

		$this->assertEquals('Hi there', $response->getBody());
		$this->assertEquals('name=George', $request->curl_options[CURLOPT_POSTFIELDS]);
	}

	//--------------------------------------------------------------------
	public function testResponseHeaders()
	{
		$request = $this->getRequest([
			'base_uri' => 'http://www.foo.com/api/v1/',
			'delay'    => 1000,
		]);

		$request->setOutput("HTTP/2.0 234 Ohoh\x0d\x0aAccept: text/html\x0d\x0a\x0d\x0aHi there");
		$response = $request->get('bogus');

		$this->assertEquals('2.0', $response->getProtocolVersion());
		$this->assertEquals(234, $response->getStatusCode());
	}

	//--------------------------------------------------------------------

	public function testPostFormEncoded()
	{
		$params = [
			'foo' => 'bar',
			'baz' => [
				'hi',
				'there',
			],
		];
		$this->request->request('POST', '/post', [
			'form_params' => $params,
		]);

		$this->assertEquals('post', $this->request->getMethod());

		$options = $this->request->curl_options;

		$expected = http_build_query($params);
		$this->assertArrayHasKey(CURLOPT_POSTFIELDS, $options);
		$this->assertEquals($expected, $options[CURLOPT_POSTFIELDS]);
	}

	public function testPostFormMultipart()
	{
		$params = [
			'foo'   => 'bar',
			'baz'   => [
				'hi',
				'there',
			],
			'afile' => new \CURLFile(__FILE__),
		];
		$this->request->request('POST', '/post', [
			'multipart' => $params,
		]);

		$this->assertEquals('post', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_POSTFIELDS, $options);
		$this->assertEquals($params, $options[CURLOPT_POSTFIELDS]);
	}

	//--------------------------------------------------------------------

	public function testSetForm()
	{
		$params = [
			'foo' => 'bar',
			'baz' => [
				'hi',
				'there',
			],
		];

		$this->request->setForm($params)->post('/post');

		$this->assertEquals(
			http_build_query($params),
			$this->request->curl_options[CURLOPT_POSTFIELDS]
		);

		$params['afile'] = new \CURLFile(__FILE__);

		$this->request->setForm($params, true)->post('/post');

		$this->assertEquals(
			$params,
			$this->request->curl_options[CURLOPT_POSTFIELDS]
		);
	}

	//--------------------------------------------------------------------

	public function testJSONData()
	{
		$params = [
			'foo' => 'bar',
			'baz' => [
				'hi',
				'there',
			],
		];
		$this->request->request('POST', '/post', [
			'json' => $params,
		]);

		$this->assertEquals('post', $this->request->getMethod());

		$expected = json_encode($params);
		$this->assertEquals($expected, $this->request->getBody());
	}

	//--------------------------------------------------------------------

	public function testSetJSON()
	{
		$params = [
			'foo' => 'bar',
			'baz' => [
				'hi',
				'there',
			],
		];
		$this->request->setJSON($params)->post('/post');

		$this->assertEquals(json_encode($params), $this->request->getBody());
		$this->assertEquals('application/json', $this->request->getHeaderLine('Content-Type'));
	}

	//--------------------------------------------------------------------

	public function testHTTPv1()
	{
		$this->request->request('POST', '/post', [
			'version' => 1.0,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
		$this->assertEquals(CURL_HTTP_VERSION_1_0, $options[CURLOPT_HTTP_VERSION]);
	}

	public function testHTTPv11()
	{
		$this->request->request('POST', '/post', [
			'version' => 1.1,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_HTTP_VERSION, $options);
		$this->assertEquals(CURL_HTTP_VERSION_1_1, $options[CURLOPT_HTTP_VERSION]);
	}

	//--------------------------------------------------------------------

	public function testCookieOption()
	{
		$holder = SUPPORTPATH . 'HTTP/Files/CookiesHolder.txt';
		$this->request->request('POST', '/post', [
			'cookie' => $holder,
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_COOKIEJAR, $options);
		$this->assertEquals($holder, $options[CURLOPT_COOKIEJAR]);
		$this->assertArrayHasKey(CURLOPT_COOKIEFILE, $options);
		$this->assertEquals($holder, $options[CURLOPT_COOKIEFILE]);
	}

}
