<?php
/**
 * Common Functions
 *
 * Several application-wide utility methods.
 *
 * @package  CodeIgniter
 * @category Common Functions
 */

if ( ! function_exists('get_config'))
{
	/**
	 * Loads a config file from the application/config directory, taking
	 * any environment-specific versions of the config file into account.
	 *
	 * This function lets us grab the config file even if the Config class
	 * hasn't been instantiated yet
	 *
	 * @param    string $file
	 *
	 * @return    array
	 */
	function &get_config($file)
	{
		$config = [];

		if (empty($config[$file]))
		{
			$file_path = APPPATH.'config/'.$file.'.php';
			$found     = false;
			if (file_exists($file_path))
			{
				$found = true;
				require($file_path);
			}

			// Is the config file in the environment folder?
			if (file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/'.$file.'.php'))
			{
				require($file_path);
			}
			elseif ( ! $found)
			{
				set_status_header(503);
				echo 'The configuration file does not exist.';
				exit(3); // EXIT_CONFIG
			}

			// Does the $config array exist in the file?
			if ( ! isset($config) OR ! is_array($config))
			{
				set_status_header(503);
				echo 'Your config file does not appear to be formatted correctly.';
				exit(3); // EXIT_CONFIG
			}
		}

		return $config;
	}
}

//--------------------------------------------------------------------

if (! function_exists('DI'))
{
	/**
	 * A convenience method for getting the current instance
	 * of the dependency injection container.
	 *
	 * @return \CodeIgniter\DI\DI instance
	 */
	function DI()
	{
		return \CodeIgniter\DI\DI::getInstance();
	}
}

//--------------------------------------------------------------------

