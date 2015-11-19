<?php

use App\Config\AppConfig;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\Response;

/**
 * Class MockCURLRequest
 *
 * Simply allows us to not actually call cURL during the
 * test runs. Instead, we can set the desired output
 * and get back the set options.
 */
class MockCURLRequest extends \CodeIgniter\HTTP\CURLRequest {

	public $curl_options;

	protected $output = '';

	//--------------------------------------------------------------------

	public function setOutput($output)
	{
		$this->output = $output;

		return $this;
	}

	//--------------------------------------------------------------------

	protected function sendRequest(array $curl_options = []): string
	{
		// Save so we can access later.
		$this->curl_options = $curl_options;

		return $this->output;
	}

	//--------------------------------------------------------------------

}

//--------------------------------------------------------------------


class CURLRequestTest extends PHPUnit_Framework_TestCase
{
	protected $request;

	public function setUp()
	{
	    $this->request = new MockCURLRequest(new AppConfig(), new URI(), new Response());
	}

	//--------------------------------------------------------------------

	public function testSendReturnsResponse()
	{
		$output = "Howdy Stranger.";

	    $response = $this->request->setOutput($output)
		                 ->send('get', 'http://example.com');

		$this->assertInstanceOf('CodeIgniter\\HTTP\\Response', $response);
		$this->assertEquals($output, $response->body());
	}

	//--------------------------------------------------------------------

	public function testGetSetsCorrectMethod()
	{
	    $response = $this->request->get('http://example.com');

		$this->assertEquals('get', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('GET', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testDeleteSetsCorrectMethod()
	{
		$response = $this->request->delete('http://example.com');

		$this->assertEquals('delete', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('DELETE', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testHeadSetsCorrectMethod()
	{
		$response = $this->request->head('http://example.com');

		$this->assertEquals('head', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('HEAD', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testOptionsSetsCorrectMethod()
	{
		$response = $this->request->options('http://example.com');

		$this->assertEquals('options', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('OPTIONS', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPatchSetsCorrectMethod()
	{
		$response = $this->request->patch('http://example.com');

		$this->assertEquals('patch', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('PATCH', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPostSetsCorrectMethod()
	{
		$response = $this->request->post('http://example.com');

		$this->assertEquals('post', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('POST', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testPutSetsCorrectMethod()
	{
		$response = $this->request->put('http://example.com');

		$this->assertEquals('put', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('PUT', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testCustomMethodSetsCorrectMethod()
	{
		$response = $this->request->request('custom', 'http://example.com');

		$this->assertEquals('custom', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('CUSTOM', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testRequestMethodGetsSanitized()
	{
		$response = $this->request->request('<script>Custom</script>', 'http://example.com');

		$this->assertEquals('custom', $this->request->method());

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_CUSTOMREQUEST]));
		$this->assertEquals('CUSTOM', $options[CURLOPT_CUSTOMREQUEST]);
	}

	//--------------------------------------------------------------------

	public function testRequestSetsBasicCurlOptions()
	{
		$response = $this->request->request('get', 'http://example.com');

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_URL]));
		$this->assertEquals('http://example.com', $options[CURLOPT_URL]);

		$this->assertTrue(isset($options[CURLOPT_RETURNTRANSFER]));
		$this->assertEquals(true, $options[CURLOPT_RETURNTRANSFER]);

		$this->assertTrue(isset($options[CURLOPT_HEADER]));
		$this->assertEquals(true, $options[CURLOPT_HEADER]);

		$this->assertTrue(isset($options[CURLOPT_FRESH_CONNECT]));
		$this->assertEquals(true, $options[CURLOPT_FRESH_CONNECT]);

		$this->assertTrue(isset($options[CURLOPT_TIMEOUT_MS]));
		$this->assertEquals(0.0, $options[CURLOPT_TIMEOUT_MS]);

		$this->assertTrue(isset($options[CURLOPT_CONNECTTIMEOUT_MS]));
		$this->assertEquals(150 * 1000, $options[CURLOPT_CONNECTTIMEOUT_MS]);
	}

	//--------------------------------------------------------------------

	public function testAuthBasicOption()
	{
		$response = $this->request->request('get', 'http://example.com', [
			'auth' => ['username', 'password']
		]);

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_USERPWD]));
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertTrue(isset($options[CURLOPT_HTTPAUTH]));
		$this->assertEquals(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
	}

	//--------------------------------------------------------------------

	public function testAuthBasicOptionExplicit()
	{
		$response = $this->request->request('get', 'http://example.com', [
			'auth' => ['username', 'password', 'basic']
		]);

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_USERPWD]));
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertTrue(isset($options[CURLOPT_HTTPAUTH]));
		$this->assertEquals(CURLAUTH_BASIC, $options[CURLOPT_HTTPAUTH]);
	}

	//--------------------------------------------------------------------

	public function testAuthDigestOption()
	{
		$response = $this->request->request('get', 'http://example.com', [
			'auth' => ['username', 'password', 'digest']
		]);

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_USERPWD]));
		$this->assertEquals('username:password', $options[CURLOPT_USERPWD]);

		$this->assertTrue(isset($options[CURLOPT_HTTPAUTH]));
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

		$this->assertTrue(isset($options[CURLOPT_SSLCERT]));
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

		$this->assertTrue(isset($options[CURLOPT_SSLCERT]));
		$this->assertEquals($file, $options[CURLOPT_SSLCERT]);

		$this->assertTrue(isset($options[CURLOPT_SSLCERTPASSWD]));
		$this->assertEquals('password', $options[CURLOPT_SSLCERTPASSWD]);
	}

	//--------------------------------------------------------------------

	public function testDebugOption()
	{
		$response = $this->request->request('get', 'http://example.com', [
			'debug' => true
		]);

		$options = $this->request->curl_options;

		$this->assertTrue(isset($options[CURLOPT_VERBOSE]));
		$this->assertEquals(1, $options[CURLOPT_VERBOSE]);

		$this->assertTrue(isset($options[CURLOPT_STDERR]));
	}

	//--------------------------------------------------------------------

}