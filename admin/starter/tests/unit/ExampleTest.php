<?php

class ExampleTest extends \CodeIgniter\Test\CIUnitTestCase
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
		$env = $config = false;

		// First check in .env
		$dotenv = new \CodeIgniter\Config\DotEnv(HOMEPATH);
		if ($vars = $dotenv->parse())
		{
			$env = ! empty($vars['app.baseUrl']);
		}

		// Then check the actual config file
		$reader = new \Tests\Support\Libraries\ConfigReader();
		$config = ! empty($reader->baseUrl);

		$this->assertTrue($env || $config);
	}
}
