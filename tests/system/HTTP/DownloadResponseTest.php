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

use CodeIgniter\Exceptions\DownloadException;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use DateTime;
use DateTimeZone;

/**
 * @internal
 *
 * @group SeparateProcess
 */
final class DownloadResponseTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            unset($_SERVER['HTTP_USER_AGENT']);
        }
    }

    public function testCanGetStatusCode(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCantSetStatusCode(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->expectException(DownloadException::class);
        $response->setStatusCode(200);
    }

    public function testSetDateRemembersDateInUTC(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $datetime = DateTime::createFromFormat('!Y-m-d', '2000-03-10');
        $response->setDate($datetime);

        $date = clone $datetime;
        $date->setTimezone(new DateTimeZone('UTC'));

        $header = $response->getHeaderLine('Date');

        $this->assertSame($date->format('D, d M Y H:i:s') . ' GMT', $header);
    }

    public function testSetLastModifiedWithDateTimeObject(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $response->setLastModified(DateTime::createFromFormat('Y-m-d', '2000-03-10'));

        $date = DateTime::createFromFormat('Y-m-d', '2000-03-10');
        $date->setTimezone(new DateTimeZone('UTC'));

        $header = $response->getHeaderLine('Last-Modified');

        $this->assertSame($date->format('D, d M Y H:i:s') . ' GMT', $header);
    }

    public function testSetLastModifiedWithString(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $response->setLastModified('2000-03-10 10:23:45');

        $header = $response->getHeaderLine('Last-Modified');

        $this->assertSame('2000-03-10 10:23:45', $header);
    }

    public function testsentMethodSouldReturnRedirectResponse(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->assertInstanceOf(DownloadResponse::class, $response);
    }

    public function testSetContentType(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $response->setContentType('text/json');

        $this->assertSame('text/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testSetContentTypeNoCharSet(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $response->setContentType('application/octet-stream', '');

        $this->assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    public function testSetFileName(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);
        $response->setFileName('myFile.txt');
        $response->buildHeaders();

        $this->assertSame('attachment; filename="myFile.txt"; filename*=UTF-8\'\'myFile.txt', $response->getHeaderLine('Content-Disposition'));
    }

    public function testDispositionInline(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);
        $response->inline();
        $response->buildHeaders();
        $this->assertSame('inline', $response->getHeaderLine('Content-Disposition'));
    }

    public function testNoCache(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $response->noCache();

        $this->assertSame('private, no-transform, no-store, must-revalidate', $response->getHeaderLine('Cache-control'));
    }

    public function testCantSetCache(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->expectException(DownloadException::class);
        $response->setCache();
    }

    public function testWhenFilepathIsSetBinaryCanNotBeSet(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->expectException(DownloadException::class);
        $response->setFilePath(__FILE__);
        $response->setBinary('test');
    }

    public function testWhenBinaryIsSetFilepathCanNotBeSet(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->expectException(DownloadException::class);
        $response->setBinary('test');
        $response->setFilePath(__FILE__);
    }

    public function testCanNotSetNoFilepath(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->expectException(FileNotFoundException::class);
        $response->setFilePath('unit test');
    }

    public function testCanGetContentLength(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $this->assertSame(0, $response->getContentLength());

        $response = new DownloadResponse('unit-test.txt', true);

        $response->setBinary('1');
        $this->assertSame(1, $response->getContentLength());

        $response = new DownloadResponse('unit-test.txt', true);

        $size = filesize(SYSTEMPATH . 'Common.php');
        $response->setFilePath(SYSTEMPATH . 'Common.php');
        $this->assertSame($size, $response->getContentLength());
    }

    public function testIsSetDownloadableHeadlersFromBinary(): void
    {
        $response = new DownloadResponse('unit test.txt', false);

        $response->setBinary('test');
        $response->buildHeaders();

        $this->assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
        $this->assertSame('attachment; filename="unit test.txt"; filename*=UTF-8\'\'unit%20test.txt', $response->getHeaderLine('Content-Disposition'));
        $this->assertSame('0', $response->getHeaderLine('Expires-Disposition'));
        $this->assertSame('binary', $response->getHeaderLine('Content-Transfer-Encoding'));
        $this->assertSame('4', $response->getHeaderLine('Content-Length'));
    }

    public function testIsSetDownloadableHeadlersFromFile(): void
    {
        $response = new DownloadResponse('unit-test.php', false);

        $response->setFilePath(__FILE__);
        $response->buildHeaders();

        $this->assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
        $this->assertSame('attachment; filename="unit-test.php"; filename*=UTF-8\'\'unit-test.php', $response->getHeaderLine('Content-Disposition'));
        $this->assertSame('0', $response->getHeaderLine('Expires-Disposition'));
        $this->assertSame('binary', $response->getHeaderLine('Content-Transfer-Encoding'));
        $this->assertSame(filesize(__FILE__), (int) $response->getHeaderLine('Content-Length'));
    }

    public function testIfTheCharacterCodeIsOtherThanUtf8ReplaceItWithUtf8AndRawurlencode(): void
    {
        $response = new DownloadResponse(mb_convert_encoding('テスト.php', 'Shift-JIS', 'UTF-8'), false);

        $response->setFilePath(__FILE__);
        $response->setContentType('application/octet-stream', 'Shift-JIS');
        $response->buildHeaders();

        $this->assertSame('attachment; filename="' . mb_convert_encoding('テスト.php', 'Shift-JIS', 'UTF-8') . '"; filename*=UTF-8\'\'%E3%83%86%E3%82%B9%E3%83%88.php', $response->getHeaderLine('Content-Disposition'));
    }

    public function testFileExtensionIsUpperCaseWhenAndroidOSIs2(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; U; Android 2.0.3; ja-jp; SC-02C Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
        $response                   = new DownloadResponse('unit-test.php', false);

        $response->setFilePath(__FILE__);
        $response->buildHeaders();

        $this->assertSame('attachment; filename="unit-test.PHP"; filename*=UTF-8\'\'unit-test.PHP', $response->getHeaderLine('Content-Disposition'));
    }

    public function testIsSetContentTypeFromFilename(): void
    {
        $response = new DownloadResponse('unit-test.txt', true);

        $response->setBinary('test');
        $response->buildHeaders();

        $this->assertSame('text/plain; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testCanOutputFileBodyFromBinary(): void
    {
        $response = new DownloadResponse('unit-test.txt', false);

        $response->setBinary('test');

        ob_start();
        $response->sendBody();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame('test', $actual);
    }

    public function testCanOutputFileBodyFromFile(): void
    {
        $response = new DownloadResponse('unit-test.php', false);

        $response->setFilePath(__FILE__);

        ob_start();
        $response->sendBody();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame(file_get_contents(__FILE__), $actual);
    }

    public function testThrowExceptionWhenNoSetDownloadSource(): void
    {
        $response = new DownloadResponse('unit-test.php', false);

        $this->expectException(DownloadException::class);
        $response->sendBody();
    }

    public function testGetReason(): void
    {
        $response = new DownloadResponse('unit-test.php', false);
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testPretendOutput(): void
    {
        $response = new DownloadResponse('unit-test.php', false);
        $response->pretend(true);

        $response->setFilePath(__FILE__);

        ob_start();
        $response->send();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame(file_get_contents(__FILE__), $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRealOutput(): void
    {
        $response = new DownloadResponse('unit-test.php', false);
        $response->pretend(false);
        $response->setFilePath(__FILE__);

        // send it
        ob_start();
        $response->send();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // and what actually got sent?
        $this->assertHeaderEmitted('Content-Length: ' . filesize(__FILE__));
        $this->assertHeaderEmitted('Date:');
    }
}
