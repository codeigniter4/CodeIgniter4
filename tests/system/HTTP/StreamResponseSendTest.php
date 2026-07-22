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
use Config\App;
use Generator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * @internal
 */
#[Group('SeparateProcess')]
final class StreamResponseSendTest extends CIUnitTestCase
{
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendEmitsHeadersCookiesAndStream(): void
    {
        $response = new StreamResponse(static function (StreamResponse $stream): void {
            $stream->write('Hello ');
            $stream->write('World');
        });
        $response->pretend(false);
        $response->setCookie('foo', 'bar');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame('Hello World', $output);
        $this->assertHeaderEmitted('X-Accel-Buffering: no');
        $this->assertHeaderNotEmitted('Content-Encoding:');
        $this->assertHeaderEmitted('Set-Cookie: foo=bar;');
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendStreamsIterableChunks(): void
    {
        $response = new StreamResponse(['chunk1', 'chunk2', 'chunk3']);
        $response->pretend(false);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame('chunk1chunk2chunk3', $output);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendStreamsGeneratorChunks(): void
    {
        $generator = static function (): Generator {
            yield "line1\n";

            yield "line2\n";
        };

        $response = new StreamResponse($generator());
        $response->pretend(false);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame("line1\nline2\n", $output);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendRespectsCustomHeaders(): void
    {
        $response = new StreamResponse(static function (StreamResponse $stream): void {
            $stream->write("id,email\n");
        });
        $response->pretend(false);
        $response->setContentType('text/csv');
        $response->setHeader('X-Accel-Buffering', 'yes');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame("id,email\n", $output);
        $this->assertHeaderEmitted('Content-Type: text/csv; charset=UTF-8');
        $this->assertHeaderEmitted('X-Accel-Buffering: yes');
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendEmitsCustomStatusCode(): void
    {
        $response = new StreamResponse(static function (): void {
        });
        $response->pretend(false);
        $response->setStatusCode(202);

        ob_start();
        $response->send();
        ob_end_clean();

        $this->assertSame(202, http_response_code());
    }

    /**
     * This test does not test that CSP is handled properly -
     * it makes sure that sending gives CSP a chance to do its thing.
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendEmitsCspHeaderWhenEnabled(): void
    {
        $this->resetFactories();
        $this->resetServices();

        config(App::class)->CSPEnabled = true;

        $response = new StreamResponse(static function (): void {
        });
        $response->pretend(false);

        ob_start();
        $response->send();
        ob_end_clean();

        $this->assertHeaderEmitted('Content-Security-Policy:');
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    #[WithoutErrorHandler]
    public function testSendSkipsCspHeaderForSSE(): void
    {
        $this->resetFactories();
        $this->resetServices();

        config(App::class)->CSPEnabled = true;

        $response = new SSEResponse(static function (): void {
        });
        $response->pretend(false);

        ob_start();
        $response->send();
        ob_end_clean();

        $this->assertHeaderNotEmitted('Content-Security-Policy:');
    }
}
