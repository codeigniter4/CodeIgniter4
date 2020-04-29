<?php namespace CodeIgniter\CLI;

use CodeIgniter\CLI\Exceptions\CLIException;
use CodeIgniter\Test\CIUnitTestCase;

class CLIOutputTest extends CIUnitTestCase
{
	private $stream;

	public function setUp(): void
	{
		$this->stream = @fopen('php://memory', 'a', false);
	}

	public function tearDown(): void
	{
		$this->stream = null;
	}

	/**
	 * Convenience method to get the stream contents
	 *
	 * @return string|false
	 */
	private function getStreamContents()
	{
		rewind($this->stream);
		return stream_get_contents($this->stream);
	}

	private function yieldIterables(): iterable
	{
		yield 'foo';
		yield 'bar';
		yield 'baz';
	}

	public function testConstructorInit()
	{
		$output = new CLIOutput($this->stream);
		$this->assertEquals($this->stream, $output->getStream());
		$this->assertIsBool($output->isColored());

		$output = new CLIOutput($this->stream, true);
		$this->assertEquals($this->stream, $output->getStream());
		$this->assertTrue($output->isColored());

		$output = new CLIOutput($this->stream, false);
		$this->assertEquals($this->stream, $output->getStream());
		$this->assertFalse($output->isColored());
	}

	public function testSetStreamThrowsError()
	{
		try
		{
			new CLIOutput('not_stream');
		}
		catch (\Throwable $th)
		{
			$this->assertInstanceOf('InvalidArgumentException', $th);
			$this->assertEquals('The CLIOutput class needs a stream as its first argument.', $th->getMessage());
		}
	}

	public function testColorSupportNoColor()
	{
		$_SERVER['NO_COLOR'] = true;
		$output              = new CLIOutput($this->stream);
		$this->assertFalse($output->isColored());
		unset($_SERVER['NO_COLOR']);
	}

	public function testColorSupportHyper()
	{
		$currentTermProgram = getenv('TERM_PROGRAM');
		putenv('TERM_PROGRAM=Hyper');
		$output = new CLIOutput($this->stream);
		$this->assertTrue($output->isColored());

		$currentTermProgram ? putenv("TERM_PROGRAM={$currentTermProgram}") : putenv('TERM_PROGRAM');
	}

	public function testGenericColorDetection()
	{
		$output = new CLIOutput($this->stream);
		$this->assertIsBool($output->isColored());
	}

	public function testIsWindows()
	{
		$this->assertEquals(('\\' === DIRECTORY_SEPARATOR), CLIOutput::isWindows());
		$this->assertEquals(defined('PHP_WINDOWS_VERSION_MAJOR'), CLIOutput::isWindows());
	}

	public function testNewLine()
	{
		$output = new CLIOutput($this->stream);

		$output->newLine();
		$this->assertEquals("\n\n", $this->getStreamContents());
	}

	public function testWait()
	{
		$output = new CLIOutput($this->stream);

		$time = time();
		$output->wait(1, true);
		$this->assertCloseEnough(1, time() - $time);

		$time = time();
		$output->wait(1);
		$this->assertCloseEnough(1, time() - $time);

		// Leaving the code fragment below in, to remind myself (or others)
		// of what appears to be the most likely path to test this last
		// bit of wait() functionality.
		// The problem: if the block below is enabled, the phpunit tests
		// go catatonic when it is executed, presumably because of
		// the CLI::input() waiting for a key press
		//      // test the press any key to continue...
		//      stream_filter_register('CLITestKeyboardFilter', 'CodeIgniter\CLI\CLITestKeyboardFilter');
		//      $spoofer = stream_filter_append(STDIN, 'CLITestKeyboardFilter');
		//      $time = time();
		//      CLITestKeyboardFilter::$spoofed = ' ';
		//      CLI::wait(0);
		//      stream_filter_remove($spoofer);
		//      $this->assertEquals(0, time() - $time);
	}

	public function testForegroundColorException()
	{
		$this->expectException(CLIException::class);
		$this->expectExceptionMessage('Invalid foreground color: rainbow');
		(new CLIOutput($this->stream, true))->color('test', 'rainbow');
	}

