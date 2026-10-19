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

namespace CodeIgniter\CLI;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(Terminal::class)]
#[Group('Others')]
final class TerminalTest extends CIUnitTestCase
{
    public function testDimensionsArePositive(): void
    {
        $terminal = new Terminal();

        $this->assertGreaterThan(0, $terminal->getWidth());
        $this->assertGreaterThan(0, $terminal->getHeight());
    }

    public function testDefaultsReplaceUnreportedDimensions(): void
    {
        $terminal = new Terminal();

        $this->setPrivateProperty($terminal, 'detected', true);
        $this->setPrivateProperty($terminal, 'width', 0);
        $this->setPrivateProperty($terminal, 'height', 0);

        $this->assertSame(120, $terminal->getWidth(120));
        $this->assertSame(40, $terminal->getHeight(40));
    }

    public function testDimensionsAreDetectedOnceAndCached(): void
    {
        $terminal = new Terminal();

        $this->setPrivateProperty($terminal, 'detected', true);
        $this->setPrivateProperty($terminal, 'width', 9999);
        $this->setPrivateProperty($terminal, 'height', 9998);

        $this->assertSame(9999, $terminal->getWidth());
        $this->assertSame(9998, $terminal->getHeight());

        $terminal->generateDimensions();

        $this->assertNotSame(9999, $this->getPrivateProperty($terminal, 'width'));
        $this->assertNotSame(9998, $this->getPrivateProperty($terminal, 'height'));
    }

    public function testStreamSupportsChecksTheFunctionWhenTesting(): void
    {
        $terminal = new Terminal();

        $this->assertTrue($terminal->streamSupports('stream_isatty', STDOUT));
        $this->assertSame(
            function_exists('sapi_windows_vt100_support'),
            $terminal->streamSupports('sapi_windows_vt100_support', STDOUT),
        );
    }

    public function testHasColorSupportFollowsNoColorInTheEnvironment(): void
    {
        $noColor = getenv('NO_COLOR');
        putenv('NO_COLOR=1');

        try {
            $this->assertFalse((new Terminal())->hasColorSupport(STDOUT));
        } finally {
            putenv($noColor === false ? 'NO_COLOR' : "NO_COLOR={$noColor}");
        }
    }

    public function testHasColorSupportFollowsNoColorInServer(): void
    {
        $superglobals = service('superglobals');

        $noColor = $superglobals->server('NO_COLOR');
        $superglobals->setServer('NO_COLOR', '1');

        try {
            $this->assertFalse((new Terminal())->hasColorSupport(STDOUT));
        } finally {
            if ($noColor === null) {
                $superglobals->unsetServer('NO_COLOR');
            } else {
                $superglobals->setServer('NO_COLOR', $noColor);
            }
        }
    }

    public function testHasColorSupportOnHyperTerminals(): void
    {
        $termProgram = getenv('TERM_PROGRAM');
        putenv('TERM_PROGRAM=Hyper');

        try {
            $this->assertTrue((new Terminal())->hasColorSupport(STDOUT));
        } finally {
            putenv($termProgram === false ? 'TERM_PROGRAM' : "TERM_PROGRAM={$termProgram}");
        }
    }

    public function testHasColorSupportFallsBackToTheStreamCheck(): void
    {
        $this->assertTrue((new Terminal())->hasColorSupport(STDOUT));
    }

    /**
     * @param array<string, string> $env
     */
    #[DataProvider('provideHasColorSupportOnWindowsFollowsTheConsoleHints')]
    public function testHasColorSupportOnWindowsFollowsTheConsoleHints(array $env, bool $expected): void
    {
        $saved = [];

        foreach (['ANSICON', 'ConEmuANSI', 'TERM'] as $name) {
            $saved[$name] = getenv($name);
            putenv($name);
        }

        foreach ($env as $name => $value) {
            putenv("{$name}={$value}");
        }

        is_windows(true);

        try {
            $this->assertSame(
                $expected || function_exists('sapi_windows_vt100_support'),
                (new Terminal())->hasColorSupport(STDOUT),
            );
        } finally {
            foreach ($saved as $name => $value) {
                putenv($value === false ? $name : "{$name}={$value}");
            }
        }
    }

    /**
     * @return iterable<string, array{array<string, string>, bool}>
     */
    public static function provideHasColorSupportOnWindowsFollowsTheConsoleHints(): iterable
    {
        yield 'no hints' => [[], false];

        yield 'ANSICON' => [['ANSICON' => '1'], true];

        yield 'ConEmuANSI on' => [['ConEmuANSI' => 'ON'], true];

        yield 'ConEmuANSI off' => [['ConEmuANSI' => 'OFF'], false];

        yield 'TERM xterm' => [['TERM' => 'xterm'], true];

        yield 'TERM other' => [['TERM' => 'xterm-256color'], false];
    }
}
