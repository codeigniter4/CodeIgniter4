<?php
namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\App;
use Config\Format;
use DateTime;
use DateTimeZone;
use Tests\Support\HTTP\MockResponse;

class ResponseTest extends \CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->server = $_SERVER;
	}

	public function tearDown(): void
	{
		$_SERVER = $this->server;
	}

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

	public function testConstructWithCSPEnabled()
	{
		$config             = new App();
		$config->CSPEnabled = true;
		$response           = new Response($config);

		$this->assertTrue($response instanceof Response);
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
		$this->expectExceptionMessage(lang('HTTP.unknownStatusCode', [115]));
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

		$this->assertEquals($date->format('D, d M Y H:i:s') . ' GMT', $header);
	}

	//--------------------------------------------------------------------

	public function testSetLink()
	{
		$response = new Response(new App());
		$pager    = \Config\Services::pager();

		$pager->store('default', 3, 10, 200);
		$response->setLink($pager);

		$this->assertEquals(
				'<http://example.com?page=1>; rel="first",<http://example.com?page=2>; rel="prev",<http://example.com?page=4>; rel="next",<http://example.com?page=20>; rel="last"', $response->getHeader('Link')->getValue()
		);

		$pager->store('default', 1, 10, 200);
		$response->setLink($pager);

		$this->assertEquals(
				'<http://example.com?page=2>; rel="next",<http://example.com?page=20>; rel="last"', $response->getHeader('Link')->getValue()
		);

		$pager->store('default', 20, 10, 200);
		$response->setLink($pager);

		$this->assertEquals(
				'<http://example.com?page=1>; rel="first",<http://example.com?page=19>; rel="prev"', $response->getHeader('Link')->getValue()
		);
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
			'etag'          => '12345678',
			'last-modified' => $date,
			'max-age'       => 300,
			'must-revalidate'
		];

		$response->setCache($options);

		$this->assertEquals('12345678', $response->getHeaderLine('ETag'));
		$this->assertEquals($date, $response->getHeaderLine('Last-Modified'));
		$this->assertEquals('max-age=300, must-revalidate', $response->getHeaderLine('Cache-Control'));
	}

	public function testSetCacheNoOptions()
	{
		$response = new Response(new App());

		$date = date('r');

		$options = [];

		$response->setCache($options);

		$this->assertEquals('no-store, max-age=0, no-cache', $response->getHeaderLine('Cache-Control'));
	}

	//--------------------------------------------------------------------

	public function testSetLastModifiedWithDateTimeObject()
	{
		$response = new Response(new App());

		$response->setLastModified(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

		$date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
		$date->setTimezone(new DateTimeZone('UTC'));

		$header = $response->getHeaderLine('Last-Modified');

		$this->assertEquals($date->format('D, d M Y H:i:s') . ' GMT', $header);
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

	public function testRedirectWithIIS()
	{
		$_SERVER['SERVER_SOFTWARE'] = 'Microsoft-IIS';
		$response                   = new Response(new App());
		$response->redirect('example.com', 'auto', 307);
		$this->assertEquals('0;url=example.com', $response->getHeaderLine('Refresh'));
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

		$this->assertTrue($response->hasCookie('foo', null, 'ack'));
		$this->assertFalse($response->hasCookie('foo', null, 'nak'));
	}

	//--------------------------------------------------------------------

	public function testJSONWithArray()
	{
		$response  = new Response(new App());
		$config    = new Format();
		$formatter = $config->getFormatter('application/json');

		$body     = [
			'foo' => 'bar',
			'bar' => [
				1,
				2,
				3,
			],
		];
		$expected = $formatter->format($body);

		$response->setJSON($body);

		$this->assertEquals($expected, $response->getJSON());
		$this->assertTrue(strpos($response->getHeaderLine('content-type'), 'application/json') !== false);
	}

	public function testJSONGetFromNormalBody()
	{
		$response  = new Response(new App());
		$config    = new Format();
		$formatter = $config->getFormatter('application/json');

		$body     = [
			'foo' => 'bar',
			'bar' => [
				1,
				2,
				3,
			],
		];
		$expected = $formatter->format($body);

		$response->setBody($body);

		$this->assertEquals($expected, $response->getJSON());
	}

	//--------------------------------------------------------------------

	public function testXMLWithArray()
	{
		$response  = new Response(new App());
		$config    = new Format();
		$formatter = $config->getFormatter('application/xml');

		$body     = [
			'foo' => 'bar',
			'bar' => [
				1,
				2,
				3,
			],
		];
		$expected = $formatter->format($body);

		$response->setXML($body);

		$this->assertEquals($expected, $response->getXML());
		$this->assertTrue(strpos($response->getHeaderLine('content-type'), 'application/xml') !== false);
	}

	public function testXMLGetFromNormalBody()
	{
		$response  = new Response(new App());
		$config    = new Format();
		$formatter = $config->getFormatter('application/xml');

		$body     = [
			'foo' => 'bar',
			'bar' => [
				1,
				2,
				3,
			],
		];
		$expected = $formatter->format($body);

		$response->setBody($body);

		$this->assertEquals($expected, $response->getXML());
	}

	//--------------------------------------------------------------------

	public function testGetDownloadResponseByData()
	{
		$response = new Response(new App());

		$actual = $response->download('unit-test.txt', 'data');

		$this->assertInstanceOf(DownloadResponse::class, $actual);
		$actual->buildHeaders();
		$this->assertSame('attachment; filename="unit-test.txt"; filename*=UTF-8\'\'unit-test.txt', $actual->getHeaderLine('Content-Disposition'));

		ob_start();
		$actual->sendBody();
		$actual_output = ob_get_contents();
		ob_end_clean();

		$this->assertSame('data', $actual_output);
	}

	public function testGetDownloadResponseByFilePath()
	{
		$response = new Response(new App());

		$actual = $response->download(__FILE__, null);

		$this->assertInstanceOf(DownloadResponse::class, $actual);
		$actual->buildHeaders();
		$this->assertSame('attachment; filename="' . basename(__FILE__) . '"; filename*=UTF-8\'\'' . basename(__FILE__), $actual->getHeaderLine('Content-Disposition'));

		ob_start();
		$actual->sendBody();
		$actual_output = ob_get_contents();
		ob_end_clean();

		$this->assertSame(file_get_contents(__FILE__), $actual_output);
	}

	public function testVagueDownload()
	{
		$response = new Response(new App());

		$actual = $response->download();

		$this->assertNull($actual);
	}

	//--------------------------------------------------------------------

	public function testPretendMode()
	{
		$response = new MockResponse(new App());
		$response->pretend(true);
		$this->assertTrue($response->getPretend());
		$response->pretend(false);
		$this->assertFalse($response->getPretend());
	}

	public function testMisbehaving()
	{
		$response = new MockResponse(new App());
		$response->misbehave();

		$this->expectException(HTTPException::class);
		$response->getStatusCode();
	}

	//--------------------------------------------------------------------

	public function testTemporaryRedirect11()
	{
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$_SERVER['REQUEST_METHOD']  = 'POST';
		$response                   = new Response(new App());

		$response->setProtocolVersion('HTTP/1.1');
		$response->redirect('/foo');

		$this->assertEquals(303, $response->getStatusCode());
	}

	public function testTemporaryRedirectGet11()
	{
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$_SERVER['REQUEST_METHOD']  = 'GET';
		$response                   = new Response(new App());

		$response->setProtocolVersion('HTTP/1.1');
		$response->redirect('/foo');

		$this->assertEquals(307, $response->getStatusCode());
	}

	//--------------------------------------------------------------------
	// Make sure cookies are set by RedirectResponse this way
	// See https://github.com/codeigniter4/CodeIgniter4/issues/1393
	public function testRedirectResponseCookies()
	{
		$login_time = time();

		$response = new Response(new App());
		$answer1  = $response->redirect('/login')
				->setCookie('foo', 'bar', YEAR)
				->setCookie('login_time', $login_time, YEAR);

		$this->assertTrue($answer1->hasCookie('foo'));
		$this->assertTrue($answer1->hasCookie('login_time'));
	}

	//--------------------------------------------------------------------
	// Make sure we don't blow up if pretending to send headers
	public function testPretendOutput()
	{
		$response = new Response(new App());
		$response->pretend(true);

		$response->setBody('Happy days');

		ob_start();
		$response->send();
		$actual = ob_get_contents();
		ob_end_clean();

		$this->assertEquals('Happy days', $actual);
	}

}
