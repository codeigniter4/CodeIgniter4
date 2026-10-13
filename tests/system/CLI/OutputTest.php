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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\CLI\RecordingInputOutput;

/**
 * @internal
 */
#[CoversClass(Output::class)]
#[Group('Others')]
final class OutputTest extends CIUnitTestCase
{
    public function testWriteStartsOnAFreshLineOnceAndThenContinues(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->write('first');
        $output->write('second');

        $this->assertSame([['STDOUT', "\nfirst\n"], ['STDOUT', "second\n"]], $io->writes);
    }

    public function testPrintLeavesTheCursorAfterTheText(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->write('first');
        $output->print('second');
        $output->write('third');

        $this->assertSame([['STDOUT', "\nfirst\n"], ['STDOUT', 'second'], ['STDOUT', "\nthird\n"]], $io->writes);
    }

    public function testPrintEndingInANewlineLeavesTheCursorAtTheLineStart(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->print("first\n");
        $output->write('second');
        $output->print("third\r\n");
        $output->write('fourth');

        $this->assertSame([
            ['STDOUT', "first\n"],
            ['STDOUT', "second\n"],
            ['STDOUT', "third\r\n"],
            ['STDOUT', "fourth\n"],
        ], $io->writes);
    }

    public function testErrorWritesToStderrAndSharesTheCursorState(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io, $this->createUncoloredTerminal());

        $output->write('first');
        $output->error('second');
        $output->write('third');

