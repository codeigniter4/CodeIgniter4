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

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * @internal
 */
#[Group('SeparateProcess')]
final class SSEResponseSendTest extends CIUnitTestCase
{
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendEmitsHeadersCookiesAndStream(): void
    {
        $response = new SSEResponse(static function (SSEResponse $sse): void {
            $sse->event('hello');
        });
        $response->pretend(false);
        $response->setCookie('foo', 'bar');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame("data: hello\n\n", $output);
        $this->assertHeaderEmitted('Content-Type: text/event-stream; charset=UTF-8');
        $this->assertHeaderEmitted('Cache-Control: no-cache');
        $this->assertHeaderEmitted('X-Accel-Buffering: no');
        $this->assertHeaderEmitted('Set-Cookie: foo=bar;');

        if (version_compare($response->getProtocolVersion(), '2.0', '<')) {
            $this->assertHeaderEmitted('Connection: keep-alive');
        } else {
            $this->assertHeaderNotEmitted('Connection: keep-alive');
        }
    }
}
