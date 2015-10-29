<?php

use CodeIgniter\HTTP\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
	public function testCanSetStatusCode()
	{
	    $response = new Response();

		$response->setStatusCode(200);

		$this->assertEquals(200, $response->statusCode());
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

		$this->assertEquals('OK', $response->reason());
	}

	//--------------------------------------------------------------------

	public function testCanSetCustomReasonCode()
	{
		$response = new Response();

		$response->setStatusCode(200, 'Not the mama');

		$this->assertEquals('Not the mama', $response->reason());
	}

	//--------------------------------------------------------------------
}
