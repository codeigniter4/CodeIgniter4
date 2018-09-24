<?php namespace CodeIgniter\HTTP;

use InvalidArgumentException;
use DateTime;
use DateTimeZone;

class DownloadResponseTest extends \CIUnitTestCase
{
	public function testCanGetStatusCode()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->assertSame(200, $response->getStatusCode());
	}

	public function testCanSetCustomReasonCode()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$response->setStatusCode(200, 'Not the mama');

		$this->assertSame('Not the mama', $response->getReason());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCantSet200OtherThanStatusCode()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$response->setStatusCode(999);
	}

	public function testSetDateRemembersDateInUTC()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$response->setDate(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

		$date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
		$date->setTimezone(new DateTimeZone('UTC'));

		$header = $response->getHeaderLine('Date');

		$this->assertEquals($date->format('D, d M Y H:i:s').' GMT', $header);
	}

	public function testSetLastModifiedWithDateTimeObject()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$response->setLastModified(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

		$date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
		$date->setTimezone(new DateTimeZone('UTC'));

		$header = $response->getHeaderLine('Last-Modified');

		$this->assertEquals($date->format('D, d M Y H:i:s').' GMT', $header);
	}

	public function testsentMethodSouldReturnRedirectResponse()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->assertInstanceOf(DownloadResponse::class, $response);
	}

	public function testSetContentType()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$response->setContentType('text/json');

		$this->assertEquals('text/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
	}

	public function testSetContentTypeNoCharSet()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$response->setContentType('application/octet-stream', '');

		$this->assertEquals('application/octet-stream', $response->getHeaderLine('Content-Type'));
	}
}
