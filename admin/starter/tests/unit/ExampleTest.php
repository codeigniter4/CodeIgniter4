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

		if ($dotenv->load())
		{
			// Check any line with "app.baseUrl" to see if it actually has a value set
			foreach (preg_grep('/^app\.baseURL', file(HOMEPATH . '.env')) as $line)
			{
			}
		}

		$this->assertTrue($test);
	}
}
