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

use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Exceptions\ArgumentCountMismatchException;
use CodeIgniter\CLI\Exceptions\InvalidArgumentDefinitionException;
use CodeIgniter\CLI\Exceptions\InvalidOptionDefinitionException;
use CodeIgniter\CLI\Exceptions\OptionValueMismatchException;
use CodeIgniter\CLI\Exceptions\UnknownOptionException;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Commands\Help;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\App;
use Config\Services;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use Tests\Support\Commands\Modern\AppAboutCommand;
use Tests\Support\Commands\Modern\InteractFixtureCommand;
use Tests\Support\Commands\Modern\InteractiveStateProbeCommand;
use Tests\Support\Commands\Modern\ParentCallsInteractFixtureCommand;
use Tests\Support\Commands\Modern\TestFixtureCommand;
use Throwable;

/**
 * @internal
 */
#[CoversClass(AbstractCommand::class)]
#[Group('Others')]
final class AbstractCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    #[After]
    #[Before]
    protected function resetAll(): void
    {
        $this->resetServices();

        CLI::reset();

        InteractiveStateProbeCommand::reset();
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    public function testConstructorSetsNeededProperties(): void
    {
        $commands  = new Commands();
        $command   = new Help($commands);
        $attribute = (new ReflectionClass($command))->getAttributes(Command::class)[0]->newInstance();

        $this->assertSame($attribute->name, $command->getName());
        $this->assertSame($attribute->description, $command->getDescription());
        $this->assertSame($attribute->group, $command->getGroup());
        $this->assertSame($commands, $command->getCommandRunner());
        $this->assertSame('help [options] [--] [<command_name>]', $command->getUsages()[0]);
    }

    public function testCommandRequiresCommandAttribute(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/^Command class ".*" is missing the CodeIgniter\\\\CLI\\\\Attributes\\\\Command attribute\.$/');

        new class (new Commands()) extends AbstractCommand {
            protected function execute(array $arguments, array $options): int
            {
                return EXIT_SUCCESS;
            }
        };
    }

    public function testCommandCanGetDefinitions(): void
    {
        $command = new Help(new Commands());

        $this->assertCount(1, $command->getArgumentsDefinition());
        $this->assertCount(3, $command->getOptionsDefinition());
        $this->assertCount(2, $command->getShortcuts());
        $this->assertEmpty($command->getNegations());
    }

    public function testCommandHasDefaultOptions(): void
    {
        $defaultOptions = ['help', 'no-header', 'no-interaction'];

        $this->assertSame($defaultOptions, array_keys((new Help(new Commands()))->getOptionsDefinition()));
    }

    /**
     * @param list<array<string, mixed>> $definitions
     */
    #[DataProvider('provideCollectionLevelArgumentRegistrationIsRejected')]
    public function testCollectionLevelArgumentRegistrationIsRejected(string $message, array $definitions): void
    {
        $this->expectException(InvalidArgumentDefinitionException::class);
        $this->expectExceptionMessage($message);

        $command = new TestFixtureCommand(new Commands());

        foreach ($definitions as $definition) {
            $command->addArgument(new Argument(...$definition));
        }
    }

    /**
     * @return iterable<string, array{string, list<array<string, mixed>>}>
     */
    public static function provideCollectionLevelArgumentRegistrationIsRejected(): iterable
    {
        yield 'duplicate name' => [
            'An argument with the name "command_name" is already defined.',
            [
                ['name' => 'command_name', 'default' => 'file'],
                ['name' => 'command_name', 'default' => 'file2'],
            ],
        ];

        yield 'non-array argument after array argument' => [
            'Argument "second" cannot be defined after array argument "first".',
            [
                ['name' => 'first', 'isArray' => true],
                ['name' => 'second', 'default' => 'x'],
            ],
        ];

        yield 'required argument after optional argument' => [
            'Required argument "second" cannot be defined after optional argument "first".',
            [
                ['name' => 'first', 'default' => 'value'],
                ['name' => 'second', 'required' => true],
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $definitions
     */
    #[DataProvider('provideCollectionLevelOptionRegistrationIsRejected')]
    public function testCollectionLevelOptionRegistrationIsRejected(string $message, array $definitions): void
    {
        $this->expectException(InvalidOptionDefinitionException::class);
        $this->expectExceptionMessage($message);

        $command = new TestFixtureCommand(new Commands());

        foreach ($definitions as $definition) {
            $command->addOption(new Option(...$definition));
        }
    }

    /**
     * @return iterable<string, array{string, list<array<string, mixed>>}>
     */
    public static function provideCollectionLevelOptionRegistrationIsRejected(): iterable
    {
        yield 'duplicate name' => [
            'An option with the name "--test" is already defined.',
            [
                ['name' => 'test'],
                ['name' => 'test'],
            ],
        ];

        yield 'shortcut name already in use' => [
            'Shortcut "-t" cannot be used for option "--test2"; it is already assigned to option "--test1".',
            [
                ['name' => 'test1', 'shortcut' => 't'],
                ['name' => 'test2', 'shortcut' => 't'],
            ],
        ];

        yield 'negatable option already defined as option' => [
            'Negatable option "--test" cannot be defined because its negation "--no-test" already exists as an option.',
            [
                ['name' => 'no-test'],
                ['name' => 'test', 'negatable' => true, 'default' => false],
            ],
        ];

        yield 'option name clashes with existing negation' => [
            'Option "--no-test" clashes with the negation of negatable option "--test".',
            [
                ['name' => 'test', 'negatable' => true, 'default' => false],
                ['name' => 'no-test'],
            ],
        ];
    }

    public function testRenderThrowable(): void
    {
        $command = new AppAboutCommand(new Commands());

        $this->assertSame(EXIT_ERROR, $command->bomb());
        $this->assertStringContainsString('[CodeIgniter\CLI\Exceptions\CLIException]', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Invalid "background" color: "Background".', $this->getStreamFilterBuffer());
    }

    public function testRenderThrowableSwapsNonCliRequestAndRestores(): void
    {
        // Seed the shared request with a non-CLI instance so renderThrowable()
        // exercises the swap-and-restore branch.
        Services::createRequest(config(App::class));
        $incoming = Services::get('request');

        $command = new AppAboutCommand(new Commands());

        $this->assertSame(EXIT_ERROR, $command->bomb());
        $this->assertStringContainsString('Invalid "background" color: "Background".', $this->getStreamFilterBuffer());

        $this->assertSame($incoming, Services::get('request'));
    }

    public function testCheckingOfArgumentsAndOptions(): void
    {
        $command = new Help(new Commands());

        $this->assertTrue($command->hasArgument('command_name'));
        $this->assertFalse($command->hasArgument('lorem'));
        $this->assertTrue($command->hasOption('help'));
        $this->assertTrue($command->hasOption('no-header'));
        $this->assertTrue($command->hasOption('no-interaction'));
        $this->assertFalse($command->hasOption('lorem'));
        $this->assertTrue($command->hasShortcut('h'));
        $this->assertTrue($command->hasShortcut('N'));
        $this->assertFalse($command->hasShortcut('x'));
        $this->assertFalse($command->hasNegation('no-help'));
    }

    public function testCommandCanCallAnotherCommand(): void
    {
        $command = new AppAboutCommand(new Commands());

        $this->assertSame(0, $command->helpMe());
        $this->assertStringContainsString('help [options] [--] [<command_name>]', $this->getStreamFilterBuffer());
    }

    public function testRunCommand(): void
    {
        command('app:about a');

        $this->assertSame(
            sprintf("\nCodeIgniter Version: %s\n", CodeIgniter::CI_VERSION),
            $this->getUndecoratedBuffer(),
        );
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('provideBindingOfArguments')]
    public function testBindingOfArguments(array $arguments, string $key, mixed $value): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run($arguments, []);

        $this->assertSame($value, $command->callGetValidatedArgument($key));
    }

    /**
     * @return iterable<string, array{list<string>, string, mixed}>
     */
    public static function provideBindingOfArguments(): iterable
    {
        yield 'Required argument provided [app:about a]' => [
            ['a'],
            'required',
            'a',
        ];

        yield 'Optional argument omitted [app:about a]' => [
            ['a'],
            'optional',
            'val', // default value
        ];

        yield 'Optional argument provided [app:about a opt]' => [
            ['a', 'opt'],
            'optional',
            'opt',
        ];

        yield 'Array argument omitted [app:about a]' => [
            ['a'],
            'array',
            ['a', 'b'], // default values
        ];

        yield 'Multiple array arguments provided [app:about a b x y]' => [
            ['a', 'b', 'x', 'y'],
            'array',
            ['x', 'y'],
        ];

        yield 'One array argument provided [app:about a b z]' => [
            ['a', 'b', 'z'],
            'array',
            ['z'],
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('provideBindingOfOptions')]
    public function testBindingOfOptions(array $options, string $key, mixed $value): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], $options);

        $this->assertSame($value, $command->callGetValidatedOption($key));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string, mixed}>
     */
    public static function provideBindingOfOptions(): iterable
    {
        yield 'Option requiring value [app:about a --foo=bar]' => [
            ['foo' => 'bar'],
            'foo',
            'bar',
        ];

        yield 'Option shortcut requiring value [app:about a -f bar]' => [
            ['f' => 'bar'],
            'foo',
            'bar',
        ];

        yield 'Option optionally accepting value [app:about a --bar]' => [
            ['bar' => null],
            'bar',
            null,
        ];

        yield 'Option shortcut optionally accepting value [app:about a -a 3]' => [
            ['a' => '3'],
            'bar',
            '3',
        ];

        yield 'Option allowing multiple values [app:about a --baz=val1]' => [
            ['baz' => ['val1']],
            'baz',
            ['val1'],
        ];

        yield 'Option allowing multiple values [app:about a --baz=val1] (as string)' => [
            ['baz' => 'val1'],
            'baz',
            ['val1'],
        ];

        yield 'Option allowing multiple values [app:about a --baz=val1 --baz=val2]' => [
            ['baz' => ['val1', 'val2']],
            'baz',
            ['val1', 'val2'],
        ];

        yield 'Option shortcut allowing multiple values [app:about a -b 1 -b 2]' => [
            ['b' => ['1', '2']],
            'baz',
            ['1', '2'],
        ];

        yield 'Option and shortcut allowing multiple values [app:about a -b 1 --baz 2]' => [
            ['b' => '1', 'baz' => '2'],
            'baz',
            ['2', '1'], // long names of array options are recognised first
        ];

        yield 'Array option with shortcut passed multiple times after long name [app:about a --baz 1 -b 2 -b 3]' => [
            ['baz' => '1', 'b' => ['2', '3']],
            'baz',
            ['1', '2', '3'], // leftover shortcut values are flattened, not nested
        ];

        yield 'Negatable option provided [app:about a --quux]' => [
            ['quux' => null],
            'quux',
            true,
        ];

        yield 'Negated option provided [app:about a --no-quux]' => [
            ['no-quux' => null],
            'quux',
            false,
        ];
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('provideValidationNoArgumentsExpected')]
    public function testValidationNoArgumentsExpected(string $message, array $arguments): void
    {
        $command = new TestFixtureCommand(new Commands());

        $this->expectException(ArgumentCountMismatchException::class);
        $this->expectExceptionMessage($message);

        $command->run($arguments, []);
    }

    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function provideValidationNoArgumentsExpected(): iterable
    {
        yield 'one extra argument [test:fixture a]' => [
            'No arguments expected for "test:fixture" command. Received: "a".',
            ['a'],
        ];

        yield 'two extra arguments [test:fixture a b]' => [
            'No arguments expected for "test:fixture" command. Received: "a", "b".',
            ['a', 'b'],
        ];
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('provideValidationTooManyArguments')]
    public function testValidationTooManyArguments(string $message, array $arguments): void
    {
        $command = (new TestFixtureCommand(new Commands()))
            ->addArgument(new Argument(name: 'first', default: 'a'))
            ->addArgument(new Argument(name: 'second', default: 'b'));

        $this->expectException(ArgumentCountMismatchException::class);
        $this->expectExceptionMessage($message);

        $command->run($arguments, []);
    }

    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function provideValidationTooManyArguments(): iterable
    {
        yield 'one extra argument [test:fixture a b c]' => [
            'One unexpected argument was provided to "test:fixture" command: "c".',
            ['a', 'b', 'c'],
        ];

        yield 'two extra arguments [test:fixture a b c d]' => [
            'Multiple unexpected arguments were provided to "test:fixture" command: "c", "d".',
            ['a', 'b', 'c', 'd'],
        ];
    }

    public function testValidationWithMissingRequiredArgument(): void
    {
        $command = new TestFixtureCommand(new Commands());
        $command->addArgument(new Argument(name: 'required_arg', required: true));

        $this->expectException(ArgumentCountMismatchException::class);
        $this->expectExceptionMessage('Command "test:fixture" is missing the following required argument: required_arg.');

        $command->run([], []);
    }

    public function testValidationWithMissingMultipleRequiredArguments(): void
    {
        $command = new TestFixtureCommand(new Commands());
        $command->addArgument(new Argument(name: 'first_required', required: true));
        $command->addArgument(new Argument(name: 'second_required', required: true));

        $this->expectException(ArgumentCountMismatchException::class);
        $this->expectExceptionMessage('Command "test:fixture" is missing the following required arguments: first_required, second_required.');

        $command->run([], []);
    }

    /**
     * @param class-string<Throwable> $exception
     * @param array<string, mixed>    $options
     */
    #[DataProvider('provideValidationOfOptions')]
    public function testValidationOfOptions(string $exception, string $message, array $options): void
    {
        $command = new AppAboutCommand(new Commands());

        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        $command->run(['a'], $options);
    }

    /**
     * @return iterable<string, array{class-string<Throwable>, string, array<string, mixed>}>
     */
    public static function provideValidationOfOptions(): iterable
    {
        yield 'flag option passed multiple times [app:about a --help --help]' => [
            LogicException::class,
            'Option "--help" is passed multiple times.',
            ['help' => [null, null]],
        ];

        yield 'flag option shortcut passed multiple times [app:about a -h -h]' => [
            LogicException::class,
            'Option "--help" is passed multiple times.',
            ['h' => [null, null]],
        ];

        yield 'flag option and its shortcut passed [app:about a --help -h]' => [
            LogicException::class,
            'Option "--help" is passed multiple times.',
            ['help' => null, 'h' => null],
        ];

        yield 'flag option passed with value [app:about a --help=value]' => [
            OptionValueMismatchException::class,
            'Option "--help" does not accept a value.',
            ['help' => 'value'],
        ];

        yield 'option requiring value passed without value [app:about a --foo]' => [
            OptionValueMismatchException::class,
            'Option "--foo" requires a value to be provided.',
            ['foo' => null],
        ];

        yield 'array option requiring value passed without value [app:about a --baz]' => [
            OptionValueMismatchException::class,
            'Option "--baz" requires a value to be provided.',
            ['baz' => null],
        ];

        yield 'array option requiring value passed without value multiple times [app:about a --baz --baz]' => [
            OptionValueMismatchException::class,
            'Option "--baz" requires a value to be provided.',
            ['baz' => [null, null]],
        ];

        yield 'array option requiring value mixing value and no-value [app:about a --baz=v1 --baz]' => [
            OptionValueMismatchException::class,
            'Option "--baz" requires a value to be provided.',
            ['baz' => ['v1', null]],
        ];

        yield 'option not accepting value passed with value [app:about a --no-header=value]' => [
            OptionValueMismatchException::class,
            'Option "--no-header" does not accept a value.',
            ['no-header' => 'value'],
        ];

        yield 'negatable option passed with value [app:about a --quux=value]' => [
            OptionValueMismatchException::class,
            'Negatable option "--quux" does not accept a value.',
            ['quux' => 'value'],
        ];

        yield 'negation of negatable option passed with value [app:about a --no-quux=value]' => [
            OptionValueMismatchException::class,
            'Negated option "--no-quux" does not accept a value.',
            ['no-quux' => 'value'],
        ];

        yield 'non-array option accepting value passed multiple times [app:about a --foo=a --foo=b]' => [
            OptionValueMismatchException::class,
            'Option "--foo" does not accept an array value.',
            ['foo' => ['a', 'b']],
        ];

        yield 'non-array option accepting value passed multiple times via shortcut [app:about a -f c -f d]' => [
            OptionValueMismatchException::class,
            'Option "--foo" does not accept an array value.',
            ['f' => ['c', 'd']],
        ];

        yield 'negatable option passed multiple times [app:about a --quux --quux]' => [
            OptionValueMismatchException::class,
            'Negatable option "--quux" is passed multiple times.',
            ['quux' => [null, null]],
        ];

        yield 'negatable option passed multiple times some with value [app:about a --quux --quux=b]' => [
            OptionValueMismatchException::class,
            'Negatable option "--quux" is passed multiple times.',
            ['quux' => [null, 'b']],
        ];

        yield 'negatable option passed multiple times all with values [app:about a --quux=b --quux=c]' => [
            OptionValueMismatchException::class,
            'Negatable option "--quux" is passed multiple times.',
            ['quux' => ['b', 'c']],
        ];

        yield 'negation of negatable option passed multiple times [app:about a --no-quux --no-quux]' => [
            OptionValueMismatchException::class,
            'Negated option "--no-quux" is passed multiple times.',
            ['no-quux' => [null, null]],
        ];

        yield 'negation of negatable option passed multiple times some with value [app:about a --no-quux --no-quux=d]' => [
            OptionValueMismatchException::class,
            'Negated option "--no-quux" is passed multiple times.',
            ['no-quux' => [null, 'd']],
        ];

        yield 'negation of negatable option passed multiple times all with values [app:about a --no-quux=e --no-quux=f]' => [
            OptionValueMismatchException::class,
            'Negated option "--no-quux" is passed multiple times.',
            ['no-quux' => ['e', 'f']],
        ];

        yield 'negatable option passed with its negation [app:about a --quux --no-quux]' => [
            LogicException::class,
            'Option "--quux" and its negation "--no-quux" cannot be used together.',
            ['quux' => null, 'no-quux' => null],
        ];

        yield 'negatable option passed with its negation carrying a value [app:about a --quux --no-quux=text]' => [
            LogicException::class,
            'Option "--quux" and its negation "--no-quux" cannot be used together.',
            ['quux' => null, 'no-quux' => 'text'],
        ];

        yield 'negatable option passed with its negation multiple times [app:about a --quux --no-quux --no-quux]' => [
            LogicException::class,
            'Option "--quux" and its negation "--no-quux" cannot be used together.',
            ['quux' => null, 'no-quux' => [null, null]],
        ];

        yield 'negatable option passed multiple times with its negation [app:about a --quux --quux --no-quux]' => [
            LogicException::class,
            'Option "--quux" and its negation "--no-quux" cannot be used together.',
            ['quux' => [null, null], 'no-quux' => null],
        ];

        yield 'unknown option passed [app:about a --unknown]' => [
            UnknownOptionException::class,
            'The following option is unknown in the "app:about" command: --unknown.',
            ['unknown' => 'value'],
        ];

        yield 'multiple unknown options passed [app:about a --unknown1 --unknown2]' => [
            UnknownOptionException::class,
            'The following options are unknown in the "app:about" command: --unknown1, --unknown2.',
            ['unknown1' => 'value', 'unknown2' => 'value'],
        ];
    }

    public function testInteractMutationsCarryThroughToExecute(): void
    {
        $command = new InteractFixtureCommand(new Commands());
        $command->run([], []);

        $this->assertTrue($command->isInteractive());
        $this->assertSame(['name' => 'from-interact'], $command->executedArguments);
        $this->assertTrue($command->executedOptions['force']);
    }

    public function testInteractIsSkippedWhenNoInteractionFlagIsPassed(): void
    {
        $command = new InteractFixtureCommand(new Commands());
        $command->run([], ['no-interaction' => null]);

        $this->assertFalse($command->isInteractive());
        $this->assertSame(['name' => 'anonymous'], $command->executedArguments);
        $this->assertFalse($command->executedOptions['force']);
    }

    public function testInteractIsSkippedWhenShortcutFlagIsPassed(): void
    {
        $command = new InteractFixtureCommand(new Commands());
        $command->run([], ['N' => null]);

        $this->assertFalse($command->isInteractive());
        $this->assertSame(['name' => 'anonymous'], $command->executedArguments);
        $this->assertFalse($command->executedOptions['force']);
    }

    public function testInteractIsSkippedWhenSetInteractiveFalseIsCalled(): void
    {
        $command = new InteractFixtureCommand(new Commands());
        $command->setInteractive(false);
        $this->assertFalse($command->isInteractive());

        $command->run([], []);

        $this->assertFalse($command->isInteractive());
        $this->assertSame(['name' => 'anonymous'], $command->executedArguments);
        $this->assertFalse($command->executedOptions['force']);
    }

    public function testSetInteractiveTrueOverridesNoInteractionFlag(): void
    {
        // Explicit caller intent wins over the CLI flag.
        $command = new InteractFixtureCommand(new Commands());
        $command->setInteractive(true);
        $command->run([], ['no-interaction' => null]);

        $this->assertTrue($command->isInteractive());
        $this->assertSame(['name' => 'from-interact'], $command->executedArguments);
        $this->assertTrue($command->executedOptions['force']);
    }

    public function testNoInteractionFlagDoesNotLeakAcrossRuns(): void
    {
        $command = new InteractFixtureCommand(new Commands());

        $command->run([], ['no-interaction' => null]);
        $this->assertFalse($command->isInteractive());
        $this->assertSame(['name' => 'anonymous'], $command->executedArguments);
        $this->assertFalse($command->executedOptions['force']);

        $command->run([], []);
        $this->assertTrue($command->isInteractive());
        $this->assertSame(['name' => 'from-interact'], $command->executedArguments);
        $this->assertTrue($command->executedOptions['force']);
    }

    public function testSetInteractiveCallPersistsAcrossRuns(): void
    {
        $command = new InteractFixtureCommand(new Commands());
        $command->setInteractive(false);
        $this->assertFalse($command->isInteractive());

        $command->run([], []);
        $this->assertFalse($command->isInteractive());
        $this->assertSame(['name' => 'anonymous'], $command->executedArguments);

        $command->run([], []);
        $this->assertFalse($command->isInteractive());
        $this->assertSame(['name' => 'anonymous'], $command->executedArguments);
    }

    public function testIsInteractiveReflectsExplicitState(): void
    {
        $command = new InteractFixtureCommand(new Commands());

        // Default: in the testing env, `CLI::streamSupports('stream_isatty', STDIN)`
        // resolves to `function_exists('stream_isatty')`, which is true on PHP 8.1+.
        $this->assertTrue($command->isInteractive());

        $command->setInteractive(false);
        $this->assertFalse($command->isInteractive());

        $command->setInteractive(true);
        $this->assertTrue($command->isInteractive());
    }

    public function testNoInteractionCascadesToSubCommandsViaCall(): void
    {
        $command = new ParentCallsInteractFixtureCommand(new Commands());

        $exitCode = $command->run([], ['no-interaction' => null]);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertFalse($command->isInteractive());
        $this->assertFalse(InteractiveStateProbeCommand::$interactCalled);
        $this->assertFalse(InteractiveStateProbeCommand::$observedInteractive);
    }

    public function testSubCommandStaysInteractiveWhenParentIsInteractive(): void
    {
        $command = new ParentCallsInteractFixtureCommand(new Commands());

        $exitCode = $command->run([], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertTrue($command->isInteractive());
        $this->assertTrue(InteractiveStateProbeCommand::$interactCalled);
        $this->assertTrue(InteractiveStateProbeCommand::$observedInteractive);
    }

    public function testCallAllowsSubCommandInteractiveEvenWhenParentIsNonInteractive(): void
    {
        $command = new ParentCallsInteractFixtureCommand(new Commands());
        $command->setInteractive(false);
        $command->childNoInteractionOverride = false;

        $exitCode = $command->run([], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertFalse($command->isInteractive());
        $this->assertTrue(InteractiveStateProbeCommand::$interactCalled);
        $this->assertTrue(InteractiveStateProbeCommand::$observedInteractive);
    }

    public function testCallForcesSubCommandNonInteractiveEvenWhenParentIsInteractive(): void
    {
        $command = new ParentCallsInteractFixtureCommand(new Commands());

        $command->childNoInteractionOverride = true;

        $exitCode = $command->run([], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertTrue($command->isInteractive());
        $this->assertFalse(InteractiveStateProbeCommand::$interactCalled);
        $this->assertFalse(InteractiveStateProbeCommand::$observedInteractive);
    }

    /**
     * Caller passes --no-interaction in the sub-command's options, but also
     * sets noInteractionOverride to false: the explicit parameter wins and
     * the inherited flag is stripped under both its long name and its shortcut.
     */
    public function testCallStripsInheritedNoInteractionWhenCallerAllowsInteraction(): void
    {
        $command = new ParentCallsInteractFixtureCommand(new Commands());

        $command->childNoInteractionOverride = false;

        $command->childOptions = ['no-interaction' => null, 'N' => null];

        $exitCode = $command->run([], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertTrue($command->isInteractive());
        $this->assertTrue(InteractiveStateProbeCommand::$interactCalled);
        $this->assertTrue(InteractiveStateProbeCommand::$observedInteractive);
    }

    /**
     * When $noInteractionOverride is true and the caller already supplied the flag,
     * the resolver must not touch the caller's entry. The child still sees a
     * non-interactive state.
     */
    public function testCallPreservesCallerFlagWhenForcingNonInteractive(): void
    {
        $command = new ParentCallsInteractFixtureCommand(new Commands());

        $command->childNoInteractionOverride = true;

        $command->childOptions = ['no-interaction' => null];

        $exitCode = $command->run([], []);

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertTrue($command->isInteractive());
        $this->assertFalse(InteractiveStateProbeCommand::$interactCalled);
        $this->assertFalse(InteractiveStateProbeCommand::$observedInteractive);
    }

    /**
     * @param array<string, list<string|null>|string|null> $options
     */
    #[DataProvider('provideHasUnboundOptionResolvesAlias')]
    public function testHasUnboundOptionResolvesAlias(string $name, array $options, bool $expected): void
    {
        $command = new AppAboutCommand(new Commands());

        $this->assertSame($expected, $command->callHasUnboundOption($name, $options));
    }

    /**
     * @return iterable<string, array{string, array<string, list<string|null>|string|null>, bool}>
     */
    public static function provideHasUnboundOptionResolvesAlias(): iterable
    {
        yield 'long name' => ['foo', ['foo' => 'bar'], true];

        yield 'shortcut' => ['foo', ['f' => 'bar'], true];

        yield 'negation' => ['quux', ['no-quux' => null], true];

        yield 'not provided' => ['foo', [], false];
    }

    public function testHasUnboundOptionThrowsForUndeclaredOption(): void
    {
        $command = new AppAboutCommand(new Commands());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Option "undeclared" is not defined on this command.');

        $command->callHasUnboundOption('undeclared', []);
    }

    /**
     * @param array<string, list<string|null>|string|null> $options
     * @param list<string|null>|string|null                $expected
     */
    #[DataProvider('provideGetUnboundOptionResolvesAlias')]
    public function testGetUnboundOptionResolvesAlias(string $name, array $options, array|string|null $expected): void
    {
        $command = new AppAboutCommand(new Commands());

        $this->assertSame($expected, $command->callGetUnboundOption($name, $options));
    }

    /**
     * @return iterable<string, array{string, array<string, list<string|null>|string|null>, list<string|null>|string|null}>
     */
    public static function provideGetUnboundOptionResolvesAlias(): iterable
    {
        yield 'long name' => ['foo', ['foo' => 'bar'], 'bar'];

        yield 'shortcut' => ['foo', ['f' => 'bar'], 'bar'];

        yield 'negation' => ['quux', ['no-quux' => null], null];

        yield 'not provided' => ['foo', [], null];
    }

    public function testGetUnboundOptionThrowsForUndeclaredOption(): void
    {
        $command = new AppAboutCommand(new Commands());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Option "undeclared" is not defined on this command.');

        $command->callGetUnboundOption('undeclared', []);
    }

    public function testUnboundOptionHelpersFallBackToInstanceStateAfterRun(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], ['f' => 'shortcut-value']);

        // Options array passed to run() is the raw (unbound) input. After run()
        // completes, $this->unboundOptions holds that snapshot. Calling the
        // helpers with $options = null should read from that state.
        $this->assertTrue($command->callHasUnboundOption('foo'));
        $this->assertSame('shortcut-value', $command->callGetUnboundOption('foo'));
    }

    public function testGetUnboundArgumentsReturnsRawArgumentList(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a', 'b', 'extra'], []);

        $this->assertSame(['a', 'b', 'extra'], $command->callGetUnboundArguments());
    }

    public function testGetUnboundArgumentByIndex(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a', 'b'], []);

        $this->assertSame('a', $command->callGetUnboundArgument(0));
        $this->assertSame('b', $command->callGetUnboundArgument(1));
    }

    public function testGetUnboundArgumentThrowsForMissingIndex(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], []);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unbound argument at index "5" does not exist.');

        $command->callGetUnboundArgument(5);
    }

    public function testGetUnboundOptionsReturnsRawOptionMap(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], ['f' => 'shortcut', 'bar' => 'longname']);

        $this->assertSame(
            ['f' => 'shortcut', 'bar' => 'longname'],
            $command->callGetUnboundOptions(),
        );
    }

    public function testGetValidatedArgumentsReflectsDefaultsAfterBinding(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], []);

        $this->assertSame(
            ['required' => 'a', 'optional' => 'val', 'array' => ['a', 'b']],
            $command->callGetValidatedArguments(),
        );
    }

    public function testGetValidatedArgumentByName(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['hello', 'world'], []);

        $this->assertSame('hello', $command->callGetValidatedArgument('required'));
        $this->assertSame('world', $command->callGetValidatedArgument('optional'));
    }

    public function testGetValidatedArgumentThrowsForUnknownName(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], []);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Validated argument with name "missing" does not exist.');

        $command->callGetValidatedArgument('missing');
    }

    public function testGetValidatedOptionsReflectsDefaultsAfterBinding(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], ['foo' => 'provided']);

        $this->assertSame(
            [
                'foo'            => 'provided',
                'bar'            => null,
                'baz'            => ['a'],
                'quux'           => false,
                'help'           => false,
                'no-header'      => false,
                'no-interaction' => false,
            ],
            $command->callGetValidatedOptions(),
        );
    }

    public function testGetValidatedOptionByName(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], ['foo' => 'provided']);

        $this->assertSame('provided', $command->callGetValidatedOption('foo'));
        $this->assertFalse($command->callGetValidatedOption('help'));
    }

    public function testGetValidatedOptionThrowsForUnknownName(): void
    {
        $command = new AppAboutCommand(new Commands());
        $command->run(['a'], []);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Validated option with name "missing" does not exist.');

        $command->callGetValidatedOption('missing');
    }
}
