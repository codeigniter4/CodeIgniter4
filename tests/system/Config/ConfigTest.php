<?php namespace CodeIgniter\Config;

use Config\Email;

class ConfigTest extends \CIUnitTestCase
{

	public function testCreateSingleInstance()
	{
		$Config          = Config::get('Format', false);
		$NamespaceConfig = Config::get('Config\\Format', false);

		$this->assertInstanceOf(Email::class, $Config);
		$this->assertInstanceOf(Email::class, $NamespaceConfig);
	}

	public function testCreateInvalidInstance()
	{
		$Config = Config::get('gfnusvjai', false);

		$this->assertNull($Config);
	}

	public function testCreateSharedInstance()
	{
		$Config  = Config::get('Format' );
		$Config2 = Config::get('Config\\Format');

		$this->assertTrue($Config === $Config2);
	}

	public function testCreateNonConfig()
	{
		$Config = Config::get('Constants', false);

		$this->assertNull($Config);
	}
}
