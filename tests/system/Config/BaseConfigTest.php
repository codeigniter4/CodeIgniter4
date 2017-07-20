<?php namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;

class BaseConfigTest extends CIUnitTestCase
{

	protected $fixturesFolder;

	//--------------------------------------------------------------------

	public function setup()
	{
		$this->fixturesFolder = __DIR__ . '/fixtures';

		if ( ! class_exists('SimpleConfig', false))
		{
			require $this->fixturesFolder . '/SimpleConfig.php';
		}
		if ( ! class_exists('RegistrarConfig', false))
		{
			require $this->fixturesFolder . '/RegistrarConfig.php';
		}
		if ( ! class_exists('RegistrarConfig2', false))
		{
			require $this->fixturesFolder . '/RegistrarConfig2.php';
		}
		if ( ! class_exists('RegistrarConfig3', false))
		{
			require $this->fixturesFolder . '/RegistrarConfig3.php';
		}
	}

	//--------------------------------------------------------------------

	public function testBasicValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();
		$config = new \SimpleConfig();

		$this->assertEquals('bar', $config->FOO);
		// empty treated as boolean false
		$this->assertEquals(false, $config->echo);
		// 'true' should be treated as boolean true
		$this->assertEquals(true, $config->foxtrot);
		// numbers should be treated properly
		$this->assertEquals(18, $config->golf);
	}

	//--------------------------------------------------------------------

	public function testEnvironmentOverrides()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env', 'z');
		$dotenv->load();

		$config = new \SimpleConfig();

		// override config with ENV var
		$this->assertEquals('pow', $config->alpha);
		// config should not be over-written by wrongly named ENV var
		$this->assertEquals('three', $config->charlie);
		// override config with shortPrefix ENV var
		$this->assertEquals('hubbahubba', $config->delta);
		// incorrect env name should not inject property
		$this->assertFalse(isset($config->notthere));
		// same ENV var as property, but not namespaced, still over-rides 
		$this->assertEquals('kazaam', $config->bravo);
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
		$this->assertEquals('Malcolm', $config->crew['captain']);
		$this->assertEquals('Spock', $config->crew['science']);
		$this->assertEmpty($config->crew['pilot']);
		$this->assertTrue($config->crew['comms']);
		$this->assertFalse($config->crew['doctor']);
	}

	//--------------------------------------------------------------------

	public function testArrayValues()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		$this->assertEquals('complex', $config->simple['name']);
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

		// no change to unmodified property	
		$this->assertEquals('bar', $config->foo);
		// add to an existing array property
		$this->assertEquals(['baz', 'first', 'second'], $config->bar);
		// add a new property
		$this->assertEquals('nice', $config->format);
		// add a new array property
		$this->assertEquals(['apple', 'banana'], $config->fruit);
	}

	public function testBadRegistrar()
	{
		$this->expectException('RuntimeException');
		$config = new \RegistrarConfig2();
		$this->assertEquals('bar', $config->foo);
	}

	// not very interesting, but useful for code coverage
	public function testWorseRegistrar()
	{
		$config = new \RegistrarConfig3();
		// there should be no change
		$this->assertEquals('bar', $config->foo);
	}

}
