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

use CodeIgniter\HTTP\Exceptions\RedirectException;
use Config\Services;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @group Others
 */
final class RedirectExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        Services::reset();
    }

    public function testResponse(): void
    {
        $response = Services::response()
            ->redirect('redirect')
            ->setCookie('cookie', 'value')
            ->setHeader('Redirect-Header', 'value');
        $exception = new RedirectException($response);

        $this->assertSame('redirect', $exception->getResponse()->getHeaderLine('location'));
        $this->assertSame(302, $exception->getResponse()->getStatusCode());
        $this->assertSame('value', $exception->getResponse()->getHeaderLine('Redirect-Header'));
        $this->assertSame('value', $exception->getResponse()->getCookie('cookie')->getValue());
    }

    public function testResponseWithoutLocation(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The Response object passed to RedirectException does not contain a redirect address.'
        );

        new RedirectException(Services::response());
    }

    public function testResponseWithoutStatusCode(): void
    {
        $response  = Services::response()->setHeader('Location', 'location');
        $exception = new RedirectException($response);

        $this->assertSame('location', $exception->getResponse()->getHeaderLine('location'));
        $this->assertSame(302, $exception->getResponse()->getStatusCode());
    }
}
