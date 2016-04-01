<?php namespace CodeIgniter\HTTP;

use Config\App;
use DateTime;
use DateTimeZone;

class ResponseTest extends \CIUnitTestCase
{
	public function testCanSetStatusCode()
	{
		$response = new Response(new App());

		$response->setStatusCode(200);

		$this->assertEquals(200, $response->getStatusCode());
	}

	//--------------------------------------------------------------------

	public function testSetStatusCodeThrowsExceptionForBadCodes()
	{
		$response = new Response(new App());

		$this->setExpectedException('InvalidArgumentException');
		$response->setStatusCode(54322);
	}

	//--------------------------------------------------------------------


	public function testSetStatusCodeSetsReason()
	{
		$response = new Response(new App());

		$response->setStatusCode(200);

		$this->assertEquals('OK', $response->getReason());
	}

	//--------------------------------------------------------------------

	public function testCanSetCustomReasonCode()
	{
		$response = new Response(new App());

		$response->setStatusCode(200, 'Not the mama');

		$this->assertEquals('Not the mama', $response->getReason());
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithUnknownStatusCode()
	{
		$response = new Response(new App());

		$this->setExpectedException('InvalidArgumentException', 'Unknown HTTP status code provided with no message');
		$response->setStatusCode(115);
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithSmallStatusCode()
	{
		$response = new Response(new App());

		$this->setExpectedException('InvalidArgumentException', '95 is not a valid HTTP return status code');
		$response->setStatusCode(95);
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithLargeStatusCode()
	{
		$response = new Response(new App());

		$this->setExpectedException('InvalidArgumentException', '695 is not a valid HTTP return status code');
		$response->setStatusCode(695);
	}

	//--------------------------------------------------------------------

	public function testExceptionThrownWhenNoStatusCode()
	{
		$response = new Response(new App());

		$this->setExpectedException('BadMethodCallException', 'HTTP Response is missing a status code');
		$response->getStatusCode();
	}

	//--------------------------------------------------------------------

	public function testSetStatusCodeInterpretsReason()
	{
		$response = new Response(new App());

		$response->setStatusCode(300);

		$this->assertEquals('Multiple Choices', $response->getReason());
	}

	//--------------------------------------------------------------------

	public function testSetStatusCodeSavesCustomReason()
	{
		$response = new Response(new App());

		$response->setStatusCode(300, 'My Little Pony');

		$this->assertEquals('My Little Pony', $response->getReason());
	}

	//--------------------------------------------------------------------

	public function testGetReasonReturnsEmptyStringWithNoStatus()
	{
		$response = new Response(new App());

		$this->assertEquals('', $response->getReason());
	}

	//--------------------------------------------------------------------

	public function testSetDateRemembersDateInUTC()
	{
		$response = new Response(new App());

		$response->setDate(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

		$date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
		$date->setTimezone(new DateTimeZone('UTC'));

		$header = $response->getHeaderLine('Date');

		$this->assertEquals($date->format('D, d M Y H:i:s').' GMT', $header);
	}

	//--------------------------------------------------------------------

	public function testSetContentType()
	{
		$response = new Response(new App());

		$response->setContentType('text/json');

		$this->assertEquals('text/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
	}

	//--------------------------------------------------------------------

	public function testNoCache()
	{
		$response = new Response(new App());

		$response->noCache();

		$this->assertEquals('no-store, max-age=0, no-cache', $response->getHeaderLine('Cache-control'));
	}

	//--------------------------------------------------------------------

	public function testSetCache()
	{
		$response = new Response(new App());

		$date = date('r');

		$options = [
			'etag' => '12345678',
			'last-modified' => $date,
			'max-age' => 300,
			'must-revalidate'
		];

		$response->setCache($options);

		$this->assertEquals('12345678', $response->getHeaderLine('ETag'));
		$this->assertEquals($date, $response->getHeaderLine('Last-Modified'));
		$this->assertEquals('max-age=300, must-revalidate', $response->getHeaderLine('Cache-Control'));
	}

	//--------------------------------------------------------------------

	public function testSetLastModifiedWithDateTimeObject()
	{
		$response = new Response(new App());

		$response->setLastModified(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

		$date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
		$date->setTimezone(new DateTimeZone('UTC'));

		$header = $response->getHeaderLine('Last-Modified');

		$this->assertEquals($date->format('D, d M Y H:i:s').' GMT', $header);
	}

	//--------------------------------------------------------------------

	public function testRedirectSetsDefaultCodeAndLocationHeader()
	{
		$response = new Response(new App());

		try
		{
			$response->redirect('example.com');
			$this->fail('RedirectException should be raised.');
		}
		catch (RedirectException $e) {}

		$this->assertTrue($response->hasHeader('location'));
		$this->assertEquals('example.com', $response->getHeaderLine('Location'));
		$this->assertEquals(302, $response->getStatusCode());
	}

	//--------------------------------------------------------------------

	public function testRedirectSetsCode()
	{
		$response = new Response(new App());

		try
		{
			$response->redirect('example.com', 'auto', 307);
			$this->fail('RedirectException should be raised.');
		}
		catch (RedirectException $e) {}

		$this->assertTrue($response->hasHeader('location'));
		$this->assertEquals('example.com', $response->getHeaderLine('Location'));
		$this->assertEquals(307, $response->getStatusCode());
	}

	//--------------------------------------------------------------------

}
