<?php namespace CodeIgniter\Config;

use Config\Email;

class ConfigTest extends \CIUnitTestCase
{

	public function testCreateSingleInstance()
	{
		$Config          = Config::get('Email', false);
		$NamespaceConfig = Config::get('Config\\Email', false);

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
		$Config  = Config::get('Email' );
		$Config2 = Config::get('Config\\Email');

		$this->assertTrue($Config === $Config2);
	}

	public function testCreateNonConfig()
	{
		$Config = Config::get('Constants', false);

		$this->assertNull($Config);
	}
}
