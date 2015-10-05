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

if (! function_exists('log_message'))
{
	/**
	 * A convenience/compatibility method for logging events through
	 * the Log system.
	 *
	 * Allowed log levels are:
	 *  - emergency
	 *  - alert
	 *  - critical
	 *  - error
	 *  - warning
	 *  - notice
	 *  - info
	 *  - debug
	 *
	 * @param string $level
	 * @param        $message
	 * @param array  $context
	 *
	 * @return mixed
	 */
	function log_message(string $level, $message, array $context=[])
	{
		return DI('logger')->log($level, $message, $context);
	}
}

//--------------------------------------------------------------------
