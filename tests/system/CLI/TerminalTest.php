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
    private const COLOR_HINTS = [
        'NO_COLOR',
        'FORCE_COLOR',
        'MSYSTEM',
        'TERM_PROGRAM',
        'COLORTERM',
        'ANSICON',
        'ConEmuANSI',
        'TERM',
    ];

    /**
     * @var array<string, array{false|string, mixed, array<array-key, mixed>|float|int|string|null}>
     */
    private array $savedHints = [];

    protected function setUp(): void
    {
        parent::setUp();

        $superglobals = service('superglobals');

        foreach (self::COLOR_HINTS as $name) {
            $this->savedHints[$name] = [getenv($name), $_ENV[$name] ?? null, $superglobals->server($name)];

            putenv($name);
            unset($_ENV[$name]);
            $superglobals->unsetServer($name);
        }
    }

    protected function tearDown(): void
    {
        $superglobals = service('superglobals');

        foreach ($this->savedHints as $name => [$process, $env, $server]) {
            putenv($process === false ? $name : "{$name}={$process}");

            if ($env === null) {
                unset($_ENV[$name]);
            } else {
                $_ENV[$name] = $env;
            }

            if ($server === null) {
                $superglobals->unsetServer($name);
            } else {
                $superglobals->setServer($name, $server);
            }
        }

        parent::tearDown();
    }

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

    public function testHasColorSupportFollowsNoColorInServer(): void
    {
        service('superglobals')->setServer('NO_COLOR', '1');

        $this->assertFalse((new Terminal())->hasColorSupport(STDOUT));
    }

    public function testHasColorSupportFollowsNoColorInDotEnv(): void
    {
        $_ENV['NO_COLOR'] = '1';

        $this->assertFalse((new Terminal())->hasColorSupport(STDOUT));
    }

    public function testHasColorSupportFollowsForceColorInServer(): void
    {
        service('superglobals')->setServer('FORCE_COLOR', '1');

        $this->assertTrue($this->createTerminal(tty: false)->hasColorSupport(STDOUT));
    }

    public function testHasColorSupportFallsBackToTheStreamCheck(): void
    {
        $this->assertTrue((new Terminal())->hasColorSupport(STDOUT));
    }

    /**
     * @param array<string, string> $env
     */
    #[DataProvider('provideHasColorSupportFollowsTheTerminalHints')]
    public function testHasColorSupportFollowsTheTerminalHints(array $env, bool $windows, bool $tty, bool $vt100, bool $expected): void
    {
        foreach ($env as $name => $value) {
            putenv("{$name}={$value}");
        }

        is_windows($windows);

        $this->assertSame($expected, $this->createTerminal($tty, $vt100)->hasColorSupport(STDOUT));
    }

    /**
     * @return iterable<string, array{array<string, string>, bool, bool, bool, bool}>
     */
    public static function provideHasColorSupportFollowsTheTerminalHints(): iterable
    {
        yield 'NO_COLOR over FORCE_COLOR' => [['NO_COLOR' => '1', 'FORCE_COLOR' => '1'], false, true, false, false];

        yield 'NO_COLOR of 0' => [['NO_COLOR' => '0'], false, true, false, false];

        yield 'NO_COLOR of false' => [['NO_COLOR' => 'false'], false, true, false, false];

        yield 'empty NO_COLOR' => [['NO_COLOR' => ''], false, true, false, true];

        yield 'FORCE_COLOR without a TTY' => [['FORCE_COLOR' => '1'], false, false, false, true];

        yield 'FORCE_COLOR of 0 without a TTY' => [['FORCE_COLOR' => '0'], false, false, false, true];

        yield 'FORCE_COLOR of false without a TTY' => [['FORCE_COLOR' => 'false'], false, false, false, true];

        yield 'empty FORCE_COLOR without a TTY' => [['FORCE_COLOR' => ''], false, false, false, false];

        yield 'no TTY' => [[], false, false, false, false];

        yield 'no TTY under MINGW64' => [['MSYSTEM' => 'MINGW64', 'TERM' => 'xterm'], true, false, false, true];

        yield 'no TTY under lowercase mingw32' => [['MSYSTEM' => 'mingw32', 'TERM' => 'xterm'], true, false, false, true];

        yield 'no TTY under MSYS' => [['MSYSTEM' => 'MSYS', 'TERM' => 'xterm'], true, false, false, false];

        yield 'Windows VT100' => [[], true, true, true, true];

        yield 'VT100 support ignored off Windows' => [['TERM' => 'dumb'], false, true, true, false];

        yield 'Hyper' => [['TERM_PROGRAM' => 'Hyper', 'TERM' => 'dumb'], true, true, false, true];

        yield 'COLORTERM' => [['COLORTERM' => 'truecolor'], true, true, false, true];

        yield 'ANSICON' => [['ANSICON' => '1'], true, true, false, true];

        yield 'ConEmuANSI on' => [['ConEmuANSI' => 'ON'], true, true, false, true];

        yield 'ConEmuANSI off' => [['ConEmuANSI' => 'OFF'], true, true, false, false];

        yield 'TERM dumb' => [['TERM' => 'dumb'], false, true, false, false];

        yield 'TTY without TERM' => [[], false, true, false, true];

        yield 'TTY with an unlisted TERM' => [['TERM' => 'unknown'], false, true, false, true];

        yield 'Windows without hints' => [[], true, true, false, false];

        yield 'Windows TERM xterm' => [['TERM' => 'xterm'], true, true, false, true];

        yield 'Windows TERM cygwin' => [['TERM' => 'cygwin'], true, true, false, true];

        yield 'Windows TERM 256color' => [['TERM' => 'tmux-256color'], true, true, false, true];

        yield 'Windows unlisted TERM' => [['TERM' => 'unknown'], true, true, false, false];
    }

    private function createTerminal(bool $tty, bool $vt100 = false): Terminal
    {
        $terminal = $this->createPartialMock(Terminal::class, ['streamSupports']);
        $terminal->method('streamSupports')->willReturnCallback(
            static fn (string $function): bool => $function === 'stream_isatty' ? $tty : $vt100,
        );

        return $terminal;
    }
}
