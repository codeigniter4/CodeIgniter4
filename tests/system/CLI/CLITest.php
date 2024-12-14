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

use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\PhpStreamWrapper;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use ReflectionProperty;

/**
 * @internal
 */
#[Group('Others')]
final class CLITest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public function testNew(): void
    {
        $actual = new CLI();

        $this->assertInstanceOf(CLI::class, $actual);
    }

    public function testBeep(): void
    {
        $this->expectOutputString("\x07");

        CLI::beep();
    }

    public function testBeep4(): void
    {
        $this->expectOutputString("\x07\x07\x07\x07");

        CLI::beep(4);
    }

    /**
     * This test waits for 2 seconds before last assertion so this
     * is naturally a "slow" test on the perspective of the default limit.
     *
     * @timeLimit 2.5
     */
    public function testWait(): void
    {
        $time = time();
        CLI::wait(1, true);
        $this->assertCloseEnough(1, time() - $time);

        $time = time();
        CLI::wait(1);
        $this->assertCloseEnough(1, time() - $time);
    }

    public function testWaitZero(): void
    {
        PhpStreamWrapper::register();
        PhpStreamWrapper::setContent(' ');

        // test the press any key to continue...
        $time = time();
        CLI::wait(0);

        PhpStreamWrapper::restore();

        $this->assertCloseEnough(0, time() - $time);
    }

    public function testPrompt(): void
    {
        PhpStreamWrapper::register();

        $expected = 'red';
        PhpStreamWrapper::setContent($expected);

        $output = CLI::prompt('What is your favorite color?');

        PhpStreamWrapper::restore();

        $this->assertSame($expected, $output);
    }

    public function testPromptInputNothing(): void
    {
        PhpStreamWrapper::register();

        $input = '';
        PhpStreamWrapper::setContent($input);

        $output = CLI::prompt('What is your favorite color?', 'red');

        PhpStreamWrapper::restore();

        $this->assertSame('red', $output);
    }

    public function testPromptInputZero(): void
    {
        PhpStreamWrapper::register();

        $input = '0';
        PhpStreamWrapper::setContent($input);

        $output = CLI::prompt('What is your favorite number?', '7');

        PhpStreamWrapper::restore();

        $this->assertSame('0', $output);
    }

    public function testPromptByKey(): void
    {
        PhpStreamWrapper::register();

        $input = '1';
        PhpStreamWrapper::setContent($input);

        $options = ['Playing game', 'Sleep', 'Badminton'];
        $output  = CLI::promptByKey('Select your hobbies:', $options);

        PhpStreamWrapper::restore();

        $this->assertSame($input, $output);
    }

    public function testPromptByKeyInputNothing(): void
    {
        PhpStreamWrapper::register();

        $input = ''; // This is when you press the Enter key.
        PhpStreamWrapper::setContent($input);

        $options = ['Playing game', 'Sleep', 'Badminton'];
        $output  = CLI::promptByKey('Select your hobbies:', $options);

        PhpStreamWrapper::restore();

        $expected = '0';
        $this->assertSame($expected, $output);
    }

    public function testPromptByKeyInputZero(): void
    {
        PhpStreamWrapper::register();

        $input = '0';
        PhpStreamWrapper::setContent($input);

        $options = ['Playing game', 'Sleep', 'Badminton'];
        $output  = CLI::promptByKey('Select your hobbies:', $options);

        PhpStreamWrapper::restore();

        $this->assertSame($input, $output);
    }

    public function testPromptByMultipleKeys(): void
    {
        PhpStreamWrapper::register();

        $input = '0,1';
        PhpStreamWrapper::setContent($input);

        $options = ['Playing game', 'Sleep', 'Badminton'];
        $output  = CLI::promptByMultipleKeys('Select your hobbies:', $options);

        PhpStreamWrapper::restore();

        $expected = [
            0 => 'Playing game',
            1 => 'Sleep',
        ];
        $this->assertSame($expected, $output);
    }

    public function testPromptByMultipleKeysInputNothing(): void
    {
        PhpStreamWrapper::register();

        $input = ''; // This is when you press the Enter key.
        PhpStreamWrapper::setContent($input);

        $options = ['Playing game', 'Sleep', 'Badminton'];
        $output  = CLI::promptByMultipleKeys('Select your hobbies:', $options);

        PhpStreamWrapper::restore();

        $expected = [
            0 => 'Playing game',
        ];
        $this->assertSame($expected, $output);
    }

    public function testPromptByMultipleKeysInputZero(): void
    {
        PhpStreamWrapper::register();

        $input = '0';
        PhpStreamWrapper::setContent($input);

        $options = ['Playing game', 'Sleep', 'Badminton'];
        $output  = CLI::promptByMultipleKeys('Select your hobbies:', $options);

        PhpStreamWrapper::restore();

        $expected = [
            0 => 'Playing game',
        ];
        $this->assertSame($expected, $output);
    }

    public function testNewLine(): void
    {
        $this->expectOutputString('');

        CLI::newLine();
    }

    public function testColorExceptionForeground(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid "foreground" color: "Foreground"');

        CLI::color('test', 'Foreground');
    }

    public function testColorExceptionBackground(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid "background" color: "Background"');

        CLI::color('test', 'white', 'Background');
    }

    public function testColorSupportOnNoColor(): void
    {
        $nocolor = getenv('NO_COLOR');
        putenv('NO_COLOR=1');
        CLI::init(); // force re-check on env

        $this->assertSame('test', CLI::color('test', 'white', 'green'));

        putenv($nocolor ? "NO_COLOR={$nocolor}" : 'NO_COLOR');
    }

    public function testColorSupportOnHyperTerminals(): void
    {
        $termProgram = getenv('TERM_PROGRAM');
        putenv('TERM_PROGRAM=Hyper');
        CLI::init(); // force re-check on env

        $this->assertSame("\033[1;37m\033[42m\033[4mtest\033[0m", CLI::color('test', 'white', 'green', 'underline'));

        putenv($termProgram ? "TERM_PROGRAM={$termProgram}" : 'TERM_PROGRAM');
    }

    public function testStreamSupports(): void
    {
        $this->assertTrue(CLI::streamSupports('stream_isatty', STDOUT));
        $this->assertIsBool(CLI::streamSupports('sapi_windows_vt100_support', STDOUT));
    }

    public function testColor(): void
    {
        // After the tests on NO_COLOR and TERM_PROGRAM above,
        // the $isColored variable is rigged. So we reset this.
        CLI::init();

        $this->assertSame(
            "\033[1;37m\033[42m\033[4mtest\033[0m",
            CLI::color('test', 'white', 'green', 'underline')
        );
    }

    public function testColorEmtpyString(): void
    {
        $this->assertSame(
            '',
            CLI::color('', 'white', 'green', 'underline')
        );
    }

    public function testPrint(): void
    {
        CLI::print('test');

        $expected = 'test';
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testPrintForeground(): void
    {
        CLI::print('test', 'red');

        $expected = "\033[0;31mtest\033[0m";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testPrintBackground(): void
    {
        CLI::print('test', 'red', 'green');

        $expected = "\033[0;31m\033[42mtest\033[0m";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testWrite(): void
    {
        CLI::write('test');

        $expected = "\ntest\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testWriteForeground(): void
    {
        CLI::write('test', 'red');

        $expected = "\033[0;31mtest\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testWriteForegroundWithColorBefore(): void
    {
        CLI::write(CLI::color('green', 'green') . ' red', 'red');

        $expected = "\033[0;32mgreen\033[0m\033[0;31m red\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testWriteForegroundWithColorAfter(): void
    {
        CLI::write('red ' . CLI::color('green', 'green'), 'red');

        $expected = "\033[0;31mred \033[0m\033[0;32mgreen\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5892
     */
    public function testWriteForegroundWithColorTwice(): void
    {
        CLI::write(
            CLI::color('green', 'green') . ' red ' . CLI::color('green', 'green'),
            'red'
        );

        $expected = "\033[0;32mgreen\033[0m\033[0;31m red \033[0m\033[0;32mgreen\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testWriteBackground(): void
    {
        CLI::write('test', 'red', 'green');

        $expected = "\033[0;31m\033[42mtest\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testError(): void
    {
        CLI::error('test');

        // red expected cuz stderr
        $expected = "\033[1;31mtest\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testErrorForeground(): void
    {
        CLI::error('test', 'purple');

        $expected = "\033[0;35mtest\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testErrorBackground(): void
    {
        CLI::error('test', 'purple', 'green');

        $expected = "\033[0;35m\033[42mtest\033[0m\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testShowProgress(): void
    {
        CLI::write('first.');
        CLI::showProgress(1, 20);
        CLI::showProgress(10, 20);
        CLI::showProgress(20, 20);
        CLI::write('second.');
        CLI::showProgress(1, 20);
        CLI::showProgress(10, 20);
        CLI::showProgress(20, 20);
        CLI::write('third.');
        CLI::showProgress(1, 20);

        $expected = "first.\n" .
                    "[\033[32m#.........\033[0m]   5% Complete\n" .
                    "\033[1A[\033[32m#####.....\033[0m]  50% Complete\n" .
                    "\033[1A[\033[32m##########\033[0m] 100% Complete\n" .
                    "second.\n" .
                    "[\033[32m#.........\033[0m]   5% Complete\n" .
                    "\033[1A[\033[32m#####.....\033[0m]  50% Complete\n" .
                    "\033[1A[\033[32m##########\033[0m] 100% Complete\n" .
                    "third.\n" .
                    "[\033[32m#.........\033[0m]   5% Complete\n";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testShowProgressWithoutBar(): void
    {
        CLI::write('first.');
        CLI::showProgress(false, 20);
        CLI::showProgress(false, 20);
        CLI::showProgress(false, 20);

        $expected = "first.\n\007\007\007";
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testWrap(): void
    {
        $this->assertSame('', CLI::wrap(''));
        $this->assertSame(
            "1234\n 5678\n 90\n abc\n de\n fghij\n 0987654321",
            CLI::wrap("1234 5678 90\nabc de fghij\n0987654321", 5, 1)
        );
        $this->assertSame(
            "1234 5678 90\n  abc de fghij\n  0987654321",
            CLI::wrap("1234 5678 90\nabc de fghij\n0987654321", 999, 2)
        );
        $this->assertSame(
            "1234 5678 90\nabc de fghij\n0987654321",
            CLI::wrap("1234 5678 90\nabc de fghij\n0987654321")
        );
    }

    public function testParseCommand(): void
    {
        $_SERVER['argv'] = [
            'ignored',
            'b',
            'c',
        ];
        $_SERVER['argc'] = 3;
        CLI::init();

        $this->assertNull(CLI::getSegment(3));
        $this->assertSame('b', CLI::getSegment(1));
        $this->assertSame('c', CLI::getSegment(2));
        $this->assertSame('b/c', CLI::getURI());
        $this->assertSame([], CLI::getOptions());
        $this->assertEmpty(CLI::getOptionString());
        $this->assertSame(['b', 'c'], CLI::getSegments());
    }

    public function testParseCommandMixed(): void
    {
        $_SERVER['argv'] = [
            'ignored',
            'b',
            'c',
            'd',
            '--parm',
            'pvalue',
            'd2',
            'da-sh',
            '--fix',
            '--opt-in',
            'sure',
        ];
        CLI::init();

        $this->assertNull(CLI::getSegment(7));
        $this->assertSame('b', CLI::getSegment(1));
        $this->assertSame('c', CLI::getSegment(2));
        $this->assertSame('d', CLI::getSegment(3));
        $this->assertSame(['b', 'c', 'd', 'd2', 'da-sh'], CLI::getSegments());
        $this->assertSame(['parm' => 'pvalue', 'fix' => null, 'opt-in' => 'sure'], CLI::getOptions());
        $this->assertSame('-parm pvalue -fix -opt-in sure ', CLI::getOptionString());
        $this->assertSame('-parm pvalue -fix -opt-in sure', CLI::getOptionString(false, true));
        $this->assertSame('--parm pvalue --fix --opt-in sure ', CLI::getOptionString(true));
        $this->assertSame('--parm pvalue --fix --opt-in sure', CLI::getOptionString(true, true));
    }

    public function testParseCommandOption(): void
    {
        $_SERVER['argv'] = [
            'ignored',
            'b',
            'c',
            '--parm',
            'pvalue',
            'd',
        ];
        CLI::init();

        $this->assertSame(['parm' => 'pvalue'], CLI::getOptions());
        $this->assertSame('pvalue', CLI::getOption('parm'));
        $this->assertSame('-parm pvalue ', CLI::getOptionString());
        $this->assertSame('-parm pvalue', CLI::getOptionString(false, true));
        $this->assertSame('--parm pvalue ', CLI::getOptionString(true));
        $this->assertSame('--parm pvalue', CLI::getOptionString(true, true));
        $this->assertNull(CLI::getOption('bogus'));
        $this->assertSame(['b', 'c', 'd'], CLI::getSegments());
    }

    public function testParseCommandMultipleOptions(): void
    {
        $_SERVER['argv'] = [
            'ignored',
            'b',
            'c',
            '--parm',
            'pvalue',
            'd',
            '--p2',
            '--p3',
            'value 3',
        ];
        CLI::init();

        $this->assertSame(['parm' => 'pvalue', 'p2' => null, 'p3' => 'value 3'], CLI::getOptions());
        $this->assertSame('pvalue', CLI::getOption('parm'));
        $this->assertSame('-parm pvalue -p2 -p3 "value 3" ', CLI::getOptionString());
        $this->assertSame('-parm pvalue -p2 -p3 "value 3"', CLI::getOptionString(false, true));
        $this->assertSame('--parm pvalue --p2 --p3 "value 3" ', CLI::getOptionString(true));
        $this->assertSame('--parm pvalue --p2 --p3 "value 3"', CLI::getOptionString(true, true));
        $this->assertSame(['b', 'c', 'd'], CLI::getSegments());
    }

    public function testWindow(): void
    {
        $height = new ReflectionProperty(CLI::class, 'height');
        $height->setAccessible(true);
        $height->setValue(null, null);

        $this->assertIsInt(CLI::getHeight());

        $width = new ReflectionProperty(CLI::class, 'width');
        $width->setAccessible(true);
        $width->setValue(null, null);

        $this->assertIsInt(CLI::getWidth());
    }

    /**
     * @param array $tbody
     * @param array $thead
     * @param array $expected
     */
    #[DataProvider('provideTable')]
    public function testTable($tbody, $thead, $expected): void
    {
        CLI::table($tbody, $thead);

        $this->assertSame($this->getStreamFilterBuffer(), $expected);
    }

    public static function provideTable(): iterable
    {
        $head = [
            'ID',
            'Title',
        ];
        $oneRow = [
            [
                'id'  => 1,
                'foo' => 'bar',
            ],
        ];
        $manyRows = [
            [
                'id'  => 1,
                'foo' => 'bar',
            ],
            [
                'id'  => 2,
                'foo' => 'bar * 2',
            ],
            [
                'id'  => 3,
                'foo' => 'bar + bar + bar',
            ],
        ];

        return [
            [
                $oneRow,
                [],
                "+---+-----+\n" .
                "| 1 | bar |\n" .
                "+---+-----+\n\n",
            ],
            [
                $oneRow,
                $head,
                "+----+-------+\n" .
                "| ID | Title |\n" .
                "+----+-------+\n" .
                "| 1  | bar   |\n" .
                "+----+-------+\n\n",
            ],
            [
                $manyRows,
                [],
                "+---+-----------------+\n" .
                "| 1 | bar             |\n" .
                "| 2 | bar * 2         |\n" .
                "| 3 | bar + bar + bar |\n" .
                "+---+-----------------+\n\n",
            ],
            [
                $manyRows,
                $head,
                "+----+-----------------+\n" .
                "| ID | Title           |\n" .
                "+----+-----------------+\n" .
                "| 1  | bar             |\n" .
                "| 2  | bar * 2         |\n" .
                "| 3  | bar + bar + bar |\n" .
                "+----+-----------------+\n\n",
            ],
            // Multibyte letters
            [
                [
                    [
                        'id'  => 'ほげ',
                        'foo' => 'bar',
                    ],
                ],
                [
                    'ID',
                    'タイトル',
                ],
                "+------+----------+\n" .
                "| ID   | タイトル |\n" .
                "+------+----------+\n" .
                "| ほげ | bar      |\n" .
                "+------+----------+\n\n",
            ],
        ];
    }

    public function testStrlen(): void
    {
        $this->assertSame(18, mb_strlen(CLI::color('success', 'green')));
        $this->assertSame(7, CLI::strlen(CLI::color('success', 'green')));
        $this->assertSame(0, CLI::strlen(null));
    }
}