	public function testBackgroundColorException()
	{
		$this->expectException(CLIException::class);
		$this->expectExceptionMessage('Invalid background color: rainbow');
		(new CLIOutput($this->stream, true))->color('test', 'white', 'rainbow');
	}

	public function testOptionsException()
	{
		$this->expectException(CLIException::class);
		$this->expectExceptionMessage('Invalid option: strikethrough');
		(new CLIOutput($this->stream, true))->color('test', null, null, ['strikethrough']);
	}

	public function testColorOutput()
	{
		$output = new CLIOutput($this->stream, false);
		$this->assertEquals('test', $output->color('test', 'white', 'red'));

		$output = new CLIOutput($this->stream, true);
		$this->assertEquals('test', $output->color('test'));

		$output = new CLIOutput($this->stream, true);
		$this->assertEquals("\033[37;41mtest\033[39;49m", $output->color('test', 'white', 'red'));

		$output = new CLIOutput($this->stream, true);
		$this->assertEquals("\033[37;41;1mtest\033[39;49;22m", $output->color('test', 'white', 'red', ['bold']));
	}

	public function testPrint()
	{
		$output = new CLIOutput($this->stream, true);
		$output->print('test');
		$this->assertEquals('test', $this->getStreamContents());
	}

	public function testPrintForeground()
	{
		$output = new CLIOutput($this->stream, true);
		$output->print('test', 'red');
		$this->assertEquals("\033[31mtest\033[39m", $this->getStreamContents());
	}

	public function testPrintBackground()
	{
		$output = new CLIOutput($this->stream, true);
		$output->print('test', 'white', 'red');
		$this->assertEquals("\033[37;41mtest\033[39;49m", $this->getStreamContents());
	}

	public function testPrintOptions()
	{
		$output = new CLIOutput($this->stream, true);
		$output->print('test', null, null, ['bold']);
		$this->assertEquals("\033[1mtest\033[22m", $this->getStreamContents());
	}

	public function testPrintIterables()
	{
		$output = new CLIOutput($this->stream, true);
		$output->print($this->yieldIterables(), 'red');
		$this->assertEquals(
			"\033[31mfoo\033[39m\033[31mbar\033[39m\033[31mbaz\033[39m",
			$this->getStreamContents()
		);
	}

	public function testWrite()
	{
		$output = new CLIOutput($this->stream, true);
		$output->write('test');
		$this->assertEquals("\ntest\n", $this->getStreamContents());
	}

	public function testWriteForeground()
	{
		$output = new CLIOutput($this->stream, true);
		$output->write('test', 'red');
		$this->assertEquals("\033[31mtest\033[39m\n", $this->getStreamContents());
	}

	public function testWriteBackground()
	{
		$output = new CLIOutput($this->stream, true);
		$output->write('test', 'white', 'red');
		$this->assertEquals("\033[37;41mtest\033[39;49m\n", $this->getStreamContents());
	}

	public function testWriteOptions()
	{
		$output = new CLIOutput($this->stream, true);
		$output->write('test', null, null, ['bold']);
		$this->assertEquals("\033[1mtest\033[22m\n", $this->getStreamContents());
	}

	public function testWriteIterables()
	{
		$output = new CLIOutput($this->stream, true);
		$output->write($this->yieldIterables(), 'red');
		$this->assertEquals(
			"\033[31mfoo\033[39m\n\033[31mbar\033[39m\n\033[31mbaz\033[39m\n",
			$this->getStreamContents()
		);
	}

	public function testError()
	{
		$output = new CLIOutput($this->stream, true);
		$output->error('test', 'red', null, [], $this->stream);
		$this->assertEquals("\033[31mtest\033[39m\n", $this->getStreamContents());
	}

	public function testErrorForeground()
	{
		$output = new CLIOutput($this->stream, true);
		$output->error('test', 'magenta', null, [], $this->stream);
		$this->assertEquals("\033[35mtest\033[39m\n", $this->getStreamContents());
	}

