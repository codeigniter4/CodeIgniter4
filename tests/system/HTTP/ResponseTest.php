<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockResponse;
use Config\App;
use Config\Services;
use DateTime;
use DateTimeZone;

/**
 * @internal
 *
 * @group Others
 */
final class ResponseTest extends CIUnitTestCase
{
    private array $server;

    protected function setUp(): void
    {
        $this->server = $_SERVER;

        parent::setUp();

        $this->resetServices();
    }

    protected function tearDown(): void
    {
        Factories::reset('config');

        $_SERVER = $this->server;
    }

    public function testCanSetStatusCode(): void
    {
        $response = new Response(new App());

        $response->setStatusCode(200);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSetStatusCodeThrowsExceptionForBadCodes(): void
    {
        $response = new Response(new App());

        $this->expectException(HTTPException::class);
        $response->setStatusCode(54322);
    }

    public function testConstructWithCSPEnabled(): void
    {
        $config             = new App();
        $config->CSPEnabled = true;
        $response           = new Response($config);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSetStatusCodeSetsReason(): void
    {
        $response = new Response(new App());

        $response->setStatusCode(200);

        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testCanSetCustomReasonCode(): void
    {
        $response = new Response(new App());

        $response->setStatusCode(200, 'Not the mama');

        $this->assertSame('Not the mama', $response->getReasonPhrase());
    }

    public function testRequiresMessageWithUnknownStatusCode(): void
    {
        $response = new Response(new App());

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.unknownStatusCode', [115]));
        $response->setStatusCode(115);
    }

    public function testRequiresMessageWithSmallStatusCode(): void
    {
        $response = new Response(new App());

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidStatusCode', [95]));
        $response->setStatusCode(95);
    }

    public function testRequiresMessageWithLargeStatusCode(): void
    {
        $response = new Response(new App());

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidStatusCode', [695]));
        $response->setStatusCode(695);
    }

    public function testSetStatusCodeInterpretsReason(): void
    {
        $response = new Response(new App());

        $response->setStatusCode(300);

        $this->assertSame('Multiple Choices', $response->getReasonPhrase());
    }

    public function testSetStatusCodeSavesCustomReason(): void
    {
        $response = new Response(new App());

        $response->setStatusCode(300, 'My Little Pony');

        $this->assertSame('My Little Pony', $response->getReasonPhrase());
    }

    public function testGetReasonDefaultsToOK(): void
    {
        $response = new Response(new App());

        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testSetDateRemembersDateInUTC(): void
    {
        $response = new Response(new App());

        $datetime = DateTime::createFromFormat('!Y-m-d', '2000-03-10');
        $response->setDate($datetime);

        $date = clone $datetime;
        $date->setTimezone(new DateTimeZone('UTC'));

        $header = $response->getHeaderLine('Date');

        $this->assertSame($date->format('D, d M Y H:i:s') . ' GMT', $header);
    }

    public function testSetLink(): void
    {
        // Ensure our URL is not getting overridden
        $config          = new App();
        $config->baseURL = 'http://example.com/test/';
        Factories::injectMock('config', 'App', $config);

        $this->resetServices();

        $response = new Response($config);
        $pager    = Services::pager();

        $pager->store('default', 3, 10, 200);
        $response->setLink($pager);

        $this->assertSame(
            '<http://example.com/test/index.php/?page=1>; rel="first",<http://example.com/test/index.php/?page=2>; rel="prev",<http://example.com/test/index.php/?page=4>; rel="next",<http://example.com/test/index.php/?page=20>; rel="last"',
            $response->header('Link')->getValue()
        );

        $pager->store('default', 1, 10, 200);
        $response->setLink($pager);

        $this->assertSame(
            '<http://example.com/test/index.php/?page=2>; rel="next",<http://example.com/test/index.php/?page=20>; rel="last"',
            $response->header('Link')->getValue()
        );

        $pager->store('default', 20, 10, 200);
        $response->setLink($pager);

        $this->assertSame(
            '<http://example.com/test/index.php/?page=1>; rel="first",<http://example.com/test/index.php/?page=19>; rel="prev"',
            $response->header('Link')->getValue()
        );
    }

    public function testSetContentType(): void
    {
        $response = new Response(new App());

        $response->setContentType('text/json');

        $this->assertSame('text/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testNoCache(): void
    {
        $response = new Response(new App());

        $response->noCache();

        $this->assertSame('no-store, max-age=0, no-cache', $response->getHeaderLine('Cache-control'));
    }

    public function testSetCache(): void
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

    public function testSetCacheNoOptions(): void
    {
        $response = new Response(new App());

        $options = [];

        $response->setCache($options);

        $this->assertSame('no-store, max-age=0, no-cache', $response->getHeaderLine('Cache-Control'));
    }

    public function testSetLastModifiedWithDateTimeObject(): void
    {
        $response = new Response(new App());

        $datetime = DateTime::createFromFormat('Y-m-d', '2000-03-10');
        $response->setLastModified($datetime);

        $date = clone $datetime;
        $date->setTimezone(new DateTimeZone('UTC'));

        $header = $response->getHeaderLine('Last-Modified');

        $this->assertSame($date->format('D, d M Y H:i:s') . ' GMT', $header);
    }

    public function testRedirectSetsDefaultCodeAndLocationHeader(): void
    {
        $response = new Response(new App());

        $response->redirect('example.com');

        $this->assertTrue($response->hasHeader('location'));
        $this->assertSame('example.com', $response->getHeaderLine('Location'));
        $this->assertSame(302, $response->getStatusCode());
    }

    /**
     * @dataProvider provideRedirect
     */
    public function testRedirect(
        string $server,
        string $protocol,
        string $method,
        ?int $code,
        int $expectedCode
    ): void {
        $_SERVER['SERVER_SOFTWARE'] = $server;
        $_SERVER['SERVER_PROTOCOL'] = $protocol;
        $_SERVER['REQUEST_METHOD']  = $method;

        $response = new Response(new App());
        $response->redirect('example.com', 'auto', $code);

        $this->assertTrue($response->hasHeader('location'));
        $this->assertSame('example.com', $response->getHeaderLine('Location'));
        $this->assertSame($expectedCode, $response->getStatusCode());
    }

    public static function provideRedirect(): iterable
    {
        yield from [
            ['Apache/2.4.17', 'HTTP/1.1', 'GET', null, 302],
            ['Apache/2.4.17', 'HTTP/1.1', 'GET', 307, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'GET', 302, 302],
            ['Apache/2.4.17', 'HTTP/1.1', 'POST', null, 303],
            ['Apache/2.4.17', 'HTTP/1.1', 'POST', 307, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'POST', 302, 302],
            ['Apache/2.4.17', 'HTTP/1.1', 'HEAD', null, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'HEAD', 307, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'HEAD', 302, 302],
            ['Apache/2.4.17', 'HTTP/1.1', 'OPTIONS', null, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'OPTIONS', 307, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'OPTIONS', 302, 302],
            ['Apache/2.4.17', 'HTTP/1.1', 'PUT', null, 303],
            ['Apache/2.4.17', 'HTTP/1.1', 'PUT', 307, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'PUT', 302, 302],
            ['Apache/2.4.17', 'HTTP/1.1', 'DELETE', null, 303],
            ['Apache/2.4.17', 'HTTP/1.1', 'DELETE', 307, 307],
            ['Apache/2.4.17', 'HTTP/1.1', 'DELETE', 302, 302],
        ];
    }

    /**
     * @dataProvider provideRedirectWithIIS
     */
    public function testRedirectWithIIS(
        string $protocol,
        string $method,
        ?int $code,
        int $expectedCode
    ): void {
        $_SERVER['SERVER_SOFTWARE'] = 'Microsoft-IIS';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';

        $response = new Response(new App());
        $response->redirect('example.com', 'auto', $code);

        $this->assertSame('0;url=example.com', $response->getHeaderLine('Refresh'));
        $this->assertSame($expectedCode, $response->getStatusCode());

        unset($_SERVER['SERVER_SOFTWARE']);
    }

    public static function provideRedirectWithIIS(): iterable
    {
        yield from [
            ['HTTP/1.1', 'GET', null, 302],
            ['HTTP/1.1', 'GET', 307, 307],
            ['HTTP/1.1', 'GET', 302, 302],
            ['HTTP/1.1', 'POST', null, 302],
            ['HTTP/1.1', 'POST', 307, 307],
            ['HTTP/1.1', 'POST', 302, 302],
        ];
    }

    public function testSetCookieFails(): void
    {
        $response = new Response(new App());

        $this->assertFalse($response->hasCookie('foo'));
    }

    public function testSetCookieMatch(): void
    {
        $response = new Response(new App());
        $response->setCookie('foo', 'bar');

        $this->assertTrue($response->hasCookie('foo'));
        $this->assertTrue($response->hasCookie('foo', 'bar'));
    }

    public function testSetCookieFailDifferentPrefix(): void
    {
        $response = new Response(new App());
        $response->setCookie('foo', 'bar', '', '', '', 'ack');

        $this->assertFalse($response->hasCookie('foo'));
    }

    public function testSetCookieSuccessOnPrefix(): void
    {
        $response = new Response(new App());
        $response->setCookie('foo', 'bar', '', '', '', 'ack');

        $this->assertTrue($response->hasCookie('foo', null, 'ack'));
        $this->assertFalse($response->hasCookie('foo', null, 'nak'));
    }

    public function testJSONWithArray(): void
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
        $this->assertStringContainsString('application/json', $response->getHeaderLine('content-type'));
    }

    public function testJSONGetFromNormalBody(): void
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

    public function testXMLWithArray(): void
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
        $this->assertStringContainsString('application/xml', $response->getHeaderLine('content-type'));
    }

    public function testXMLGetFromNormalBody(): void
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

    public function testGetDownloadResponseByData(): void
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

    public function testGetDownloadResponseByFilePath(): void
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

    public function testVagueDownload(): void
    {
        $response = new Response(new App());

        $actual = $response->download();

        $this->assertNull($actual);
    }

    public function testPretendMode(): void
    {
        $response = new MockResponse(new App());
        $response->pretend(true);
        $this->assertTrue($response->getPretend());
        $response->pretend(false);
        $this->assertFalse($response->getPretend());
    }

    public function testMisbehaving(): void
    {
        $response = new MockResponse(new App());
        $response->misbehave();

        $this->expectException(HTTPException::class);
        $response->getStatusCode();
    }

    public function testTemporaryRedirectHTTP11(): void
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'POST';
        $response                   = new Response(new App());

        $response->setProtocolVersion('HTTP/1.1');
        $response->redirect('/foo');

        $this->assertSame(303, $response->getStatusCode());
    }

    public function testTemporaryRedirectGetHTTP11(): void
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $response                   = new Response(new App());

        $response->setProtocolVersion('HTTP/1.1');
        $response->redirect('/foo');

        $this->assertSame(302, $response->getStatusCode());
    }

    // Make sure cookies are set by RedirectResponse this way
    // See https://github.com/codeigniter4/CodeIgniter4/issues/1393
    public function testRedirectResponseCookies(): void
    {
        $loginTime = time();

        $response = new Response(new App());
        $answer1  = $response->redirect('/login')
            ->setCookie('foo', 'bar', YEAR)
            ->setCookie('login_time', $loginTime, YEAR);

        $this->assertTrue($answer1->hasCookie('foo'));
        $this->assertTrue($answer1->hasCookie('login_time'));
    }

    // Make sure we don't blow up if pretending to send headers
    public function testPretendOutput(): void
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
}
