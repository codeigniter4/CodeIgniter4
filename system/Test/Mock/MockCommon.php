<?php

/**
 * Common Functions for testing
 *
 * Several application-wide utility methods.
 *
 * @package  CodeIgniter
 * @category Common Functions
 */

if (! function_exists('is_cli'))
{
	/**
	 * Is CLI?
	 *
	 * Test to see if a request was made from the command line.
	 * You can set the return value for testing.
	 *
	 * @param  boolean $new_return return value to set
	 * @return boolean
	 */
	function is_cli(bool $new_return = null): bool
	{
		// PHPUnit always runs via CLI.
		static $return_value = true;

		if ($new_return !== null)
		{
			$return_value = $new_return;
		}

		return $return_value;
	}
}

//--------------------------------------------------------------------
