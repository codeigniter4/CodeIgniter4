<?php namespace Tests\Support\Config;

/**
 * Class Registrar
 *
 * Provides a basic registrar class for testing BaseConfig registration functions.
 */

class Registrar
{

	public static function Database()
	{
		$config = [];

		// Under Github Actions, we can set an ENV var named 'DB'
		// so that we can test against multiple databases.
		if ($group = getenv('DB'))
		{
			if (is_file(TESTPATH . '_github/Database.php'))
			{
				require TESTPATH . '_github/Database.php';

				if (! empty($dbconfig) && array_key_exists($group, $dbconfig))
				{
					$config['tests'] = $dbconfig[$group];
				}
			}
		}

		return $config;
	}

}
