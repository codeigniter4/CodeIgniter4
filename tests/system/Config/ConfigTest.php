<?php
namespace CodeIgniter\Config;

use Config\DocTypes;

class ConfigTest extends \CodeIgniter\Test\CIUnitTestCase
{

	public function testCreateSingleInstance()
	{
		$Config          = Config::get('DocTypes', false);
		$NamespaceConfig = Config::get('Config\\DocTypes', false);

		$this->assertInstanceOf(DocTypes::class, $Config);
		$this->assertInstanceOf(DocTypes::class, $NamespaceConfig);
	}

	public function testCreateInvalidInstance()
	{
		$Config = Config::get('gfnusvjai', false);

		$this->assertNull($Config);
	}

	public function testCreateSharedInstance()
	{
		$Config  = Config::get('DocTypes');
		$Config2 = Config::get('Config\\DocTypes');

		$this->assertTrue($Config === $Config2);
	}

	public function testCreateNonConfig()
	{
		$Config = Config::get('Constants', false);

		$this->assertNull($Config);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testInjection()
	{
		Config::reset();
		Config::injectMock('Banana', '\stdClass');
		$this->assertNotNull(Config::get('Banana'));
	}

}
