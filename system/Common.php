<?php
/**
 * Common Functions
 *
 * Several application-wide utility methods.
 *
 * @package  CodeIgniter
 * @category Common Functions
 */

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

