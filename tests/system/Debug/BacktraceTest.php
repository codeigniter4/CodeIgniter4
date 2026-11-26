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

namespace CodeIgniter\Debug;

use CodeIgniter\Debug\Inspection\Frame;
use CodeIgniter\Debug\Inspection\Frames;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use SensitiveParameterValue;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Backtrace::class)]
#[Group('Others')]
#[UsesClass(Frame::class)]
#[UsesClass(Frames::class)]
final class BacktraceTest extends CIUnitTestCase
{
    public function testFormatsAnEvalSourceLocationForTextAndCli(): void
    {
        $eval = sprintf("%s(17) : eval()'d code(3) : eval()'d code", ROOTPATH . 'app/Example.php');

        $this->assertSame('app/Example.php:17', Backtrace::location($eval, 2, cli: true));
        $this->assertSame('in app/Example.php (line 17)', Backtrace::location($eval, 2));
    }

    public function testFormatsArraysInlineAndSummarizesObjectsAndResources(): void
    {
        $resource = fopen('php://memory', 'rb');

        try {
            $file  = ROOTPATH . 'app/Example.php';
            $trace = new Frames([
                new Frame('run', $file, 42, 'Example', '->', [
                    new stdClass(), ['a' => 1, 'b' => [true]], [], null, $resource, 'value',
                ]),
            ]);

            $this->assertSame([
                " 1 Example()->run(Object(stdClass), ['a' => 1, 'b' => [true]], [], null, resource (stream), 'value') in app/Example.php (line 42)",
            ], Backtrace::lines($trace));
        } finally {
            fclose($resource);
        }
    }

    public function testFormatsRelativeAndInternalFramesWithoutArguments(): void
    {
        $trace = new Frames([
            new Frame('run', ROOTPATH . 'app/Example.php', 42, arguments: ['secret']),
            new Frame('array_map'),
        ]);

        $this->assertSame([
            ' 1 run() in app/Example.php (line 42)',
            ' 2 array_map() [internal function]',
        ], Backtrace::lines($trace, includeArguments: false));
    }

    public function testLocationPreservesExternalPathsAndHandlesMissingLocation(): void
    {
        $external = dirname(rtrim(ROOTPATH, '\\/')) . DIRECTORY_SEPARATOR . 'external.php';

        $this->assertSame('in ' . $external, Backtrace::location($external, null));
        $this->assertSame($external, Backtrace::location($external, null, cli: true));
        $this->assertSame('[internal function]', Backtrace::location(null, 12));
        $this->assertSame('[internal function]', Backtrace::location('', 12, cli: true));
        $this->assertSame([], Backtrace::lines(new Frames([])));
    }

    public function testArgumentPreviewUsesNamesAndSummarizesObjects(): void
    {
        $preview = Backtrace::argumentPreview(new Frame('array_map', arguments: [
            (object) ['privateValue' => 'secret'], ['hidden'], new SensitiveParameterValue('secret'),
        ]));

        $this->assertSame(['callback' => 'Object(stdClass)', 'array' => "[\n    'hidden',\n]", 'arrays' => '[redacted]'], $preview);
    }

    public function testArgumentPreviewLimitsArraysAndMasksNestedFields(): void
    {
        $preview = Backtrace::argumentPreview(new Frame('{closure}', arguments: [
            range(1, 10), ['account' => ['pin' => 'private value', 'nested' => ['deeper' => ['value' => 'too deep']]]],
        ]), ['account/pin']);

        $this->assertStringContainsString('… +2 more', $preview[0]);
        $this->assertStringContainsString("'pin' => [redacted]", $preview[1]);
        $this->assertStringContainsString("'deeper' => [...]", $preview[1]);
        $this->assertStringNotContainsString('private value', $preview[1]);
        $this->assertStringNotContainsString('too deep', $preview[1]);
    }

    public function testArgumentPreviewMasksPositionalPathsAndCredentialDsnValues(): void
    {
        $preview = Backtrace::argumentPreview(new Frame('{closure}', arguments: [
            'positional secret', ['private' => 'nested secret', 'public' => 'visible'], 'mysql://user:pass@host',
        ]), ['0', '1/private']);

        $this->assertSame([
            '[redacted]',
            "[\n    'private' => [redacted],\n    'public' => 'visible',\n]",
            '[redacted]',
        ], $preview);
    }

    public function testArgumentPreviewTruncatesAtUtf8Boundaries(): void
    {
        $prefix  = str_repeat('x', 498);
        $preview = Backtrace::argumentPreview(new Frame('{closure}', arguments: [$prefix . 'é']));

        $this->assertSame(["'" . $prefix . '…'], $preview);
    }

    public function testInlineFormattingRedactsNestedValuesAndDetectsOnlyAncestorReferences(): void
    {
        $shared        = ['label' => 'visible', 'pin' => 'hidden'];
        $cycle         = ['left' => &$shared, 'right' => &$shared];
        $cycle['self'] = &$cycle;

        $frame = new Frame('work', arguments: [&$cycle]);
        $trace = new Frames([$frame]);
        $line  = Backtrace::lines($trace, sensitiveKeys: ['pin'])[0];

        $this->assertSame(
            " 1 work(['left' => ['label' => 'visible', 'pin' => [redacted]], 'right' => ['label' => 'visible', 'pin' => [redacted]], 'self' => *RECURSION*]) [internal function]",
            $line,
        );
        $this->assertStringContainsString("'self' => *RECURSION*", Backtrace::argumentPreview($frame, ['pin'])[0]);
        $this->assertSame('hidden', $shared['pin']);
    }

    public function testArrayFormattingBoundsTheTotalValuesAcrossNestedBranches(): void
    {
        $items = array_fill(0, 8, array_fill(0, 8, 'entry'));
        $frame = new Frame('work', arguments: [$items]);
        $line  = Backtrace::lines(new Frames([$frame]))[0];

        $this->assertStringContainsString('… +', $line);
        $this->assertLessThan(32, substr_count($line, "'entry'"));
    }

    public function testInlineArraysRedactCredentialHeadersAndKeepEachFrameOnOneLine(): void
    {
        $frame = new Frame('work', arguments: [['Authorization' => 'hidden', 'note' => "first\nsecond"]]);

        $this->assertSame(
            " 1 work(['Authorization' => [redacted], 'note' => 'first\\nsecond']) [internal function]",
            Backtrace::lines(new Frames([$frame]))[0],
        );
    }

    public function testArgumentPreviewOfFrameWithoutParametersRaisesNoDeprecation(): void
    {
        $errors = [];

        set_error_handler(static function (int $severity, string $message) use (&$errors): bool {
            $errors[] = $message;

            return true;
        });

        try {
            $preview = Backtrace::argumentPreview(new Frame('require_once', arguments: ['/path/to/file.php']));
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $errors);
        $this->assertSame(["'/path/to/file.php'"], $preview);
    }
}