        $this->assertSame([['STDOUT', "\nfirst\n"], ['STDERR', "second\n"], ['STDOUT', "third\n"]], $io->writes);
    }

    public function testErrorOutsideTheCliWritesToStdoutWithoutColor(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        is_cli(false);

        try {
            $output->error('failed');
        } finally {
            is_cli(true);
        }

        $this->assertSame([['STDOUT', "\nfailed\n"]], $io->writes);
    }

    public function testColorFollowsTheSupportOfTheTargetStream(): void
    {
        $io       = new RecordingInputOutput();
        $terminal = $this->createStub(Terminal::class);
        $terminal->method('hasColorSupport')->willReturnCallback(static fn ($stream): bool => $stream === STDERR);
        $output = new Output($io, $terminal);

        $this->assertFalse($output->isDecorated());
        $this->assertSame('plain', $output->color('plain', 'red'));
        $this->assertTrue($output->forStderr()->isDecorated());
        $this->assertSame("\033[0;31mred\033[0m", $output->forStderr()->color('red', 'red'));

        $output->write('out', 'red');
        $output->error('err');

        $this->assertSame([['STDOUT', "\nout\n"], ['STDERR', "\033[1;31merr\033[0m\n"]], $io->writes);
    }

    public function testColorIsOffOutsideTheCli(): void
    {
        $output = new Output(new RecordingInputOutput());

        is_cli(false);

        try {
            $this->assertFalse($output->isDecorated());
            $this->assertSame('plain', $output->color('plain', 'red'));
        } finally {
            is_cli(true);
        }
    }

    public function testPrintColorsTheText(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->print('test', 'red');
        $output->print('test', 'red', 'green');

        $this->assertSame([
            ['STDOUT', "\033[0;31mtest\033[0m"],
            ['STDOUT', "\033[0;31m\033[42mtest\033[0m"],
        ], $io->writes);
    }

    public function testWriteColorsTheText(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->write('test', 'red');
        $output->write('test', 'red', 'green');

        $this->assertSame([
            ['STDOUT', "\n\033[0;31mtest\033[0m\n"],
            ['STDOUT', "\033[0;31m\033[42mtest\033[0m\n"],
        ], $io->writes);
    }

    public function testWriteKeepsAlreadyColoredSegments(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);
        $green  = $output->color('green', 'green');

        $output->write($green . ' red', 'red');
        $output->write('red ' . $green, 'red');
        $output->write($green . ' red ' . $green, 'red');

        $this->assertSame([
            ['STDOUT', "\n\033[0;32mgreen\033[0m\033[0;31m red\033[0m\n"],
            ['STDOUT', "\033[0;31mred \033[0m\033[0;32mgreen\033[0m\n"],
            ['STDOUT', "\033[0;32mgreen\033[0m\033[0;31m red \033[0m\033[0;32mgreen\033[0m\n"],
        ], $io->writes);
    }

    public function testErrorColorsTheTextForStderr(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->error('test');
        $output->error('test', 'purple');
        $output->error('test', 'purple', 'green');

        $this->assertSame([
            ['STDERR', "\n\033[1;31mtest\033[0m\n"],
            ['STDERR', "\033[0;35mtest\033[0m\n"],
            ['STDERR', "\033[0;35m\033[42mtest\033[0m\n"],
        ], $io->writes);
    }

    public function testColorAppliesBackgroundAndUnderline(): void
    {
        $output = new Output(new RecordingInputOutput());

        $this->assertSame("\033[1;37m\033[42m\033[4mtest\033[0m", $output->color('test', 'white', 'green', 'underline'));
        $this->assertSame('', $output->color('', 'white', 'green', 'underline'));
    }

    public function testColorRejectsAnUnknownBackground(): void
    {
        $output = new Output(new RecordingInputOutput());

        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('Invalid "background" color: "Background"');

        $output->color('test', 'white', 'Background');
    }

    public function testBeepRepeats(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->beep();
        $output->beep(4);

        $this->assertSame([['STDOUT', "\x07"], ['STDOUT', "\x07\x07\x07\x07"]], $io->writes);
    }

    public function testBackgroundWithoutForegroundIsApplied(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->write('test', '', 'green');
        $output->print('test', background: 'green');

        $this->assertSame([
            ['STDOUT', "\n\033[42mtest\033[0m\n"],
            ['STDOUT', "\033[42mtest\033[0m"],
        ], $io->writes);
    }

    public function testColorSupportIsNotCachedAcrossTheCliCheck(): void
    {
        $output = new Output(new RecordingInputOutput());

        $this->assertTrue($output->isDecorated());

        is_cli(false);

        try {
            $this->assertFalse($output->isDecorated());
            $this->assertSame('plain', $output->color('plain', 'red'));
        } finally {
            is_cli(true);
        }

        $this->assertTrue($output->isDecorated());
    }

    public function testWriteRawLeavesTheTextAndTheCursorAlone(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->write('first');
        $output->writeRaw('3... ');
        $output->write('second');

        $this->assertSame([['STDOUT', "\nfirst\n"], ['STDOUT', '3... '], ['STDOUT', "second\n"]], $io->writes);
    }

    public function testColorRejectsAnUnknownColor(): void
    {
        $output = new Output(new RecordingInputOutput());

        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('Invalid "foreground" color: "Foreground"');

        $output->color('test', 'Foreground');
    }

    public function testEmptyColorNamesLeaveTheTextAlone(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->write('plain', '', '');
        $output->print('plain', '', '');

        $this->assertSame([['STDOUT', "\nplain\n"], ['STDOUT', 'plain']], $io->writes);
    }

    public function testForStderrTargetsStderrAndKeepsTheCursorState(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io, $this->createUncoloredTerminal());

        $output->write('first');

        $stderr = $output->forStderr();
        $stderr->write('second');
        $stderr->print('third');
        $stderr->beep();

        $output->write('fourth');

        $this->assertSame([
            ['STDOUT', "\nfirst\n"],
            ['STDERR', "second\n"],
            ['STDERR', 'third'],
            ['STDERR', "\x07"],
            ['STDOUT', "fourth\n"],
        ], $io->writes);
    }

    public function testBeepAndClearScreenLeaveTheCursorStateAlone(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->write('first');
        $output->beep(2);
        $output->clearScreen();
        $output->write('second');

        $this->assertSame([
            ['STDOUT', "\nfirst\n"],
            ['STDOUT', "\x07\x07"],
            ['STDOUT', "\033[H\033[2J"],
            ['STDOUT', "second\n"],
        ], $io->writes);
    }

    public function testNewLineWritesEmptyLines(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->newLine(2);

        $this->assertSame([['STDOUT', "\n\n"], ['STDOUT', "\n"]], $io->writes);
    }

    public function testSetCursorAtLineStartControlsTheNextWrite(): void
    {
        $io     = new RecordingInputOutput();
        $output = new Output($io);

        $output->setCursorAtLineStart(true);
        $output->write('first');
        $output->setCursorAtLineStart(false);
        $output->write('second');

        $this->assertSame([['STDOUT', "first\n"], ['STDOUT', "\nsecond\n"]], $io->writes);
    }

    public function testSetInputOutputReplacesTheTransport(): void
    {
        $first  = new RecordingInputOutput();
        $second = new MockInputOutput();
        $output = new Output($first);

        $this->assertSame($first, $output->getInputOutput());

        $output->setInputOutput($second);
        $output->write('hello');

        $this->assertSame($second, $output->getInputOutput());
        $this->assertSame([], $first->writes);
        $this->assertSame("\nhello\n", $second->getOutput());
    }

    public function testDefaultsToAFreshTransportAndTerminal(): void
    {
        $output = new Output();

        $this->assertNotInstanceOf(MockInputOutput::class, $output->getInputOutput());
        $this->assertNotSame($output->getTerminal(), (new Output())->getTerminal());
    }

    private function createUncoloredTerminal(): Terminal
    {
        $terminal = $this->createStub(Terminal::class);
        $terminal->method('hasColorSupport')->willReturn(false);

        return $terminal;
    }
}
