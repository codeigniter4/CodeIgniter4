<?php namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use DateTime;
use DateTimeZone;
use BadMethodCallException;
use InvalidArgumentException;

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

	public function testCantSet200OtherThanStatusCode()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->expectException(HTTPException::class);
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

	public function testNoCache()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$response->noCache();

		$this->assertSame('private, no-transform, no-store, must-revalidate', $response->getHeaderLine('Cache-control'));
	}

	public function testCantSetCache()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->expectException(BadMethodCallException::class);
		$response->setCache();
	}

	public function testWhenFilepathIsSetBinaryCanNotBeSet()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->expectException(BadMethodCallException::class);
		$response->setFilePath(__FILE__);
		$response->setBinary('test');
	}

	public function testWhenBinaryIsSetFilepathCanNotBeSet()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->expectException(BadMethodCallException::class);
		$response->setBinary('test');
		$response->setFilePath(__FILE__);
	}

	public function testCanNotSetNoFilepath()
	{
		$response = new DownloadResponse('unit-test.txt', true);

		$this->expectException(FileNotFoundException::class);
		$response->setFilePath('unit test');
	}
}
