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
#[Group('SeparateProcess')]
final class SSEResponseTest extends CIUnitTestCase
{
    public function testEventFormatsLinesAndSanitizesFields(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $result = $response->event("line1\nline2", "up\ndate", "1\n2");
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame(
            "event: update\nid: 12\ndata: line1\ndata: line2\n\n",
            $output,
        );
    }

    public function testCommentFormatsAsSseCommentLines(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $result = $response->comment("keep\nalive");
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame(": keep\n: alive\n\n", $output);
    }

    public function testRetryFormatsRetryField(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $result = $response->retry(1500);
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame("retry: 1500\n\n", $output);
    }

    public function testEventReturnsFalseOnJsonEncodeFailure(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        $data = [
            'bad' => "\xB1\x31",
        ];

        ob_start();
        $result = $response->event($data);
        $output = ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame('', $output);
    }

    public function testEventWithStringDataOnly(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $response->event('hello');
        $output = ob_get_clean();

        $this->assertSame("data: hello\n\n", $output);
    }

    public function testEventWithArrayDataJsonEncodes(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $response->event(['key' => 'value']);
        $output = ob_get_clean();

        $this->assertSame("data: {\"key\":\"value\"}\n\n", $output);
    }

    public function testEventWithEventNameOnly(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $response->event('data', 'update');
        $output = ob_get_clean();

        $this->assertSame("event: update\ndata: data\n\n", $output);
    }

    public function testEventWithIdOnly(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $response->event('data', null, '42');
        $output = ob_get_clean();

        $this->assertSame("id: 42\ndata: data\n\n", $output);
    }

    public function testEventNormalizesCarriageReturnLineFeed(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $response->event("a\r\nb");
        $output = ob_get_clean();

        $this->assertSame("data: a\ndata: b\n\n", $output);
    }

    public function testEventNormalizesCarriageReturn(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $response->event("a\rb");
        $output = ob_get_clean();

        $this->assertSame("data: a\ndata: b\n\n", $output);
    }

    public function testCommentSingleLine(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $response->comment('hello');
        $output = ob_get_clean();

        $this->assertSame(": hello\n\n", $output);
    }

    public function testSendBodyIsNoOp(): void
    {
        $response = new SSEResponse(static function (): void {
        });

        ob_start();
        $result = $response->sendBody();
        $output = ob_get_clean();

        $this->assertSame($response, $result);
        $this->assertSame('', $output);
    }
}
