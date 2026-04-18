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

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\CLI\Exceptions\CommandNotFoundException;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use ErrorException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use RuntimeException;
use Tests\Support\Commands\Legacy\AppInfo;
use Tests\Support\Commands\Modern\AppAboutCommand;
use Tests\Support\Duplicates\DuplicateLegacy;
use Tests\Support\Duplicates\DuplicateModern;
use Tests\Support\InvalidCommands\EmptyCommandName;
use Tests\Support\InvalidCommands\NoAttributeCommand;

/**
 * @internal
 */
#[CoversClass(Commands::class)]
#[CoversClass(CommandNotFoundException::class)]
#[Group('Others')]
final class CommandsTest extends CIUnitTestCase
{
    use ReflectionHelper;
    use StreamFilterTrait;

    #[After]
    #[Before]
    protected function resetAll(): void
    {
        $this->resetServices();

        CLI::reset();
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private function copyCommand(string $path): void
    {
        if (! is_dir(APPPATH . 'Commands')) {
            mkdir(APPPATH . 'Commands');
        }

        copy($path, APPPATH . 'Commands/' . basename($path));
    }

    private function deleteCommand(string $path): void
    {
        if (is_file(APPPATH . 'Commands/' . basename($path))) {
            unlink(APPPATH . 'Commands/' . basename($path));
        }
    }

    public function testRunOnUnknownCommand(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runLegacy('app:unknown', []));
        $this->assertArrayNotHasKey('app:unknown', $commands->getCommands());
        $this->assertSame("\nCommand \"app:unknown\" not found.\n", $this->getUndecoratedBuffer());

        $this->resetStreamFilterBuffer();
        CLI::resetLastWrite();

        $this->assertSame(EXIT_ERROR, $commands->runCommand('app:unknown', [], []));
        $this->assertArrayNotHasKey('app:unknown', $commands->getModernCommands());
        $this->assertSame("\nCommand \"app:unknown\" not found.\n", $this->getUndecoratedBuffer());
    }

