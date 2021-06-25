<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Factories;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockResponse;
use Config\App;
use Config\Services;
use DateTime;
use DateTimeZone;

/**
 * @internal
 */
final class ResponseTest extends CIUnitTestCase
{
    protected $server;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        Factories::reset('config');
    }

    public function testCanSetStatusCode()
    {
        $response = new Response(new App());

        $response->setStatusCode(200);

        $this->assertSame(200, $response->getStatusCode());
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

        $this->assertSame('OK', $response->getReasonPhrase());
    }

    //--------------------------------------------------------------------

    public function testCanSetCustomReasonCode()
    {
        $response = new Response(new App());

        $response->setStatusCode(200, 'Not the mama');

        $this->assertSame('Not the mama', $response->getReasonPhrase());
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

        $this->assertSame('Multiple Choices', $response->getReasonPhrase());
    }

    //--------------------------------------------------------------------

    public function testSetStatusCodeSavesCustomReason()
    {
        $response = new Response(new App());

        $response->setStatusCode(300, 'My Little Pony');

        $this->assertSame('My Little Pony', $response->getReasonPhrase());
    }

    //--------------------------------------------------------------------

    public function testGetReasonDefaultsToOK()
    {
        $response = new Response(new App());

        $this->assertSame('OK', $response->getReasonPhrase());
    }

    //--------------------------------------------------------------------

    public function testSetDateRemembersDateInUTC()
    {
        $response = new Response(new App());

        $response->setDate(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

        $date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
        $date->setTimezone(new DateTimeZone('UTC'));

        $header = $response->getHeaderLine('Date');

        $this->assertSame($date->format('D, d M Y H:i:s') . ' GMT', $header);
    }

    //--------------------------------------------------------------------

    public function testSetLink()
    {
        // Ensure our URL is not getting overridden
        $config          = new App();
        $config->baseURL = 'http://example.com/test/';
        Factories::injectMock('config', 'App', $config);

        $response = new Response($config);
        $pager    = Services::pager();

        $pager->store('default', 3, 10, 200);
        $response->setLink($pager);

        $this->assertSame(
            '<http://example.com/test/index.php?page=1>; rel="first",<http://example.com/test/index.php?page=2>; rel="prev",<http://example.com/test/index.php?page=4>; rel="next",<http://example.com/test/index.php?page=20>; rel="last"',
            $response->header('Link')->getValue()
        );

        $pager->store('default', 1, 10, 200);
        $response->setLink($pager);

        $this->assertSame(
            '<http://example.com/test/index.php?page=2>; rel="next",<http://example.com/test/index.php?page=20>; rel="last"',
            $response->header('Link')->getValue()
        );

        $pager->store('default', 20, 10, 200);
        $response->setLink($pager);

        $this->assertSame(
            '<http://example.com/test/index.php?page=1>; rel="first",<http://example.com/test/index.php?page=19>; rel="prev"',
            $response->header('Link')->getValue()
        );
    }

    //--------------------------------------------------------------------

    public function testSetContentType()
    {
        $response = new Response(new App());

        $response->setContentType('text/json');

        $this->assertSame('text/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    //--------------------------------------------------------------------

    public function testNoCache()
    {
        $response = new Response(new App());

        $response->noCache();

        $this->assertSame('no-store, max-age=0, no-cache', $response->getHeaderLine('Cache-control'));
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
            'must-revalidate',
        ];

        $response->setCache($options);

        $this->assertSame('12345678', $response->getHeaderLine('ETag'));
        $this->assertSame($date, $response->getHeaderLine('Last-Modified'));
        $this->assertSame('max-age=300, must-revalidate', $response->getHeaderLine('Cache-Control'));
    }

    public function testSetCacheNoOptions()
    {
        $response = new Response(new App());

        $options = [];

        $response->setCache($options);

        $this->assertSame('no-store, max-age=0, no-cache', $response->getHeaderLine('Cache-Control'));
    }

    //--------------------------------------------------------------------

    public function testSetLastModifiedWithDateTimeObject()
    {
        $response = new Response(new App());

        $response->setLastModified(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

        $date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
        $date->setTimezone(new DateTimeZone('UTC'));

        $header = $response->getHeaderLine('Last-Modified');

        $this->assertSame($date->format('D, d M Y H:i:s') . ' GMT', $header);
    }

    //--------------------------------------------------------------------

    public function testRedirectSetsDefaultCodeAndLocationHeader()
    {
        $response = new Response(new App());

        $response->redirect('example.com');

        $this->assertTrue($response->hasHeader('location'));
        $this->assertSame('example.com', $response->getHeaderLine('Location'));
        $this->assertSame(302, $response->getStatusCode());
    }

    //--------------------------------------------------------------------

    public function testRedirectSetsCode()
    {
        $response = new Response(new App());

        $response->redirect('example.com', 'auto', 307);

        $this->assertTrue($response->hasHeader('location'));
        $this->assertSame('example.com', $response->getHeaderLine('Location'));
        $this->assertSame(307, $response->getStatusCode());
    }

    //--------------------------------------------------------------------

    public function testRedirectWithIIS()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'Microsoft-IIS';
        $response                   = new Response(new App());
        $response->redirect('example.com', 'auto', 307);
        $this->assertSame('0;url=example.com', $response->getHeaderLine('Refresh'));
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
        $body = [
            'foo' => 'bar',
            'bar' => [
                1,
                2,
                3,
            ],
        ];
        $expected = Services::format()->getFormatter('application/json')->format($body);

        $response = new Response(new App());
        $response->setJSON($body);

        $this->assertSame($expected, $response->getJSON());
        $this->assertTrue(strpos($response->getHeaderLine('content-type'), 'application/json') !== false);
    }

    public function testJSONGetFromNormalBody()
    {
        $body = [
            'foo' => 'bar',
            'bar' => [
                1,
                2,
                3,
            ],
        ];
        $expected = Services::format()->getFormatter('application/json')->format($body);

        $response = new Response(new App());
        $response->setBody($body);

        $this->assertSame($expected, $response->getJSON());
    }

    //--------------------------------------------------------------------

    public function testXMLWithArray()
    {
        $body = [
            'foo' => 'bar',
            'bar' => [
                1,
                2,
                3,
            ],
        ];
        $expected = Services::format()->getFormatter('application/xml')->format($body);

        $response = new Response(new App());
        $response->setXML($body);

        $this->assertSame($expected, $response->getXML());
        $this->assertTrue(strpos($response->getHeaderLine('content-type'), 'application/xml') !== false);
    }

    public function testXMLGetFromNormalBody()
    {
        $body = [
            'foo' => 'bar',
            'bar' => [
                1,
                2,
                3,
            ],
        ];
        $expected = Services::format()->getFormatter('application/xml')->format($body);

        $response = new Response(new App());
        $response->setBody($body);

        $this->assertSame($expected, $response->getXML());
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
        $actualOutput = ob_get_contents();
        ob_end_clean();

        $this->assertSame('data', $actualOutput);
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
        $actualOutput = ob_get_contents();
        ob_end_clean();

        $this->assertSame(file_get_contents(__FILE__), $actualOutput);
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

        $this->assertSame(303, $response->getStatusCode());
    }

    public function testTemporaryRedirectGet11()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $response                   = new Response(new App());

        $response->setProtocolVersion('HTTP/1.1');
        $response->redirect('/foo');

        $this->assertSame(307, $response->getStatusCode());
    }

    //--------------------------------------------------------------------

    // Make sure cookies are set by RedirectResponse this way
    // See https://github.com/codeigniter4/CodeIgniter4/issues/1393
    public function testRedirectResponseCookies()
    {
        $loginTime = time();

        $response = new Response(new App());
        $answer1  = $response->redirect('/login')
            ->setCookie('foo', 'bar', YEAR)
            ->setCookie('login_time', $loginTime, YEAR);

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

        $this->assertSame('Happy days', $actual);
    }

    public function testInvalidSameSiteCookie()
    {
        $config                 = new App();
        $config->cookieSameSite = 'Invalid';

        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidSameSite', ['Invalid']));
        new Response($config);
    }
}
