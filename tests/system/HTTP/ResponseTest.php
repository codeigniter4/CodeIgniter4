<?php namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\App;
use Config\Format;
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

		$this->expectException(HTTPException::class);
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

		$this->expectException(HTTPException::class);
		$this->expectExceptionMessage('Unknown HTTP status code provided with no message');
		$response->setStatusCode(115);
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithSmallStatusCode()
	{
		$response = new Response(new App());

		$this->expectException(HTTPException::class);
		$this->expectExceptionMessage(lang('HTTP.invalidStatusCode', [95]));
		$response->setStatusCode(95);
	}

	//--------------------------------------------------------------------

	public function testRequiresMessageWithLargeStatusCode()
	{
		$response = new Response(new App());

		$this->expectException(HTTPException::class);
		$this->expectExceptionMessage(lang('HTTP.invalidStatusCode', [695]));
		$response->setStatusCode(695);
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

	public function testGetReasonDefaultsToOK()
	{
		$response = new Response(new App());

		$this->assertEquals('OK', $response->getReason());
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

		$response->redirect('example.com');

		$this->assertTrue($response->hasHeader('location'));
		$this->assertEquals('example.com', $response->getHeaderLine('Location'));
		$this->assertEquals(302, $response->getStatusCode());
	}

	//--------------------------------------------------------------------

	public function testRedirectSetsCode()
	{
		$response = new Response(new App());

		$response->redirect('example.com', 'auto', 307);

		$this->assertTrue($response->hasHeader('location'));
		$this->assertEquals('example.com', $response->getHeaderLine('Location'));
		$this->assertEquals(307, $response->getStatusCode());
	}

	//--------------------------------------------------------------------

	public function testSetCookieFails()
	{
		$response = new Response(new App());

		$this->assertFalse($response->hasCookie('foo'));
	}

	public function testSetCookieMatch()
	{
		$response = new Response(new App());
		$response->setCookie('foo', 'bar');

		$this->assertTrue($response->hasCookie('foo'));
		$this->assertTrue($response->hasCookie('foo', 'bar'));
	}

	public function testSetCookieFailDifferentPrefix()
	{
		$response = new Response(new App());
		$response->setCookie('foo', 'bar', '', '', '', 'ack');

		$this->assertFalse($response->hasCookie('foo'));
	}

	public function testSetCookieSuccessOnPrefix()
	{
		$response = new Response(new App());
		$response->setCookie('foo', 'bar', '', '', '', 'ack');

		$this->assertFalse($response->hasCookie('foo', null, 'ack'));
	}

	public function testJSONWithArray()
	{
		$response = new Response(new App());
		$config = new Format();
		$formatter = $config->getFormatter('application/json');

		$body = [
			'foo' => 'bar',
			'bar' => [1, 2, 3]
		];
		$expected = $formatter->format($body);

		$response->setJSON($body);

		$this->assertEquals($expected, $response->getJSON());
		$this->assertTrue(strpos($response->getHeaderLine('content-type'), 'application/json') !== false);
	}

	public function testJSONGetFromNormalBody()
	{
		$response = new Response(new App());
		$config = new Format();
		$formatter = $config->getFormatter('application/json');

		$body = [
			'foo' => 'bar',
			'bar' => [1, 2, 3]
		];
		$expected = $formatter->format($body);

		$response->setBody($body);

		$this->assertEquals($expected, $response->getJSON());
	}

	public function testXMLWithArray()
	{
		$response = new Response(new App());
		$config = new Format();
		$formatter = $config->getFormatter('application/xml');

		$body = [
			'foo' => 'bar',
			'bar' => [1, 2, 3]
		];
		$expected = $formatter->format($body);

		$response->setXML($body);

		$this->assertEquals($expected, $response->getXML());
		$this->assertTrue(strpos($response->getHeaderLine('content-type'), 'application/xml') !== false);
	}

	public function testXMLGetFromNormalBody()
	{
		$response = new Response(new App());
		$config = new Format();
		$formatter = $config->getFormatter('application/xml');

		$body = [
			'foo' => 'bar',
			'bar' => [1, 2, 3]
		];
		$expected = $formatter->format($body);

		$response->setBody($body);

		$this->assertEquals($expected, $response->getXML());
	}
}
