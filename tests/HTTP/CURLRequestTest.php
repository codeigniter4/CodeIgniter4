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


}