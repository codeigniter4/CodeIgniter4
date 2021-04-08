<?php

use Config\Services;

class HealthTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testIsDefinedAppPath()
	{
		$test = defined('APPPATH');

		$this->assertTrue($test);
	}

	public function testBaseUrlHasBeenSet()
	{
		$validation = Services::validation();
		$env        = false;

		// Check the baseURL in .env
		if (is_file(HOMEPATH . '.env'))
		{
			$env = (bool) preg_grep('/^app\.baseURL = ./', file(HOMEPATH . '.env'));
		}

		if ($env)
		{
			// BaseURL in .env is a valid URL?
			// phpunit.xml.dist sets app.baseURL in $_SERVER
			// So if you set app.baseURL in .env, it takes precedence
			$config = new Config\App();
			$this->assertTrue(
				$validation->check($config->baseURL, 'valid_url'),
				'baseURL "' . $config->baseURL . '" in .env is not valid URL'
			);
		}

		// Get the baseURL in app/Config/App.php
		// You can't use Config\App, because phpunit.xml.dist sets app.baseURL
		$reader = new \Tests\Support\Libraries\ConfigReader();

		// BaseURL in app/Config/App.php is a valid URL?
		$this->assertTrue(
			$validation->check($reader->baseURL, 'valid_url'),
			'baseURL "' . $reader->baseURL . '" in app/Config/App.php is not valid URL'
		);
	}
}
