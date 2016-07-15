<?php namespace CodeIgniter\Config;

//require_once 'system/Benchmark/Timer.php';

class DotEnvTest extends \CIUnitTestCase
{
	
	protected $fixturesFolder;
	
	//--------------------------------------------------------------------
	
	public function setup()
	{
		$this->fixturesFolder = __DIR__.'/fixtures';
	}
	
	//--------------------------------------------------------------------
	
	public function testReturnsFalseIfCannotFindFile()
	{
		$dotenv = new DotEnv(__DIR__);
		$this->assertFalse($dotenv->load());
	}

	//--------------------------------------------------------------------

	public function testLoadsVars()
	{
		$dotenv = new DotEnv($this->fixturesFolder);
		$dotenv->load();
		$this->assertEquals('bar', getenv('FOO'));
		$this->assertEquals('baz', getenv('BAR'));
		$this->assertEquals('with spaces', getenv('SPACED'));
		$this->assertEquals('', getenv('NULL'));
	}

	//--------------------------------------------------------------------

	public function testCommentedLoadsVars()
	{
		$dotenv = new DotEnv($this->fixturesFolder, 'commented.env');
		$dotenv->load();
		$this->assertEquals('bar', getenv('CFOO'));
		$this->assertEquals(false, getenv('CBAR'));
		$this->assertEquals(false, getenv('CZOO'));
		$this->assertEquals('with spaces', getenv('CSPACED'));
		$this->assertEquals('a value with a # character', getenv('CQUOTES'));
		$this->assertEquals('a value with a # character & a quote " character inside quotes', getenv('CQUOTESWITHQUOTE'));
		$this->assertEquals('', getenv('CNULL'));
	}

	//--------------------------------------------------------------------

	public function testQuotedDotenvLoadsEnvironmentVars()
	{
		$dotenv = new Dotenv($this->fixturesFolder, 'quoted.env');
		$dotenv->load();
		$this->assertEquals('bar', getenv('QFOO'));
		$this->assertEquals('baz', getenv('QBAR'));
		$this->assertEquals('with spaces', getenv('QSPACED'));
		$this->assertEquals('', getenv('QNULL'));
		$this->assertEquals('pgsql:host=localhost;dbname=test', getenv('QEQUALS'));
		$this->assertEquals('test some escaped characters like a quote (") or maybe a backslash (\\)', getenv('QESCAPED'));
	}

	//--------------------------------------------------------------------

	public function testSpacedValuesWithoutQuotesThrowsException()
	{
		$this->setExpectedException('InvalidArgumentException', '.env values containing spaces must be surrounded by quotes.');

		$dotenv = new Dotenv($this->fixturesFolder, 'spaced-wrong.env');
		$dotenv->load();
	}

	//--------------------------------------------------------------------

	public function testLoadsServerGlobals()
	{
		$dotenv = new Dotenv($this->fixturesFolder, '.env');
		$dotenv->load();

		$this->assertEquals('bar', $_SERVER['FOO']);
		$this->assertEquals('baz', $_SERVER['BAR']);
		$this->assertEquals('with spaces', $_SERVER['SPACED']);
		$this->assertEquals('', $_SERVER['NULL']);
	}

	//--------------------------------------------------------------------

	public function testLoadsEnvGlobals()
	{
		$dotenv = new Dotenv($this->fixturesFolder);
		$dotenv->load();
		$this->assertEquals('bar', $_ENV['FOO']);
		$this->assertEquals('baz', $_ENV['BAR']);
		$this->assertEquals('with spaces', $_ENV['SPACED']);
		$this->assertEquals('', $_ENV['NULL']);
	}

	//--------------------------------------------------------------------

	public function testNestedEnvironmentVars()
	{
		$dotenv = new Dotenv($this->fixturesFolder, 'nested.env');
		$dotenv->load();
		$this->assertEquals('{$NVAR1} {$NVAR2}', $_ENV['NVAR3']); // not resolved
		$this->assertEquals('Hello World!', $_ENV['NVAR4']);
		$this->assertEquals('$NVAR1 {NVAR2}', $_ENV['NVAR5']); // not resolved
	}

	//--------------------------------------------------------------------

	public function testDotenvAllowsSpecialCharacters()
	{
		$dotenv = new Dotenv($this->fixturesFolder, 'specialchars.env');
		$dotenv->load();
		$this->assertEquals('$a6^C7k%zs+e^.jvjXk', getenv('SPVAR1'));
		$this->assertEquals('?BUty3koaV3%GA*hMAwH}B', getenv('SPVAR2'));
		$this->assertEquals('jdgEB4{QgEC]HL))&GcXxokB+wqoN+j>xkV7K?m$r', getenv('SPVAR3'));
		$this->assertEquals('22222:22#2^{', getenv('SPVAR4'));
		$this->assertEquals('test some escaped characters like a quote " or maybe a backslash \\', getenv('SPVAR5'));
	}

	//--------------------------------------------------------------------

}
