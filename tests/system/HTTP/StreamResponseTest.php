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
use Generator;
use IteratorAggregate;
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

    public function testConstructorTreatsCallableIterableAsCallable(): void
    {
        $callbackOrChunks = new class () implements IteratorAggregate {
            public function __invoke(StreamResponse $stream): void
            {
                $stream->write('Hello');
            }

            /**
             * @return Generator<int, string>
             */
            public function getIterator(): Generator
            {
                yield 'iterable chunk';
            }
        };

        $response = new StreamResponse($callbackOrChunks);
        $response->pretend();

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertSame('Hello', $output);
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
        /** @var iterable<string> $chunks */
        $chunks   = ['a', 'b'];
        $response = (new Response())->stream($chunks);

        $this->assertInstanceOf(StreamResponse::class, $response);
    }

    public function testStreamFactoryCopiesProtocolVersion(): void
    {
        $response = (new Response())
            ->setProtocolVersion('2.0')
            ->stream(static function (): void {
            });

        $this->assertSame('2.0', $response->getProtocolVersion());
    }

    public function testEventStreamFactoryReturnsSSEResponse(): void
    {
        $response = (new Response())->eventStream(static function (): void {
        });

        $this->assertInstanceOf(SSEResponse::class, $response);
    }

    public function testEventStreamFactoryCopiesProtocolVersion(): void
    {
        $response = (new Response())
            ->setProtocolVersion('2.0')
            ->eventStream(static function (): void {
            });

        $this->assertSame('2.0', $response->getProtocolVersion());
    }
}
