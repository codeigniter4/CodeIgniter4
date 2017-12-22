<?php namespace CodeIgniter\HTTP;

use Config\App;

class CURLRequestTest extends \CIUnitTestCase
{
	protected $request;

	public function setUp()
	{
	    $this->request = new MockCURLRequest(new App(), new URI(), new Response(new \Config\App()));
	}

	//--------------------------------------------------------------------

	public function testSendReturnsResponse()
	{
		$output = "Howdy Stranger.";

		$response = $this->request->setOutput($output)
						->send('get', 'http://example.com');

		$this->assertInstanceOf('CodeIgniter\\HTTP\\Response', $response);
		$this->assertEquals($output, $response->getBody());
	}

	//--------------------------------------------------------------------

	public function testGetSetsCorrectMethod()
	{
		$response = $this->request->get('http://example.com');

		$this->assertEquals('get', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('GET', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testDeleteSetsCorrectMethod()
	{
		$response = $this->request->delete('http://example.com');

		$this->assertEquals('delete', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('DELETE', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testHeadSetsCorrectMethod()
	{
		$response = $this->request->head('http://example.com');

		$this->assertEquals('head', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('HEAD', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testOptionsSetsCorrectMethod()
	{
		$response = $this->request->options('http://example.com');

		$this->assertEquals('options', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('OPTIONS', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPatchSetsCorrectMethod()
	{
		$response = $this->request->patch('http://example.com');

		$this->assertEquals('patch', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('PATCH', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPostSetsCorrectMethod()
	{
		$response = $this->request->post('http://example.com');

		$this->assertEquals('post', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('POST', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPutSetsCorrectMethod()
	{
		$response = $this->request->put('http://example.com');

		$this->assertEquals('put', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('PUT', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testCustomMethodSetsCorrectMethod()
	{
		$response = $this->request->request('custom', 'http://example.com');

		$this->assertEquals('custom', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('CUSTOM', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testRequestMethodGetsSanitized()
	{
		$response = $this->request->request('<script>Custom</script>', 'http://example.com');

		$this->assertEquals('custom', $this->request->getMethod());

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
		$this->assertEquals('CUSTOM', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testRequestSetsBasicCurlOptions()
	{
		$response = $this->request->request('get', 'http://example.com');

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
		$response = $this->request->request('get', 'http://example.com', [
			'auth' => ['username', 'password']
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
		$response = $this->request->request('get', 'http://example.com', [
			'auth' => ['username', 'password', 'basic']
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
		$response = $this->request->request('get', 'http://example.com', [
			'auth' => ['username', 'password', 'digest']
		]);

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

		$response = $this->request->request('get', 'http://example.com', [
			'cert' => $file
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_SSLCERT, $options);
		$this->assertEquals($file, $options[CURLOPT_SSLCERT]);
	}

	//--------------------------------------------------------------------

	public function testCertOptionWithPassword()
	{
		$file = __FILE__;

		$response = $this->request->request('get', 'http://example.com', [
			'cert' => [$file, 'password']
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_SSLCERT, $options);
		$this->assertEquals($file, $options[CURLOPT_SSLCERT]);

		$this->assertArrayHasKey(CURLOPT_SSLCERTPASSWD, $options);
		$this->assertEquals('password', $options[CURLOPT_SSLCERTPASSWD]);
	}

	//--------------------------------------------------------------------

	public function testDebugOption()
	{
		$response = $this->request->request('get', 'http://example.com', [
			'debug' => true
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_VERBOSE, $options);
		$this->assertEquals(1, $options[CURLOPT_VERBOSE]);

		$this->assertArrayHasKey(CURLOPT_STDERR, $options);
	}

	//--------------------------------------------------------------------

	public function testAllowRedirectsOptionFalse()
	{
		$response = $this->request->request('get', 'http://example.com', [
				'allow_redirects' => false
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
		$this->assertEquals(0, $options[CURLOPT_FOLLOWLOCATION]);

		$this->assertArrayNotHasKey(CURLOPT_MAXREDIRS, $options);
		$this->assertArrayNotHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
	}

	//--------------------------------------------------------------------

	public function testAllowRedirectsOptionTrue()
	{
		$response = $this->request->request('get', 'http://example.com', [
				'allow_redirects' => true
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
		$this->assertEquals(1, $options[CURLOPT_FOLLOWLOCATION]);

		$this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
		$this->assertEquals(5, $options[CURLOPT_MAXREDIRS]);
		$this->assertArrayHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
		$this->assertEquals(CURLPROTO_HTTP|CURLPROTO_HTTPS, $options[CURLOPT_REDIR_PROTOCOLS]);
	}

	//--------------------------------------------------------------------

	public function testAllowRedirectsOptionDefaults()
	{
		$response = $this->request->request('get', 'http://example.com', [
				'allow_redirects' => true
		]);

		$options = $this->request->curl_options;

		$this->assertArrayHasKey(CURLOPT_FOLLOWLOCATION, $options);
		$this->assertEquals(1, $options[CURLOPT_FOLLOWLOCATION]);

		$this->assertArrayHasKey(CURLOPT_MAXREDIRS, $options);
		$this->assertArrayHasKey(CURLOPT_REDIR_PROTOCOLS, $options);
	}

	//--------------------------------------------------------------------
}
