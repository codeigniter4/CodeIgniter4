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

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class HelpCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    #[After]
    #[Before]
    protected function resetCli(): void
    {
        CLI::reset();
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    public function testNoArgumentDescribesItself(): void
    {
        command('help');

        $this->assertSame(
            <<<'EOT'

                Usage:
                  help [options] [--] [<command_name>]

                Description:
                  Displays basic usage information.

                Arguments:
                  command_name          The command name. [default: "help"]

                Options:
                  -h, --help            Display help for the given command.
                      --no-header       Do not display the banner when running the command.
                  -N, --no-interaction  Do not ask any interactive questions.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDescribeCommandNoArguments(): void
    {
        command('help app:about');

        $this->assertSame(
            <<<'EOT'

                Usage:
                  app:about [options] [--] <required> [<optional>] [<array>...]
                  app:about required-value

                Description:
                  Displays basic application information.

                Arguments:
                  required              Unused required argument.
                  optional              Unused optional argument. [default: "val"]
                  array                 Unused array argument. [default: ["a", "b"]]

                Options:
                  -f, --foo=FOO         Option that requires a value.
                  -a, --bar[=BAR]       Option that optionally accepts a value.
                  -b, --baz=BAZ         Option that allows multiple values. (multiple values allowed)
                      --quux|--no-quux  Negatable option.
                  -h, --help            Display help for the given command.
                      --no-header       Do not display the banner when running the command.
                  -N, --no-interaction  Do not ask any interactive questions.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDescribeSpecificCommand(): void
    {
        command('help cache:clear');

        $this->assertSame(
            <<<'EOT'

                Usage:
                  cache:clear [options] [--] [<driver>]

                Description:
                  Clears the current system caches.

                Arguments:
                  driver                The cache driver to use. [default: "file"]

                Options:
                  -h, --help            Display help for the given command.
                      --no-header       Do not display the banner when running the command.
                  -N, --no-interaction  Do not ask any interactive questions.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDescribeLegacyCommandUsesLegacyShowHelp(): void
    {
        // `app:info` is a legacy BaseCommand fixture. Help must take the
        // legacy branch and delegate to BaseCommand::showHelp() instead of
        // rendering via the modern describeHelp() pipeline.
        command('help app:info');

        $this->assertSame(
            <<<'EOT'

                Usage:
                  app:info [arguments]

                Description:
                  Displays basic application information.

                Arguments:
                  draft  unused

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDescribeInexistentCommand(): void
    {
        command('help fixme');

        $this->assertSame("\nCommand \"fixme\" not found.\n", $this->getUndecoratedBuffer());
    }

    public function testDescribeInexistentCommandButWithAlternatives(): void
    {
        command('help clear');

        $this->assertSame(
            <<<'EOT'

                Command "clear" not found.

                Did you mean one of these?
                    cache:clear
                    debugbar:clear
                    logs:clear

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDescribeInexistentCommandSuggestsLegacyAlternatives(): void
    {
        command('help app:inf');

        $this->assertSame(
            <<<'EOT'

                Command "app:inf" not found.

                Did you mean this?
                    app:info

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDescribeUsingHelpOption(): void
    {
        command('cache:clear --help');

        $this->assertSame(
            <<<'EOT'

                Usage:
                  cache:clear [options] [--] [<driver>]

                Description:
                  Clears the current system caches.

                Arguments:
                  driver                The cache driver to use. [default: "file"]

                Options:
                  -h, --help            Display help for the given command.
                      --no-header       Do not display the banner when running the command.
                  -N, --no-interaction  Do not ask any interactive questions.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDescribeUsingHelpShortOption(): void
    {
        command('cache:clear -h');

        $this->assertSame(
            <<<'EOT'

                Usage:
                  cache:clear [options] [--] [<driver>]

                Description:
                  Clears the current system caches.

                Arguments:
                  driver                The cache driver to use. [default: "file"]

                Options:
                  -h, --help            Display help for the given command.
                      --no-header       Do not display the banner when running the command.
                  -N, --no-interaction  Do not ask any interactive questions.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testNormalHelpCommandHasNoBanner(): void
    {
        command('help');

        $this->assertStringNotContainsString(
            sprintf('CodeIgniter %s Command Line Tool', CodeIgniter::CI_VERSION),
            $this->getStreamFilterBuffer(),
        );
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    public function testHelpCommandWithDoubleHyphenStillRemovesBanner(): void
    {
        command('help -- list');

        $this->assertStringNotContainsString(
            sprintf('CodeIgniter %s Command Line Tool', CodeIgniter::CI_VERSION),
            $this->getStreamFilterBuffer(),
        );
        $this->assertStringContainsString('Lists the available commands.', $this->getStreamFilterBuffer());
    }
}
