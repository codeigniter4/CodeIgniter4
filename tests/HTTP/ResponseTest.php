<?php

use CodeIgniter\HTTP\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
	public function testCanSetStatusCode()
	{
	    $response = new Response();

		$response->setStatusCode(200);

		$this->assertEquals(200, $response->getStatusCode());
	}

	//--------------------------------------------------------------------

	public function testSetStatusCodeThrowsExceptionForBadCodes()
	{
		$response = new Response();

		$this->setExpectedException('InvalidArgumentException');
		$response->setStatusCode(54322);
	}

	//--------------------------------------------------------------------


	public function testSetStatusCodeSetsReason()
	{
		$response = new Response();

		$response->setStatusCode(200);

		$this->assertEquals('OK', $response->getReason());
	}

	//--------------------------------------------------------------------

	public function testCanSetCustomReasonCode()
	{
		$response = new Response();

		$response->setStatusCode(200, 'Not the mama');

		$this->assertEquals('Not the mama', $response->getReason());
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithUnknownStatusCode()
	{
		$response = new Response();

		$this->setExpectedException('InvalidArgumentException', 'Unknown HTTP status code provided with no message');
		$response->setStatusCode(115);
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithSmallStatusCode()
	{
		$response = new Response();

		$this->setExpectedException('InvalidArgumentException', '95 is not a valid HTTP return status code');
		$response->setStatusCode(95);
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithLargeStatusCode()
	{
		$response = new Response();

		$this->setExpectedException('InvalidArgumentException', '695 is not a valid HTTP return status code');
		$response->setStatusCode(695);
	}

	//--------------------------------------------------------------------

	public function testExceptionThrownWhenNoStatusCode()
	{
		$response = new Response();

		$this->setExpectedException('BadMethodCallException', 'HTTP Response is missing a status code');
		$response->getStatusCode();
	}

	//--------------------------------------------------------------------

}
