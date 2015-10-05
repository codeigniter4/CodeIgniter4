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
	 * If a class "alias" is passed in as the first parameter
	 * then try to create that class using the single() method.
	 *
	 * @return \CodeIgniter\DI\DI instance
	 */
	function DI($alias=null)
	{
		if (! empty($alias) && is_string($alias))
		{
			return \CodeIgniter\DI\DI::getInstance()->single($alias);
		}

		return \CodeIgniter\DI\DI::getInstance();
	}
}

//--------------------------------------------------------------------

