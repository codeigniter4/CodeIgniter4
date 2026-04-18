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

use CodeIgniter\CLI\Exceptions\CLIException;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Commands\Legacy\AppInfo;

/**
 * @internal
 */
#[CoversClass(BaseCommand::class)]
#[CoversClass(CLIException::class)]
#[Group('Others')]
final class BaseCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    #[After]
    #[Before]
    protected function resetCli(): void
    {
        CLI::reset();
    }

    public function testRunCommand(): void
    {
        $command = new AppInfo(single_service('logger'), single_service('commands'));

        $this->assertSame(0, $command->run([]));
        $this->assertSame(
            sprintf("\nCodeIgniter Version: %s\n", CodeIgniter::CI_VERSION),
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testCallingOtherCommands(): void
    {
        $command = new AppInfo(single_service('logger'), single_service('commands'));

        $this->assertSame(0, $command->helpMe());
        $this->assertStringContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }

    public function testShowError(): void
    {
        $command = new AppInfo(single_service('logger'), single_service('commands'));

        $this->assertSame(1, $command->bomb());
        $this->assertStringContainsString('[CodeIgniter\CLI\Exceptions\CLIException]', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Invalid "background" color: "Background".', $this->getStreamFilterBuffer());
    }

    public function testShowHelp(): void
    {
        $command = new AppInfo(single_service('logger'), single_service('commands'));
        $command->showHelp();

        $this->assertSame(
            <<<'EOT'

                Usage:
                  app:info [arguments]

                Description:
                  Displays basic application information.

                Arguments:
                  draft  unused

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testMagicGetAndIsset(): void
    {
        $command = new AppInfo(single_service('logger'), single_service('commands'));

        $this->assertInstanceOf(Logger::class, $command->logger);
        $this->assertInstanceOf(Commands::class, $command->commands);
        $this->assertSame('demo', $command->group);
        $this->assertSame('app:info', $command->name);
        $this->assertNull($command->foo); // @phpstan-ignore property.notFound
    }
}
