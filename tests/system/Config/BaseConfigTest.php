<?php namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;

class BaseConfigTest extends CIUnitTestCase
{
	protected $fixturesFolder;

	//--------------------------------------------------------------------

	public function setup()
	{
		$this->fixturesFolder = __DIR__.'/fixtures';

		if (! class_exists('SimpleConfig', false))
		{
			require $this->fixturesFolder.'/SimpleConfig.php';
		}
	}

	//--------------------------------------------------------------------

	public function testBasicValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals('bar', $config->FOO);
	}

	//--------------------------------------------------------------------

	public function testPrefixedValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals('baz', $config->onedeep);
	}

	//--------------------------------------------------------------------

	public function testPrefixedArrayValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals('ci4', $config->default['name']);
	}

	//--------------------------------------------------------------------

	public function testArrayValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals('simpleton', $config->simple['name']);
	}

	//--------------------------------------------------------------------

	public function testSetsDefaultValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, 'commented.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals('foo', $config->first);
		$this->assertEquals('bar', $config->second);
	}

	//--------------------------------------------------------------------

	public function testRecognizesLooseValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, 'loose.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals(0, $config->QZERO);
		$this->assertSame("0", $config->QZEROSTR);
		$this->assertEquals(" ", $config->QEMPTYSTR);
		$this->assertFalse($config->QFALSE);
	}

	//--------------------------------------------------------------------

}
