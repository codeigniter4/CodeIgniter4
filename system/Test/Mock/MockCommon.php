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
	 * @param  boolean $newReturn return value to set
	 * @return boolean
	 */
	function is_cli(bool $newReturn = null): bool
	{
		// PHPUnit always runs via CLI.
		static $returnValue = true;

		if ($newReturn !== null)
		{
			$returnValue = $newReturn;
		}

		return $returnValue;
	}
}

//--------------------------------------------------------------------
