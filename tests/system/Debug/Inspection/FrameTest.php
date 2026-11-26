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

namespace CodeIgniter\Debug\Inspection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Frame::class)]
#[Group('Others')]
final class FrameTest extends TestCase
{
    public function testFormatsFunctionStaticAndInstanceCalls(): void
    {
        $function = new Frame('run');
        $static   = new Frame('run', class: 'Example', type: '::');
        $instance = new Frame('run', class: 'Example', type: '->');

        $this->assertSame('run()', $function->call());
        $this->assertSame('Example::run(42)', $static->call('42'));
        $this->assertSame('Example()->run(42)', $instance->call('42'));
    }

    public function testMetadataOmitsArgumentsAndMissingFields(): void
    {
        $frame = new Frame('run', '/example/file.php', arguments: ['private value']);

        $this->assertSame(['file' => '/example/file.php', 'function' => 'run'], $frame->metadata());
    }

    public function testResolvesSourceLocationWithoutALine(): void
    {
        $this->assertSame(['file' => '/example/file.php'], Frame::sourceLocation('/example/file.php', null));
    }

    public function testResolvesNestedEvalToTheOuterSourceLocation(): void
    {
        $file = "/example/file.php(17) : eval()'d code(3) : eval()'d code";

        $this->assertSame(['file' => '/example/file.php', 'line' => 17], Frame::sourceLocation($file, 2));
    }
}
