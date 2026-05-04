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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Override the global passthru() within this namespace so the unit test
 * can capture the shell command that Serve::run() would otherwise execute.
 *
 * @internal
 */
function passthru(string $command, ?int &$result_code = null): void
{
    ServeTest::$capturedCommand = $command;
    $result_code                = 0;
}

/**
 * @internal
 */
#[Group('Others')]
final class ServeTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    /**
     * Captured shell command from the namespaced passthru() stub above.
     */
    public static string $capturedCommand = '';

    protected function setUp(): void
    {
        parent::setUp();

        self::$capturedCommand = '';
        $_SERVER['argv']       = ['spark', 'serve'];
    }

    protected function tearDown(): void
    {
        // Restore default argv so other tests are not affected.
        $_SERVER['argv'] = ['spark'];

        parent::tearDown();
    }

    public function testServeRunsWithDefaultHostAndPort(): void
    {
        command('serve');

        $this->assertStringContainsString(' -S ', self::$capturedCommand);
        $this->assertStringContainsString("'localhost:8080'", self::$capturedCommand);
    }

    public function testServeEscapesShellMetacharactersInHost(): void
    {
        $_SERVER['argv'] = ['spark', 'serve', '--host', '$(id)'];

        command('serve --host="$(id)"');

        // The literal '$(id)' must remain inside single quotes so /bin/sh -c
        // never expands it into a sub-shell. escapeshellarg() wraps the
        // entire host:port pair in single quotes and escapes any embedded
        // single quote.
        $this->assertStringNotContainsString(' $(id) ', self::$capturedCommand);
        $this->assertMatchesRegularExpression(
            "/'[^']*\\\$\\(id\\)[^']*:[0-9]+'/",
            self::$capturedCommand,
            'host:port must be wrapped in single quotes by escapeshellarg()',
        );
    }

    public function testServeEscapesEmbeddedSingleQuoteInHost(): void
    {
        $_SERVER['argv'] = ['spark', 'serve', '--host', "evil'host"];

        command("serve --host=\"evil'host\"");

        // escapeshellarg() turns a single quote into '\'' inside the wrapper,
        // so the dangerous quote can never break out of the argument.
        $this->assertStringNotContainsString(" evil'host:", self::$capturedCommand);
        $this->assertStringContainsString("'\\''", self::$capturedCommand);
    }
}
