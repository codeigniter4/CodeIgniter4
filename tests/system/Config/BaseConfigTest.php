<?php
namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;

class BaseConfigTest extends CIUnitTestCase
{

	protected $fixturesFolder;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->fixturesFolder = __DIR__ . '/fixtures';

		if (! class_exists('SimpleConfig', false))
		{
			require $this->fixturesFolder . '/SimpleConfig.php';
		}
		if (! class_exists('RegistrarConfig', false))
		{
			require $this->fixturesFolder . '/RegistrarConfig.php';
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
		$this->assertTrue($config->foxtrot);
		// numbers should be treated properly
		$this->assertEquals(18, $config->golf);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testServerValues()
	{
		$_SERVER = [
			'simpleconfig.shortie' => 123,
			'SimpleConfig.longie'  => 456,
		];
		$dotenv  = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();
		$config = new \SimpleConfig();

		$this->assertEquals(123, $config->shortie);
		$this->assertEquals(456, $config->longie);
	}

	//--------------------------------------------------------------------

	public function testEnvironmentOverrides()
	{
		$dotenv = new DotEnv($this->fixturesFolder, '.env');
		$dotenv->load();

		$config = new \SimpleConfig();

		// override config with ENV var
		$this->assertEquals('pow', $config->alpha);
		// config should not be over-written by wrongly named ENV var
		$this->assertEquals('three', $config->charlie);
		// override config with shortPrefix ENV var
		$this->assertEquals('hubbahubba', $config->delta);
		// incorrect env name should not inject property
		$this->assertObjectNotHasAttribute('notthere', $config);
		// same ENV var as property, but not namespaced, still over-rides
		$this->assertEquals('kazaam', $config->bravo);
		// empty ENV var should not affect config setting
		$this->assertEquals('pineapple', $config->fruit);
		// non-empty ENV var should overrideconfig setting
		$this->assertEquals('banana', $config->dessert);
		// null property should not be affected
		$this->assertNull($config->QEMPTYSTR);
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
		$this->assertFalse(array_key_exists('pilot', $config->crew));
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
		$this->assertSame('0', $config->QZEROSTR);
		$this->assertEquals(' ', $config->QEMPTYSTR);
		$this->assertFalse($config->QFALSE);
	}

	//--------------------------------------------------------------------

	public function testRegistrars()
	{
		$config              = new \RegistrarConfig();
		$config::$registrars = ['\Tests\Support\Config\Registrar'];
		$this->setPrivateProperty($config, 'didDiscovery', true);
		$method = $this->getPrivateMethodInvoker($config, 'registerProperties');
		$method();

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
		// Shouldn't change any values.
		$config              = new \RegistrarConfig();
		$config::$registrars = ['\Tests\Support\Config\BadRegistrar'];
		$this->setPrivateProperty($config, 'didDiscovery', true);

		$this->expectException(\RuntimeException::class);
		$method = $this->getPrivateMethodInvoker($config, 'registerProperties');
		$method();

		$this->assertEquals('bar', $config->foo);
	}

	public function testNotEnabled()
	{
		$modulesConfig          = config('Modules');
		$modulesConfig->enabled = false;

		$config              = new \RegistrarConfig();
		$config::$registrars = [];
		$expected            = $config::$registrars;

		$method = $this->getPrivateMethodInvoker($config, 'registerProperties');
		$method();

		$this->assertEquals($expected, $config::$registrars);
	}

	public function testDidDiscovery()
	{
		$modulesConfig          = config('Modules');
		$modulesConfig->enabled = true;

		$config              = new \RegistrarConfig();
		$config::$registrars = [];
		$this->setPrivateProperty($config, 'didDiscovery', false);

		$method = $this->getPrivateMethodInvoker($config, 'registerProperties');
		$method();

		$this->assertEquals(true, $this->getPrivateProperty($config, 'didDiscovery'));
	}

}
