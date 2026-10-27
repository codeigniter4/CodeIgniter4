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
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
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
        return preg_replace('/\e\[[^m]+m/', '', $this->io->getOutput()) ?? '';
    }

    public function testAskReturnsTheAnswer(): void
    {
        $this->assertSame('red', $this->createPrompt(['red'])->ask('Color?'));
        $this->assertSame("Color? : red\n", $this->getUndecoratedOutput());
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
            "Color? [red, blue]: green\nThe \"Color?\" field must be one of: red, blue.\nColor? [red, blue]: blue\n",
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
            "\nHobby:\n  [0]  Games\n  [1]  Sleep\n\n[0, 1]: 1\n",
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
            "\nPick a hobby.\n  [a]  Games\n  [b]  Sleep\n\nHobby [a, b]: b\n",
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
            "\nHobbies:\n  [0]  Games\n  [1]  Sleep\n  [2]  Music\n\nYou can specify multiple values separated by commas.\n[0, 1, 2] : 0,2\n",
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
}