    public function testRunOnUnknownLegacyCommandButWithOneAlternative(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runLegacy('app:inf', []));
        $this->assertSame(
            <<<'EOT'

                Command "app:inf" not found.

                Did you mean this?
                    app:info

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testRunOnUnknownModernCommandButWithOneAlternative(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runCommand('app:ab', [], []));
        $this->assertSame(
            <<<'EOT'

                Command "app:ab" not found.

                Did you mean this?
                    app:about

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testRunOnUnknownLegacyCommandButWithMultipleAlternatives(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runLegacy('app:', []));
        $this->assertSame(
            <<<'EOT'

                Command "app:" not found.

                Did you mean one of these?
                    app:about
                    app:destructive
                    app:info

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testRunOnUnknownLegacyCommandAlsoSuggestsModernAlternatives(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runLegacy('app:ab', []));
        $this->assertSame(
            <<<'EOT'

                Command "app:ab" not found.

                Did you mean this?
                    app:about

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testRunOnUnknownModernCommandAlsoSuggestsLegacyAlternatives(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runCommand('app:inf', [], []));
        $this->assertSame(
            <<<'EOT'

                Command "app:inf" not found.

                Did you mean this?
                    app:info

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testRunOnUnknownModernCommandButWithMultipleAlternatives(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runCommand('clear', [], []));
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

    public function testRunOnAbstractLegacyCommandCannotBeRun(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_ERROR, $commands->runLegacy('app:pablo', []));
        $this->assertArrayNotHasKey('app:pablo', $commands->getCommands());
        $this->assertSame("\nCommand \"app:pablo\" not found.\n", $this->getUndecoratedBuffer());
    }

    public function testRunOnKnownLegacyCommand(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_SUCCESS, $commands->runLegacy('app:info', []));
        $this->assertArrayHasKey('app:info', $commands->getCommands());
        $this->assertSame(
            sprintf("\nCodeIgniter Version: %s\n", CodeIgniter::CI_VERSION),
            $this->getUndecoratedBuffer(),
        );
    }

    public function testRunOnKnownModernCommand(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_SUCCESS, $commands->runCommand('app:about', ['a'], []));
        $this->assertArrayHasKey('app:about', $commands->getModernCommands());
        $this->assertSame(
            sprintf("\nCodeIgniter Version: %s\n", CodeIgniter::CI_VERSION),
            $this->getUndecoratedBuffer(),
        );
    }

    public function testRunOnLegacyCommandReturningNullIsDeprecated(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Since v4.8.0, commands must return an integer exit code. Last command "null:return" exited with null. Defaulting to EXIT_SUCCESS.');

        (new Commands())->runLegacy('null:return', []);
    }

    public function testRunMethodIsDeprecatedInFavorOfRunLegacy(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Since v4.8.0, "CodeIgniter\\CLI\\Commands::run()" is deprecated. Use "CodeIgniter\\CLI\\Commands::runLegacy()" instead.');

        (new Commands())->run('app:info', []);
    }

    public function testDiscoveryWarnsWhenSameCommandNameExistsInBothRegistries(): void
    {
        $this->injectDuplicateLocator();

        $message = wordwrap(
            sprintf(
                'Warning: The "dup:test" command is defined as both legacy (%s) and modern (%s). The legacy command will be executed. Please rename or remove one.',
                DuplicateLegacy::class,
                DuplicateModern::class,
            ),
            CLI::getWidth(),
        );

        $commands = new Commands();

        $this->assertSame("\n{$message}\n", $this->getUndecoratedBuffer());
        $this->assertArrayHasKey('dup:test', $commands->getCommands());
        $this->assertArrayHasKey('dup:test', $commands->getModernCommands());
    }

    public function testHasLegacyCommand(): void
    {
        $commands = new Commands();

        $this->assertTrue($commands->hasLegacyCommand('app:info'));
        $this->assertFalse($commands->hasLegacyCommand('app:about'));
        $this->assertFalse($commands->hasLegacyCommand('app:unknown'));
    }

    public function testHasModernCommand(): void
    {
        $commands = new Commands();

        $this->assertTrue($commands->hasModernCommand('app:about'));
        $this->assertFalse($commands->hasModernCommand('app:info'));
        $this->assertFalse($commands->hasModernCommand('app:unknown'));
    }

    public function testCollidingCommandNameIsDetectableFromBothRegistries(): void
    {
        $this->injectDuplicateLocator();

        $commands = new Commands();

        $this->assertTrue($commands->hasLegacyCommand('dup:test'));
        $this->assertTrue($commands->hasModernCommand('dup:test'));
    }

    public function testDestructiveCommandIsNotRisky(): void
    {
        $this->expectException(RuntimeException::class);

        command('app:destructive');
    }

    public function testGetCommand(): void
    {
        $commands = new Commands();

        $this->assertInstanceOf(AppInfo::class, $commands->getCommand('app:info', legacy: true));
        $this->assertInstanceOf(AppAboutCommand::class, $commands->getCommand('app:about'));
    }

    public function testGetCommandOnUnknownLegacyCommand(): void
    {
        $this->expectException(CommandNotFoundException::class);
        $this->expectExceptionMessage('Command "app:unknown" not found.');

        (new Commands())->getCommand('app:unknown', legacy: true);
    }

    public function testGetCommandOnUnknownModernCommand(): void
    {
        $this->expectException(CommandNotFoundException::class);
        $this->expectExceptionMessage('Command "app:unknown" not found.');

        (new Commands())->getCommand('app:unknown');
    }

    public function testDiscoverCommandsDoNotRunTwice(): void
    {
        $locator = $this->createMock(FileLocatorInterface::class);
        $locator
            ->expects($this->once())
            ->method('listFiles')
            ->with('Commands/')
            ->willReturn([
                SUPPORTPATH . 'Commands/Legacy/AppInfo.php',
                SUPPORTPATH . 'Commands/Modern/AppAboutCommand.php',
            ]);
        $locator
            ->expects($this->exactly(2))
            ->method('findQualifiedNameFromPath')
            ->willReturnMap([
                [SUPPORTPATH . 'Commands/Legacy/AppInfo.php', AppInfo::class],
                [SUPPORTPATH . 'Commands/Modern/AppAboutCommand.php', AppAboutCommand::class],
            ]);
        Services::injectMock('locator', $locator);

        $commands = new Commands(); // discoverCommands will be called in the constructor
        $commands->discoverCommands();
    }

    public function testDiscoverySkipsModernCommandWithoutCommandAttribute(): void
    {
        $path = SUPPORTPATH . 'InvalidCommands/NoAttributeCommand.php';

        $locator = $this->createMock(FileLocatorInterface::class);
        $locator
            ->expects($this->once())
            ->method('listFiles')
            ->with('Commands/')
            ->willReturn([$path]);
        $locator
            ->expects($this->once())
            ->method('findQualifiedNameFromPath')
            ->with($path)
            ->willReturn(NoAttributeCommand::class);
        Services::injectMock('locator', $locator);

        $commands = new Commands();

        $this->assertSame([], $commands->getModernCommands());
        $this->assertSame([], $commands->getCommands());
    }

    public function testDiscoveryLogsErrorWhenCommandAttributeFailsToInstantiate(): void
    {
        $path = SUPPORTPATH . 'InvalidCommands/EmptyCommandName.php';

        $locator = $this->createMock(FileLocatorInterface::class);
        $locator
            ->expects($this->once())
            ->method('listFiles')
            ->with('Commands/')
            ->willReturn([$path]);
        $locator
            ->expects($this->once())
            ->method('findQualifiedNameFromPath')
            ->with($path)
            ->willReturn(EmptyCommandName::class);
        Services::injectMock('locator', $locator);

        $logger = $this->createMock(Logger::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with($this->callback(static fn (string $message): bool => $message !== ''));

        $commands = new Commands($logger);

        $this->assertSame([], $commands->getModernCommands());
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

    public function testVerifyCommandThrowsDeprecationWhenCommandsArrayIsPassed(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Since v4.8.0, the $commands parameter of CodeIgniter\CLI\Commands::verifyCommand() is no longer used.');

        $commands = new Commands();
        $commands->verifyCommand('app:info', $commands->getCommands());
    }

    public function testGetCommandAlternativesThrowsDeprecationWhenCommandsArrayIsPassed(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Since v4.8.0, the $collection parameter of CodeIgniter\CLI\Commands::getCommandAlternatives() is no longer used.');

        $commands = new Commands();
        self::getPrivateMethodInvoker($commands, 'getCommandAlternatives')('app:inf', $commands->getCommands());
    }

    public function testDiscoveredLegacyCommandsCanBeOverridden(): void
    {
        $this->copyCommand(SUPPORTPATH . '_command/AppInfo.php');

        command('app:info');

        $this->assertStringContainsString('This is App\Commands\AppInfo', $this->getStreamFilterBuffer());
        $this->assertStringNotContainsString('CodeIgniter Version:', $this->getStreamFilterBuffer());

        $this->deleteCommand(SUPPORTPATH . '_command/AppInfo.php');
    }

    public function testDiscoveredModernCommandsCanBeOverridden(): void
    {
        $this->copyCommand(SUPPORTPATH . '_command/AppAboutCommand.php');

        command('app:about a');

        $this->assertStringContainsString('This is App\Commands\AppAboutCommand', $this->getStreamFilterBuffer());
        $this->assertStringNotContainsString('CodeIgniter Version:', $this->getStreamFilterBuffer());

        $this->deleteCommand(SUPPORTPATH . '_command/AppAboutCommand.php');
    }

    private function injectDuplicateLocator(): void
    {
        $legacyFile = (new ReflectionClass(DuplicateLegacy::class))->getFileName();
        $modernFile = (new ReflectionClass(DuplicateModern::class))->getFileName();

        $locator = $this->getMockBuilder(FileLocator::class)
            ->setConstructorArgs([service('autoloader')])
            ->onlyMethods(['listFiles', 'findQualifiedNameFromPath'])
            ->getMock();
        $locator->expects($this->once())
            ->method('listFiles')
            ->with('Commands/')
            ->willReturn([$legacyFile, $modernFile]);
        $locator->expects($this->exactly(2))
            ->method('findQualifiedNameFromPath')
            ->willReturnMap([
                [$legacyFile, DuplicateLegacy::class],
                [$modernFile, DuplicateModern::class],
            ]);
        Services::injectMock('locator', $locator);
    }
}
