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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
#[CoversClass(Frames::class)]
#[Group('Others')]
#[UsesClass(Frame::class)]
final class FramesTest extends TestCase
{
    public function testNormalizesClosureNamesAndNestedEvalLocations(): void
    {
        $exception = new RuntimeException('Example');
        $file      = ROOTPATH . 'app/Example.php';
        $eval      = sprintf("%s(17) : eval()'d code(3) : eval()'d code", $file);
        $frames    = Frames::fromException($exception, [
            ['file' => $eval, 'line' => 2, 'class' => 'Example', 'type' => '::', 'function' => '{closure:Example::run():13}'],
            ['function' => 'eval'],
            ['function' => 'App\{closure}'],
        ])->take(3);

        $this->assertSame('{closure}', $frames[0]->function);
        $this->assertSame('Example', $frames[0]->class);
        $this->assertSame($file, $frames[1]->file);
        $this->assertSame(17, $frames[1]->line);
        $this->assertSame('{closure}', $frames[2]->function);
    }

    public function testNormalizesThrowSiteAndKeepsArgumentsWithTheirCall(): void
    {
        $exception = new RuntimeException('Example');

        $frames = Frames::fromException($exception, [
            [
                'file'     => '/example/Caller.php',
                'line'     => 17,
                'class'    => 'Example',
                'type'     => '->',
                'function' => 'fail',
                'args'     => ['masked'],
            ],
            ['function' => 'array_map'],
        ]);
        $this->assertCount(3, $frames);

        [$first, $caller, $main] = $frames->take(3);

        $this->assertSame([
            'file'     => $exception->getFile(),
            'line'     => $exception->getLine(),
            'function' => 'fail',
            'class'    => 'Example',
            'type'     => '->',
        ], $first->metadata());
        $this->assertSame(['masked'], $first->arguments);
        $this->assertSame(['file' => '/example/Caller.php', 'line' => 17, 'function' => 'array_map'], $caller->metadata());
        $this->assertSame(['function' => '{main}'], $main->metadata());
        $this->assertSame([$first, $caller, $main], iterator_to_array($frames));
        $this->assertSame([$first, $caller], $frames->take(2));
    }

    public function testFromExceptionUsesTheCapturedTraceByDefault(): void
    {
        $capture   = static fn (): RuntimeException => new RuntimeException('Example');
        $exception = $capture();
        $frame     = Frames::fromException($exception)->take(1)[0];

        $this->assertSame('{closure}', $frame->function);
        $this->assertSame($exception->getFile(), $frame->file);
        $this->assertSame($exception->getLine(), $frame->line);
    }

    public function testEmptyTraceWithoutAStartingLocationHasNoFrames(): void
    {
        $frames = Frames::fromTrace([]);

        $this->assertCount(0, $frames);
        $this->assertSame([], iterator_to_array($frames));
    }

    public function testEmptyTraceWithAStartingLocationHasAnEntryFrame(): void
    {
        $frames = Frames::fromTrace([], '/example/index.php', 42);

        $this->assertCount(1, $frames);
        $this->assertSame(['file' => '/example/index.php', 'line' => 42, 'function' => '{main}'], $frames->take(1)[0]->metadata());
    }
}
