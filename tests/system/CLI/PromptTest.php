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
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Validation\ValidationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(KeyReader::class)]
#[CoversClass(Prompt::class)]
#[Group('Others')]
final class PromptTest extends CIUnitTestCase
{
    private MockInputOutput $io;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();

        $this->io = new MockInputOutput();
    }

    protected function tearDown(): void
    {
        CLI::reset();

        parent::tearDown();
    }

    /**
     * @param list<string> $inputs
     */
    private function createPrompt(array $inputs, ?ValidationInterface $validation = null): Prompt
    {
        $this->io->setInputs($inputs);

        return new Prompt(new Output($this->io), $validation);
    }

    private function getUndecoratedOutput(): string
    {
        return preg_replace('/\e\[[0-9;]*m/', '', $this->io->getOutput()) ?? '';
    }

    public function testAskReturnsTheAnswer(): void
    {
        $this->assertSame('red', $this->createPrompt(['red'])->ask('Color?'));
        $this->assertSame("Color? : red\n", $this->getUndecoratedOutput());
    }

    public function testPromptsAreSeparatedByABlankLineOnlyWhenSomethingFollows(): void
    {
        $prompt = $this->createPrompt(['red', 'blue']);

        $prompt->ask('Color?');
        $prompt->ask('Other?');

        $this->assertSame("Color? : red\n\nOther? : blue\n", $this->getUndecoratedOutput());
    }

    public function testAskReturnsTheDefaultForAnEmptyAnswer(): void
    {
        $this->assertSame('red', $this->createPrompt([''])->ask('Color?', 'red'));
        $this->assertSame("Color?  [red]: \n", $this->getUndecoratedOutput());
    }

    public function testAskKeepsAZeroAnswer(): void
    {
        $this->assertSame('0', $this->createPrompt(['0'])->ask('Number?', '7'));
    }

    public function testAskWithASingleOptionShowsItAsTheDefault(): void
    {
        $this->assertSame('red', $this->createPrompt([''])->ask('Color?', ['red']));
        $this->assertSame("Color? red: \n", $this->getUndecoratedOutput());
    }

    public function testAskAsksAgainUntilTheAnswerIsAnOption(): void
    {
        $this->assertSame('blue', $this->createPrompt(['green', 'blue'])->ask('Color?', ['red', 'blue']));
        $this->assertSame(
            <<<'EOT'
                Color? [red, blue]: green
                The "Color?" field must be one of: red, blue.
                Color? [red, blue]: blue

                EOT,
            $this->getUndecoratedOutput(),
        );
    }

    public function testAskAsksAgainUntilTheAnswerPassesTheRules(): void
    {
        $this->assertSame('a@example.com', $this->createPrompt(['nope', 'a@example.com'])->ask('Email', null, ['valid_email']));
        $this->assertStringContainsString('The "Email" field must contain a valid email address.', $this->getUndecoratedOutput());
    }

    public function testAskUsesTheInjectedValidation(): void
    {
        $validation = $this->createMock(ValidationInterface::class);
        $validation->method('reset')->willReturnSelf();
        $validation->method('setRules')->willReturnSelf();
        $validation->expects($this->exactly(2))->method('run')->willReturnOnConsecutiveCalls(false, true);
        $validation->method('getError')->willReturn('Not today.');

        $this->assertSame('blue', $this->createPrompt(['red', 'blue'], $validation)->ask('Color?', null, 'required'));
        $this->assertStringContainsString("Not today.\n", $this->getUndecoratedOutput());
    }

    public function testAskByKeyListsTheOptionsAndReturnsTheKey(): void
    {
        $this->assertSame('1', $this->createPrompt(['1'])->askByKey('Hobby:', ['Games', 'Sleep']));
        $this->assertSame(
            <<<'EOT'

                Hobby:
                  [0]  Games
                  [1]  Sleep

                [0, 1]: 1

                EOT,
            $this->getUndecoratedOutput(),
        );
    }

    public function testAskByKeyReturnsTheFirstKeyForAnEmptyAnswer(): void
    {
        $this->assertSame('0', $this->createPrompt([''])->askByKey('Hobby:', ['Games', 'Sleep']));
    }

    public function testAskByKeyTakesTheTextsBeforeTheListAndTheAnswer(): void
    {
        $this->assertSame('b', $this->createPrompt(['b'])->askByKey(['Pick a hobby.', 'Hobby'], ['a' => 'Games', 'b' => 'Sleep']));
        $this->assertSame(
            <<<'EOT'

                Pick a hobby.
                  [a]  Games
                  [b]  Sleep

                Hobby [a, b]: b

                EOT,
            $this->getUndecoratedOutput(),
        );
    }

    public function testAskByKeyRejectsAnEmptyList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No options to select from were provided');

        $this->createPrompt([])->askByKey('Hobby:', []);
    }

    public function testAskByMultipleKeysReturnsTheChosenOptions(): void
    {
        $this->assertSame(
            [0 => 'Games', 2 => 'Music'],
            $this->createPrompt(['0,2'])->askByMultipleKeys('Hobbies:', ['Games', 'Sleep', 'Music']),
        );
        $this->assertSame(
            <<<'EOT'

                Hobbies:
                  [0]  Games
                  [1]  Sleep
                  [2]  Music

                You can specify multiple values separated by commas.
                [0, 1, 2] : 0,2

                EOT,
            $this->getUndecoratedOutput(),
        );
    }

    public function testAskByMultipleKeysReturnsTheFirstOptionForAnEmptyAnswer(): void
    {
        $this->assertSame([0 => 'Games'], $this->createPrompt([''])->askByMultipleKeys('Hobbies:', ['Games', 'Sleep']));
    }

    public function testAskByMultipleKeysWithOneOptionShowsOnlyTheDefault(): void
    {
        $this->assertSame([0 => 'Games'], $this->createPrompt(['0'])->askByMultipleKeys('Hobbies:', ['Games']));
        $this->assertStringContainsString("\n0 : 0\n", $this->getUndecoratedOutput());
    }

    public function testAskByMultipleKeysAsksAgainForAnInvalidOrUnknownKey(): void
    {
        $this->assertSame(
            [1 => 'Sleep'],
            $this->createPrompt(['x', '5', '1'])->askByMultipleKeys('Hobbies:', ['Games', 'Sleep']),
        );
        $this->assertSame(2, substr_count($this->getUndecoratedOutput(), "Please select correctly.\n"));
    }

    public function testAskByMultipleKeysAsksAgainForAMissingKeyBelowTheLast(): void
    {
        $this->assertSame(
            [2 => 'Music'],
            $this->createPrompt(['1', '2'])->askByMultipleKeys('Hobbies:', [0 => 'Games', 2 => 'Music']),
        );
        $this->assertSame(1, substr_count($this->getUndecoratedOutput(), "Please select correctly.\n"));
    }

    public function testAskByMultipleKeysAcceptsAKeyAboveTheLast(): void
    {
        $this->assertSame(
            [5 => 'Sleep'],
            $this->createPrompt(['5'])->askByMultipleKeys('Hobbies:', [0 => 'Games', 5 => 'Sleep', 2 => 'Music']),
        );
        $this->assertStringNotContainsString('Please select correctly.', $this->getUndecoratedOutput());
    }

    public function testAskByMultipleKeysDefaultsToTheFirstKey(): void
    {
        $this->assertSame(
            [2 => 'Music'],
            $this->createPrompt([''])->askByMultipleKeys('Hobbies:', [2 => 'Music', 5 => 'Sleep']),
        );
        $this->assertStringContainsString("[2, 5] : \n", $this->getUndecoratedOutput());
        $this->assertStringNotContainsString('Please select correctly.', $this->getUndecoratedOutput());
    }

    public function testOptionsWrapToTheWidthOfTheOutputTerminal(): void
    {
        $terminal = $this->createStub(Terminal::class);
        $terminal->method('getWidth')->willReturn(30);

        $this->io->setInputs(['']);
        $prompt = new Prompt(new Output($this->io, $terminal));

        $this->assertSame('0', $prompt->askByKey('Pick:', ['alpha beta gamma delta epsilon zeta']));
        $this->assertStringContainsString(
            "  [0]  alpha beta gamma delta\n       epsilon zeta\n",
            $this->getUndecoratedOutput(),
        );
    }

    public function testAskByMultipleKeysRejectsAnEmptyList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No options to select from were provided');

        $this->createPrompt([])->askByMultipleKeys('Hobbies:', []);
    }

    public function testAskStopsWhenTheInputEnds(): void
    {
        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('The input ended before a valid answer was given.');

        $this->createPrompt(['c'])->ask('Pick', ['a', 'b']);
    }

    public function testAskByMultipleKeysStopsWhenTheInputEnds(): void
    {
        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('The input ended before a valid answer was given.');

        $this->createPrompt(['9'])->askByMultipleKeys('Hobbies:', ['Games', 'Sleep']);
    }

    /**
     * @param int|string|null $default
     */
    #[DataProvider('provideSelectFollowsTheKeys')]
    public function testSelectFollowsTheKeys(string $keys, $default, int|string $expected): void
    {
        $options = ['a' => 'Alpha', 'b' => 'Bravo', 'c' => 'Charlie'];

        $this->assertSame($expected, $this->createKeyPrompt($keys)->select('Pick one', $options, $default));
    }

    /**
     * @return iterable<string, array{string, int|string|null, int|string}>
     */
    public static function provideSelectFollowsTheKeys(): iterable
    {
        yield 'enter takes the first option' => ["\n", null, 'a'];

        yield 'enter takes the default' => ["\r", 'b', 'b'];

        yield 'down arrow' => ["\e[B\n", null, 'b'];

        yield 'application-mode down arrow' => ["\eOB\n", null, 'b'];

        yield 'j and k' => ["jjk\n", null, 'b'];

        yield 'up arrow wraps to the last' => ["\e[A\n", null, 'c'];

        yield 'down arrow wraps to the first' => ["\e[B\e[B\e[B\n", null, 'a'];

        yield 'end and home' => ["\e[F\n", null, 'c'];

        yield 'home after end' => ["\e[4~\e[H\n", null, 'a'];

        yield 'the end of the keys confirms' => ["\e[B", null, 'b'];

        yield 'unknown keys change nothing' => ["xyz \n", 'b', 'b'];

        yield 'an alt key is one key' => ["\ej\n", null, 'a'];

        yield 'an unfinished sequence at the end' => ["\e[B\e[", null, 'b'];
    }

    public function testSelectReadsSequencesBeyondSixteenBytes(): void
    {
        $options = array_combine(range(1, 7), ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven']);

        $this->assertSame(7, $this->createKeyPrompt(str_repeat("\e[B", 6) . "\n")->select('Pick one', $options));
    }

    public function testSelectLeavesTheKeysAfterEnterToTheNextPrompt(): void
    {
        $prompt = $this->createKeyPrompt("\n\e[B\n");

        $this->assertSame('a', $prompt->select('Pick one', ['a' => 'Alpha', 'b' => 'Bravo']));
        $this->assertSame('b', $prompt->select('Pick another', ['a' => 'Alpha', 'b' => 'Bravo']));
    }

    public function testSelectReturnsIntegerKeys(): void
    {
        $this->assertSame(5, $this->createKeyPrompt("\e[B\n")->select('Pick one', [2 => 'Two', 5 => 'Five']));
    }

    public function testSelectDrawsTheHighlightedRow(): void
    {
        $this->createKeyPrompt("\n")->select('Pick one', ['a' => 'Alpha', 'b' => 'Bravo'], 'b');

        $this->assertSame(
            "\nPick one\n\e[?25l\r\e[2K↑↓ navigate • ⏎ submit\n\r\e[2K  Alpha\n\r\e[2K❯ Bravo\n\e[?25h",
            $this->getUndecoratedOutput(),
        );
    }

    public function testSelectRedrawsOverThePreviousDrawing(): void
    {
        $this->createKeyPrompt("\e[B\n")->select('Pick one', ['a' => 'Alpha', 'b' => 'Bravo']);

        $this->assertStringContainsString("\e[3A\r\e[2K↑↓ navigate • ⏎ submit\n\r\e[2K  Alpha\n\r\e[2K❯ Bravo\n", $this->getUndecoratedOutput());
    }

    public function testSelectRejectsAnUnknownDefault(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"z" is not one of the options.');

        $this->createKeyPrompt("\n")->select('Pick one', ['a' => 'Alpha'], 'z');
    }

    public function testSelectRejectsAnEmptyList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No options to select from were provided');

        $this->createKeyPrompt("\n")->select('Pick one', []);
    }

    public function testSelectScrollsALongList(): void
    {
        $options = array_combine(range(1, 7), ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven']);

        $this->assertSame(6, $this->createKeyPrompt("\e[B\e[B\e[B\e[B\e[B\n")->select('Pick one', $options));

        $lastDrawing = substr($this->getUndecoratedOutput(), (int) strrpos($this->getUndecoratedOutput(), "\e[6A"));

        $this->assertStringNotContainsString('One', $lastDrawing);
        $this->assertStringContainsString("❯ Six\n", $lastDrawing);
    }

    public function testMultiselectRequiredOnAQuietOutputNeedsADefault(): void
    {
        $output = new Output($this->io);
        $output->setVerbosity(Verbosity::Quiet);

        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('"Pick some" needs at least one option, but none is ticked by default and the output cannot ask.');

        (new Prompt($output))->multiselect('Pick some', ['a' => 'Alpha'], required: true);
    }

    public function testMultiselectRequiredOnAQuietOutputReturnsTheDefaults(): void
    {
        $output = new Output($this->io);
        $output->setVerbosity(Verbosity::Quiet);

        $this->assertSame(['a' => 'Alpha'], (new Prompt($output))->multiselect('Pick some', ['a' => 'Alpha'], ['a'], true));
    }

    public function testSelectScrollsBackUp(): void
    {
        $options = array_combine(range(1, 7), ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven']);

        $this->assertSame(1, $this->createKeyPrompt("\e[H\n")->select('Pick one', $options, 7));

        $lastDrawing = substr($this->getUndecoratedOutput(), (int) strrpos($this->getUndecoratedOutput(), "\e[6A"));

        $this->assertStringContainsString("❯ One\n", $lastDrawing);
        $this->assertStringNotContainsString('Seven', $lastDrawing);
    }

    public function testMenuStopsOnCtrlC(): void
    {
        $navigate = $this->getPrivateMethodInvoker(new Prompt(new Output($this->io)), 'navigate');

        $this->assertNull($navigate(new KeyReader($this->createKeyStream("\e[B\x03\n"), false), 'Pick one', ['a' => 'Alpha', 'b' => 'Bravo'], 0, null, false));
        $this->assertStringEndsWith("\e[?25h", $this->io->getOutput());
    }

    public function testSelectTruncatesLinesWiderThanTheTerminal(): void
    {
        $terminal = $this->createStub(Terminal::class);
        $terminal->method('getWidth')->willReturn(16);
        $terminal->method('getHeight')->willReturn(24);

        $this->createKeyPrompt("\n", $terminal)->select('Pick one', ['a' => 'A very long label indeed']);

        $this->assertStringContainsString("\r\e[2K↑↓ navigate ...\n", $this->getUndecoratedOutput());
        $this->assertStringContainsString("❯ A very lon...\n", $this->getUndecoratedOutput());
    }

    public function testMultiselectTicksWithSpace(): void
    {
        $options = ['a' => 'Alpha', 'b' => 'Bravo', 'c' => 'Charlie'];

        $this->assertSame(
            ['a' => 'Alpha', 'c' => 'Charlie'],
            $this->createKeyPrompt(" \e[B\e[B \n")->multiselect('Pick some', $options),
        );
        $this->assertStringContainsString("↑↓ navigate • space select • a all • ⏎ submit\n", $this->getUndecoratedOutput());
        $this->assertStringContainsString("❯ ◉ Charlie\n", $this->getUndecoratedOutput());
    }

    public function testMultiselectStartsWithTheDefaultsTicked(): void
    {
        $options = ['a' => 'Alpha', 'b' => 'Bravo', 'c' => 'Charlie'];

        $this->assertSame(
            ['a' => 'Alpha', 'c' => 'Charlie'],
            $this->createKeyPrompt("\n")->multiselect('Pick some', $options, ['c', 'a']),
        );
        $this->assertStringContainsString("❯ ◉ Alpha\n\r\e[2K  ◯ Bravo\n\r\e[2K  ◉ Charlie\n", $this->getUndecoratedOutput());
    }

    public function testMultiselectUnticksATickedOption(): void
    {
        $this->assertSame([], $this->createKeyPrompt(" \n")->multiselect('Pick some', ['a' => 'Alpha'], ['a']));
    }

    public function testMultiselectTogglesAll(): void
    {
        $options = ['a' => 'Alpha', 'b' => 'Bravo'];

        $this->assertSame($options, $this->createKeyPrompt("a\n")->multiselect('Pick some', $options, ['a']));
        $this->assertSame([], $this->createKeyPrompt("aa\n")->multiselect('Pick some', $options));
    }

    public function testMultiselectReturnsTheTicksAtTheEndOfTheKeys(): void
    {
        $this->assertSame([2 => 'Two'], $this->createKeyPrompt("\e[B ")->multiselect('Pick some', [1 => 'One', 2 => 'Two']));
    }

    public function testMultiselectAllowsAnEmptySelection(): void
    {
        $this->assertSame([], $this->createKeyPrompt("\n")->multiselect('Pick some', ['a' => 'Alpha']));
    }

    public function testMultiselectRequiredStopsWhenTheKeysEndWithoutATick(): void
    {
        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('The input ended before a valid answer was given.');

        $this->createKeyPrompt("\n")->multiselect('Pick some', ['a' => 'Alpha'], required: true);
    }

    public function testMultiselectClearsTheErrorOnTheNextKey(): void
    {
        $this->assertSame(['a' => 'Alpha'], $this->createKeyPrompt("\n \n")->multiselect('Pick some', ['a' => 'Alpha'], required: true));

        $lastDrawing = substr($this->getUndecoratedOutput(), (int) strrpos($this->getUndecoratedOutput(), "\e[2A"));

        $this->assertStringContainsString("\r\e[2KPlease select at least one option.\n", $this->getUndecoratedOutput());
        $this->assertStringNotContainsString('Please select at least one option.', $lastDrawing);
    }

    public function testMultiselectRejectsAnUnknownDefault(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"z" is not one of the options.');

        $this->createKeyPrompt("\n")->multiselect('Pick some', ['a' => 'Alpha'], ['a', 'z']);
    }

    public function testPromptsReturnTheDefaultsOnAQuietOutput(): void
    {
        $output = new Output($this->io);
        $output->setVerbosity(Verbosity::Quiet);

        $keys   = $this->createKeyStream("\e[B\n");
        $prompt = new Prompt($output, null, $keys);

        $this->assertSame('b', $prompt->select('Pick one', ['a' => 'Alpha', 'b' => 'Bravo'], 'b'));
        $this->assertSame(['a' => 'Alpha'], $prompt->multiselect('Pick some', ['a' => 'Alpha', 'b' => 'Bravo'], ['a']));
        $this->assertSame(0, ftell($keys));
        $this->assertSame('', $this->io->getOutput());
    }

    public function testPromptsReturnTheDefaultsOnANullInputOutput(): void
    {
        $prompt = new Prompt(new Output(new NullInputOutput()), null, $this->createKeyStream("\e[B\n"));

        $this->assertSame('a', $prompt->select('Pick one', ['a' => 'Alpha', 'b' => 'Bravo']));
        $this->assertSame([], $prompt->multiselect('Pick some', ['a' => 'Alpha', 'b' => 'Bravo']));
    }

    public function testSelectFallsBackToANumber(): void
    {
        $options = ['a' => 'Alpha', 'b' => 'Bravo', 'c' => 'Charlie'];

        $this->assertSame('c', $this->createPrompt(['4', 'x', '3'])->select('Pick one', $options, 'b'));
        $this->assertSame(
            <<<'EOT'

                Pick one
                  [1]  Alpha
                  [2]  Bravo
                  [3]  Charlie
                Number [2]: 4
                Please select correctly.
                Number [2]: x
                Please select correctly.
                Number [2]: 3

                EOT,
            $this->getUndecoratedOutput(),
        );
    }

    public function testSelectFallbackTakesTheDefaultForAnEmptyAnswer(): void
    {
        $this->assertSame(5, $this->createPrompt([''])->select('Pick one', [2 => 'Two', 5 => 'Five'], 5));
    }

    public function testMultiselectFallsBackToNumbers(): void
    {
        $options = ['a' => 'Alpha', 'b' => 'Bravo', 'c' => 'Charlie'];

        $this->assertSame(['a' => 'Alpha', 'c' => 'Charlie'], $this->createPrompt(['3, 1'])->multiselect('Pick some', $options));
        $this->assertStringContainsString("Numbers, separated by commas (0 for none) [0]: 3, 1\n", $this->getUndecoratedOutput());
    }

    public function testMultiselectFallbackTakesTheDefaultsForAnEmptyAnswer(): void
    {
        $options = ['a' => 'Alpha', 'b' => 'Bravo', 'c' => 'Charlie'];

        $this->assertSame(['b' => 'Bravo', 'c' => 'Charlie'], $this->createPrompt([''])->multiselect('Pick some', $options, ['c', 'b']));
        $this->assertStringContainsString('(0 for none) [2,3]: ', $this->getUndecoratedOutput());
    }

    public function testMultiselectFallbackSelectsNoneForZero(): void
    {
        $this->assertSame([], $this->createPrompt(['0'])->multiselect('Pick some', ['a' => 'Alpha'], ['a']));
    }

    public function testMultiselectFallbackAsksAgainForAnInvalidAnswer(): void
    {
        $this->assertSame(['a' => 'Alpha'], $this->createPrompt(['4', '1,x', '1'])->multiselect('Pick some', ['a' => 'Alpha', 'b' => 'Bravo']));
        $this->assertSame(2, substr_count($this->getUndecoratedOutput(), "Please select correctly.\n"));
    }

    public function testMultiselectFallbackRequiresANumberWhenRequired(): void
    {
        $this->assertSame(['b' => 'Bravo'], $this->createPrompt(['', '0', '2'])->multiselect('Pick some', ['a' => 'Alpha', 'b' => 'Bravo'], required: true));
        $this->assertStringContainsString("Numbers, separated by commas: \n", $this->getUndecoratedOutput());
        $this->assertSame(2, substr_count($this->getUndecoratedOutput(), "Please select at least one option.\n"));
    }

    public function testSelectFallbackStopsWhenTheInputEnds(): void
    {
        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('The input ended before a valid answer was given.');

        $this->createPrompt(['9'])->select('Pick one', ['a' => 'Alpha']);
    }

    public function testMultiselectFallbackStopsWhenTheInputEndsWithoutAnAnswer(): void
    {
        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('The input ended before a valid answer was given.');

        $this->createPrompt([''])->multiselect('Pick some', ['a' => 'Alpha'], required: true);
    }

    /**
     * @return resource
     */
    private function createKeyStream(string $keys)
    {
        $stream = fopen('php://memory', 'r+b');
        fwrite($stream, $keys);
        rewind($stream);

        return $stream;
    }

    private function createKeyPrompt(string $keys, ?Terminal $terminal = null): Prompt
    {
        return new Prompt(new Output($this->io, $terminal ?? new Terminal()), null, $this->createKeyStream($keys));
    }
}
