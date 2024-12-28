<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Log\Logger;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;
use Config\Services;
use LogicException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\Log\Handlers\TestHandler;

/**
 * @internal
 */
#[Group('Others')]
final class RedirectExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        Services::reset();
        Services::injectMock('logger', new Logger(new LoggerConfig()));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the current time.
        Time::setTestNow();
    }

    public function testResponse(): void
    {
        $response = (new RedirectException(
            service('response')
                ->redirect('redirect')
                ->setCookie('cookie', 'value')
                ->setHeader('Redirect-Header', 'value')
        ))->getResponse();

        $this->assertSame('redirect', $response->getHeaderLine('location'));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('value', $response->getHeaderLine('Redirect-Header'));
        $this->assertSame('value', $response->getCookie('cookie')->getValue());
    }

    public function testResponseWithoutLocation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The Response object passed to RedirectException does not contain a redirect address.'
        );

        new RedirectException(service('response'));
    }

    public function testResponseWithoutStatusCode(): void
    {
        $response = (new RedirectException(service('response')->setHeader('Location', 'location')))->getResponse();

        $this->assertSame('location', $response->getHeaderLine('location'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testLoggingLocationHeader(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $uri      = 'http://location';
        $expected = 'INFO - ' . Time::now()->format('Y-m-d') . ' --> REDIRECTED ROUTE at ' . $uri;
        $response = (new RedirectException(service('response')->redirect($uri)))->getResponse();

        $logs = TestHandler::getLogs();

        $this->assertSame($uri, $response->getHeaderLine('Location'));
        $this->assertSame('', $response->getHeaderLine('Refresh'));
        $this->assertSame($expected, $logs[0]);
    }

    public function testLoggingRefreshHeader(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $uri      = 'http://location';
        $expected = 'INFO - ' . Time::now()->format('Y-m-d') . ' --> REDIRECTED ROUTE at ' . $uri;
        $response = (new RedirectException(service('response')->redirect($uri, 'refresh')))->getResponse();

        $logs = TestHandler::getLogs();

        $this->assertSame($uri, substr($response->getHeaderLine('Refresh'), 6));
        $this->assertSame('', $response->getHeaderLine('Location'));
        $this->assertSame($expected, $logs[0]);
    }
}