	public function testErrorBackground()
	{
		$output = new CLIOutput($this->stream, true);
		$output->error('test', 'magenta', 'yellow', [], $this->stream);
		$this->assertEquals("\033[35;43mtest\033[39;49m\n", $this->getStreamContents());
	}

	public function testErrorOptions()
	{
		$output = new CLIOutput($this->stream, true);
		$output->error('test', 'red', null, ['bold'], $this->stream);
		$this->assertEquals("\033[31;1mtest\033[39;22m\n", $this->getStreamContents());
	}

	public function testErrorIterables()
	{
		$output = new CLIOutput($this->stream, true);
		$output->error($this->yieldIterables(), 'red', null, [], $this->stream);
		$this->assertEquals(
			"\033[31mfoo\033[39m\n\033[31mbar\033[39m\n\033[31mbaz\033[39m\n",
			$this->getStreamContents()
		);
	}

	public function testStrlen()
	{
		$output = new CLIOutput($this->stream, true);
		$this->assertEquals(0, CLIOutput::strlen(null));
		$this->assertEquals(4, CLIOutput::strlen($output->color('test', 'red', 'yellow')));
		$this->assertEquals(20, mb_strlen($output->color('test', 'red', 'yellow')));
	}

	public function testShowProgress()
	{
		$output = new CLIOutput($this->stream, true);

		$output->write('first.');
		$output->showProgress(1, 20);
		$output->showProgress(10, 20);
		$output->showProgress(20, 20);
		$output->write('second.');
		$output->showProgress(1, 20);
		$output->showProgress(10, 20);
		$output->showProgress(20, 20);
		$output->write('third.');
		$output->showProgress(1, 20);

		$expected = <<<EOT
first.
[\033[32m#.........\033[39m]   5% Complete
\033[1A[\033[32m#####.....\033[39m]  50% Complete
\033[1A[\033[32m##########\033[39m] 100% Complete
second.
[\033[32m#.........\033[39m]   5% Complete
\033[1A[\033[32m#####.....\033[39m]  50% Complete
\033[1A[\033[32m##########\033[39m] 100% Complete
third.
[\033[32m#.........\033[39m]   5% Complete

EOT;
		$this->assertEquals($expected, $this->getStreamContents());
	}

	public function testShowProgressWithoutBar()
	{
		$output = new CLIOutput($this->stream, true);

		$output->write('first.');
		$output->showProgress(false, 20);
		$output->showProgress(false, 20);
		$output->showProgress(false, 20);

		$expected = <<<EOT
first.
\007\007\007
EOT;
		$this->assertEquals($expected, $this->getStreamContents());
	}

	/**
	 * @dataProvider tableProvider
	 */
	public function testTable($tbody, $thead, $expected)
	{
		$output = new CLIOutput($this->stream, true);
		$output->table($tbody, $thead);
		$this->assertEquals($this->getStreamContents(), $expected);
	}

	public function tableProvider()
	{
		$head      = [
			'ID',
			'Title',
		];
		$one_row   = [
			[
				'id'  => 1,
				'foo' => 'bar',
			],
		];
		$many_rows = [
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
				$one_row,
				[],
				"+---+-----+\n" .
				"| 1 | bar |\n" .
				"+---+-----+\n\n",
			],
			[
				$one_row,
				$head,
				"+----+-------+\n" .
				"| ID | Title |\n" .
				"+----+-------+\n" .
				"| 1  | bar   |\n" .
				"+----+-------+\n\n",
			],
			[
				$many_rows,
				[],
				"+---+-----------------+\n" .
				"| 1 | bar             |\n" .
				"| 2 | bar * 2         |\n" .
				"| 3 | bar + bar + bar |\n" .
				"+---+-----------------+\n\n",
			],
			[
				$many_rows,
				$head,
				"+----+-----------------+\n" .
				"| ID | Title           |\n" .
				"+----+-----------------+\n" .
				"| 1  | bar             |\n" .
				"| 2  | bar * 2         |\n" .
				"| 3  | bar + bar + bar |\n" .
				"+----+-----------------+\n\n",
			],
		];
	}
}
