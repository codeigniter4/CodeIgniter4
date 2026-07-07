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

/**
 * @internal
 */
#[Group('Others')]
final class StreamResponseTest extends CIUnitTestCase
{
    public function testWriteOutputsChunk(): void
    {
        $response = new StreamResponse(static function (): void {
        });

        ob_start();
        $result = $response->write('chunk');
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame('chunk', $output);
    }

    public function testWriteWithoutFlushOutputsChunk(): void
    {
        $response = new StreamResponse(static function (): void {
        });

        ob_start();
        $result = $response->write('chunk', false);
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame('chunk', $output);
    }

    public function testIsClientConnectedInCli(): void
    {
        $response = new StreamResponse(static function (): void {
        });

        $this->assertTrue($response->isClientConnected());
    }

    public function testSendBodyIsNoOp(): void
    {
        $response = new StreamResponse(static function (): void {
        });

        ob_start();
        $result = $response->sendBody();
        $output = ob_get_clean();

        $this->assertSame($response, $result);
        $this->assertSame('', $output);
    }

    public function testStatusCodeIsSettable(): void
    {
        $response = new StreamResponse(static function (): void {
        });
        $response->setStatusCode(206);

        $this->assertSame(206, $response->getStatusCode());
    }

    public function testStreamFactoryReturnsStreamResponse(): void
    {
        $response = (new Response())->stream(static function (): void {
        });

        $this->assertInstanceOf(StreamResponse::class, $response);
        $this->assertNotInstanceOf(SSEResponse::class, $response);
    }

    public function testStreamFactoryAcceptsIterable(): void
    {
        $response = (new Response())->stream(['a', 'b']);

        $this->assertInstanceOf(StreamResponse::class, $response);
    }

    public function testEventStreamFactoryReturnsSSEResponse(): void
    {
        $response = (new Response())->eventStream(static function (): void {
        });

        $this->assertInstanceOf(SSEResponse::class, $response);
    }
}
