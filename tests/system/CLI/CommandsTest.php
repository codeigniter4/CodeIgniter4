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

use App\Commands\ListCommands as AppListCommands;
use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Commands\ListCommands;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Support\Commands\AppInfo;

/**
 * @internal
 */
#[CoversClass(Commands::class)]
#[Group('Others')]
final class CommandsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    #[After]
    #[Before]
    protected function resetAll(): void
    {
        $this->resetServices();

        CLI::reset();
    }

    public function testRunOnUnknownCommand(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->run('app:unknown', []));
        $this->assertArrayNotHasKey('app:unknown', $commands->getCommands());
        $this->assertStringContainsString('Command "app:unknown" not found', $this->getStreamFilterBuffer());
    }

    public function testRunOnUnknownCommandButWithOneAlternative(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->run('app:inf', []));
        $this->assertSame(
            <<<'EOT'

                Command "app:inf" not found.

                Did you mean this?
                    app:info

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testRunOnUnknownCommandButWithMultipleAlternatives(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->run('app:', []));
        $this->assertSame(
            <<<'EOT'

                Command "app:" not found.

                Did you mean one of these?
                    app:destructive
                    app:info

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testRunOnAbstractCommandCannotBeRun(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->run('app:pablo', []));
        $this->assertArrayNotHasKey('app:pablo', $commands->getCommands());
        $this->assertStringContainsString('Command "app:pablo" not found', $this->getStreamFilterBuffer());
    }

    public function testRunOnKnownCommand(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_SUCCESS, $commands->run('app:info', []));
        $this->assertArrayHasKey('app:info', $commands->getCommands());
        $this->assertStringContainsString('CodeIgniter Version', $this->getStreamFilterBuffer());
    }

    public function testDestructiveCommandIsNotRisky(): void
    {
        $this->expectException(RuntimeException::class);

        command('app:destructive');
    }

    public function testDiscoverCommandsDoNotRunTwice(): void
    {
        $locator = $this->createMock(FileLocatorInterface::class);
        $locator
            ->expects($this->once())
            ->method('listFiles')
            ->with('Commands/')
            ->willReturn([SUPPORTPATH . 'Commands/AppInfo.php']);
        $locator
            ->expects($this->once())
            ->method('findQualifiedNameFromPath')
            ->with(SUPPORTPATH . 'Commands/AppInfo.php')
            ->willReturn(AppInfo::class);
        Services::injectMock('locator', $locator);

        $commands = new Commands(); // discoverCommands will be called in the constructor
        $commands->discoverCommands();
    }

    public function testDiscoverCommandsWithNoFiles(): void
    {
        $locator = $this->createMock(FileLocatorInterface::class);
        $locator
            ->expects($this->once())
            ->method('listFiles')
            ->with('Commands/')
            ->willReturn([]);
        $locator
            ->expects($this->never())
            ->method('findQualifiedNameFromPath');
        Services::injectMock('locator', $locator);

        new Commands();
    }

    public function testDiscoveredCommandsCanBeOverridden(): void
    {
        // The fixture sits outside any PSR-4 root, so the autoloader cannot load it.
        require_once SUPPORTPATH . '_command/ListCommands.php';

        $files = [
            SUPPORTPATH . '_command/ListCommands.php' => AppListCommands::class,
            SYSTEMPATH . 'Commands/ListCommands.php'  => ListCommands::class,
        ];

        $locator = $this->createMock(FileLocatorInterface::class);
        $locator->method('listFiles')->with('Commands/')->willReturn(array_keys($files));
        $locator->method('findQualifiedNameFromPath')->willReturnCallback(static fn (string $file): string => $files[$file]);
        Services::injectMock('locator', $locator);

        (new Commands())->run('list', []);

        $this->assertStringContainsString('This is App\Commands\ListCommands', $this->getStreamFilterBuffer());
        $this->assertStringNotContainsString('Displays basic usage information.', $this->getStreamFilterBuffer());
    }
}
