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

use App\Commands\AppAboutCommand as AppAboutCommandOverride;
use App\Commands\AppInfo as AppInfoOverride;
use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\CLI\Exceptions\CommandNotFoundException;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Exceptions\LogicException;
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
use Tests\Support\Commands\Modern\AliasedCommand;
use Tests\Support\Commands\Modern\AppAboutCommand;
use Tests\Support\Duplicates\DuplicateLegacy;
use Tests\Support\Duplicates\DuplicateModern;
use Tests\Support\Duplicates\HiddenDuplicateModern;
use Tests\Support\InvalidCommands\AliasClashCommand;
use Tests\Support\InvalidCommands\AliasSecondClashCommand;
use Tests\Support\InvalidCommands\AliasTargetCommand;
use Tests\Support\InvalidCommands\EmptyCommandName;
use Tests\Support\InvalidCommands\InvalidComponentGeneratorCommand;
use Tests\Support\InvalidCommands\NoAttributeCommand;
use Tests\Support\InvalidCommands\NoAttributeGeneratorCommand;

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

    private function loadOverrideFixture(string $file): string
    {
        $path = SUPPORTPATH . '_command/' . $file;

        // The fixture sits outside any PSR-4 root, so the autoloader cannot load it.
        require_once $path;

        return $path;
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

    public function testShadowedModernCommandAliasesAreNotRegistered(): void
    {
        $this->injectDuplicateLocator();

        $commands = new Commands();

        // The legacy command owns the name, so the shadowed modern command's
        // alias is dropped: neither listed nor resolvable.
        $this->assertSame([], $commands->getCommandAliases());
        $this->assertFalse($commands->hasModernCommand('dup:alias'));
    }

    public function testModernCommandAliasesAreRegistered(): void
    {
        $aliases = (new Commands())->getCommandAliases();

        $this->assertSame('fixture:aliased', $aliases['fixture:alias']);
        $this->assertSame('fixture:aliased', $aliases['fa']);
    }

    public function testHasModernCommandResolvesAliases(): void
    {
        $commands = new Commands();

        $this->assertTrue($commands->hasModernCommand('fixture:alias'));
        $this->assertTrue($commands->hasModernCommand('fa'));
    }

    public function testGetCommandResolvesAliasToCanonicalCommand(): void
    {
        $command = (new Commands())->getCommand('fixture:alias');

        $this->assertInstanceOf(AliasedCommand::class, $command);
        $this->assertSame('fixture:aliased', $command->getName());
    }

    public function testRunCommandViaAlias(): void
    {
        $commands = new Commands();

        $this->assertSame(EXIT_SUCCESS, $commands->runCommand('fa', [], []));
        $this->assertStringContainsString('Ran fixture:aliased.', $this->getStreamFilterBuffer());
    }

    public function testHiddenCommandIsRegisteredWithItsFlag(): void
    {
        $commands = (new Commands())->getModernCommands();

        $this->assertTrue($commands['fixture:hidden']['hidden']);
        $this->assertFalse($commands['fixture:aliased']['hidden']);
    }

    public function testIsHiddenCommand(): void
    {
        $commands = new Commands();

        $this->assertTrue($commands->isHiddenCommand('fixture:hidden'));
        $this->assertTrue($commands->isHiddenCommand('fixture:secret'));
        $this->assertFalse($commands->isHiddenCommand('fixture:aliased'));
        $this->assertFalse($commands->isHiddenCommand('fixture:alias'));
        $this->assertFalse($commands->isHiddenCommand('app:info'));
        $this->assertFalse($commands->isHiddenCommand('app:unknown'));
    }

    public function testIsHiddenCommandIsFalseWhenLegacyCommandShadowsIt(): void
    {
        $this->injectFixtureLocator([
            DuplicateLegacy::class       => SUPPORTPATH . 'Duplicates/DuplicateLegacy.php',
            HiddenDuplicateModern::class => SUPPORTPATH . 'Duplicates/HiddenDuplicateModern.php',
        ]);

        $this->assertFalse((new Commands())->isHiddenCommand('dup:test'));
    }

    public function testHiddenCommandRunsByName(): void
    {
        command('fixture:hidden');

        $this->assertSame(
            <<<'EOT'

                Ran fixture:hidden.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testHiddenCommandRunsByAlias(): void
    {
        command('fixture:secret');

        $this->assertSame(
            <<<'EOT'

                Ran fixture:hidden.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testHiddenCommandRunsThroughModernCall(): void
    {
        $commands = new Commands();
        $call     = $this->getPrivateMethodInvoker(new AliasedCommand($commands), 'call');

        $this->assertSame(EXIT_SUCCESS, $call('fixture:hidden'));
        $this->assertSame(EXIT_SUCCESS, $call('fixture:secret'));
        $this->assertSame(
            <<<'EOT'

                Ran fixture:hidden.
                Ran fixture:hidden.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testHiddenCommandRunsThroughLegacyCall(): void
    {
        $commands = new Commands();
        $call     = $this->getPrivateMethodInvoker(new AppInfo(service('logger'), $commands), 'call');

        $this->assertSame(EXIT_SUCCESS, $call('fixture:hidden'));
        $this->assertSame(EXIT_SUCCESS, $call('fixture:secret'));
        $this->assertSame(
            <<<'EOT'

                Ran fixture:hidden.
                Ran fixture:hidden.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testHiddenCommandAndItsAliasesAreNotSuggested(): void
    {
        $this->assertSame(['fixture:alias', 'fixture:aliased'], (new Commands())->getCommandAlternatives('fixture:'));
    }

    public function testMistypedHiddenCommandIsReportedWithoutSuggestion(): void
    {
        $commands = new Commands();

        $this->assertSame([], $commands->getCommandAlternatives('fixture:secre'));
        $this->assertSame(EXIT_ERROR, $commands->runCommand('fixture:hiddenn', [], []));
        $this->assertSame(
            <<<'EOT'

                Command "fixture:hiddenn" not found.

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testLegacyCommandShadowingHiddenModernCommandIsStillSuggested(): void
    {
        $this->injectFixtureLocator([
            DuplicateLegacy::class       => SUPPORTPATH . 'Duplicates/DuplicateLegacy.php',
            HiddenDuplicateModern::class => SUPPORTPATH . 'Duplicates/HiddenDuplicateModern.php',
        ]);

        $this->assertSame(['dup:test'], (new Commands())->getCommandAlternatives('dup:tes'));
    }

    public function testAliasClashingWithCommandNameFailsHard(): void
    {
        $this->injectFixtureLocator([
            AliasTargetCommand::class => SUPPORTPATH . 'InvalidCommands/AliasTargetCommand.php',
            AliasClashCommand::class  => SUPPORTPATH . 'InvalidCommands/AliasClashCommand.php',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Command alias "alias:target" of the "alias:source" command clashes with an existing command of the same name.');

        new Commands();
    }

    public function testAliasClashingWithAnotherAliasFailsHard(): void
    {
        $this->injectFixtureLocator([
            AliasClashCommand::class       => SUPPORTPATH . 'InvalidCommands/AliasClashCommand.php',
            AliasSecondClashCommand::class => SUPPORTPATH . 'InvalidCommands/AliasSecondClashCommand.php',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Command alias "alias:target" of the "alias:source-two" command is already used as an alias of the "alias:source" command.');

        new Commands();
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

    public function testDiscoveryLogsErrorForGeneratorCommandWithoutGeneratorAttribute(): void
    {
        $path = SUPPORTPATH . 'InvalidCommands/NoAttributeGeneratorCommand.php';

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
            ->willReturn(NoAttributeGeneratorCommand::class);
        Services::injectMock('locator', $locator);

        $logger = $this->createMock(Logger::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with($this->callback(static fn (string $message): bool => $message !== ''));

        $commands = new Commands($logger);

        $this->assertSame([], $commands->getModernCommands());
    }

    public function testDiscoveryLogsErrorWhenGeneratorAttributeFailsToInstantiate(): void
    {
        $path = SUPPORTPATH . 'InvalidCommands/InvalidComponentGeneratorCommand.php';

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
            ->willReturn(InvalidComponentGeneratorCommand::class);
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
        $commands->getCommandAlternatives('app:inf', $commands->getCommands());
    }

    public function testDiscoveredLegacyCommandsCanBeOverridden(): void
    {
        $this->injectFixtureLocator([
            AppInfoOverride::class => $this->loadOverrideFixture('AppInfo.php'),
            AppInfo::class         => SUPPORTPATH . 'Commands/Legacy/AppInfo.php',
        ]);

        (new Commands())->runLegacy('app:info', []);

        $this->assertStringContainsString('This is App\Commands\AppInfo', $this->getStreamFilterBuffer());
        $this->assertStringNotContainsString('CodeIgniter Version:', $this->getStreamFilterBuffer());
    }

    public function testDiscoveredModernCommandsCanBeOverridden(): void
    {
        $this->injectFixtureLocator([
            AppAboutCommandOverride::class => $this->loadOverrideFixture('AppAboutCommand.php'),
            AppAboutCommand::class         => SUPPORTPATH . 'Commands/Modern/AppAboutCommand.php',
        ]);

        (new Commands())->runCommand('app:about', ['a'], []);

        $this->assertStringContainsString('This is App\Commands\AppAboutCommand', $this->getStreamFilterBuffer());
        $this->assertStringNotContainsString('CodeIgniter Version:', $this->getStreamFilterBuffer());
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

    /**
     * Partially mocks the real locator so `lang()` can still load language
     * files while discovery is fed the given command fixtures.
     *
     * @param array<class-string, string> $classToFile
     */
    private function injectFixtureLocator(array $classToFile): void
    {
        $map = [];

        foreach ($classToFile as $class => $file) {
            $map[] = [$file, $class];
        }

        $locator = $this->getMockBuilder(FileLocator::class)
            ->setConstructorArgs([service('autoloader')])
            ->onlyMethods(['listFiles', 'findQualifiedNameFromPath'])
            ->getMock();
        $locator->method('listFiles')->with('Commands/')->willReturn(array_values($classToFile));
        $locator->method('findQualifiedNameFromPath')->willReturnMap($map);

        Services::injectMock('locator', $locator);
    }
}
