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
		if (! class_exists('RegistrarConfig', false))
		{
			require $this->fixturesFolder.'/RegistrarConfig.php';
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

	public function testEnvironmentOverrides()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env', 'z');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals('pow', $config->alpha);
		$this->assertEquals('kazaam', $config->bravo);
		$this->assertEquals('', $config->charlie);
		$this->assertEquals('hubbahubba', $config->delta);
		$this->assertEquals(false, $config->echo);
		$this->assertEquals(true, $config->foxtrot);
		$this->assertEquals(18, $config->golf);
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
		$this->assertEquals('foo', $config->first);
		$this->assertEquals('bar', $config->second);
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

	public function testRegistrars()
	{
		$config = new \RegistrarConfig();

		$this->assertEquals(['baz', 'first', 'second'], $config->bar);
	}

}
