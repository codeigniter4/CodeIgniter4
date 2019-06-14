<?php

/**
 * Functionality used to help display data to screen in a nicer format than var_dump()
 *
 * ddd() is a quicker way o call pretty_dump(), they both do pretty much the same thing
 */

if (! function_exists('ddd'))
{
	/**
	 * alias for pretty_dump
	 *
	 * @param variable $object
	 * @param boolean  $die        set to false if you don't want function to die
	 * @param boolean  $full_trace set to true if you want a full trace in the dump
	 */
	function ddd($object, $die = true, $full_trace = false)
	{
		pretty_dump($object, $die, $full_trace, true);
	}
}

if (! function_exists('pretty_dump'))
{
	/**
	 * dumps variables to screen and optionally die, and a full trace
	 *
	 * @param variable $object
	 * @param boolean  $die        set to false if you don't want function to die
	 * @param boolean  $full_trace set to true if you want a full trace in the dump
	 * @param boolean  $ddd        did we get to this function from ddd?
	 * @param integer  $trace_line set to numeric if we want only a certain line traced back
	 */
	function pretty_dump($object, $die = true, $full_trace = false, $ddd = false, $trace_line = null)
	{
		$fileinfo  = 'no_file_info';
		$backtrace = debug_backtrace();

		//if we want a full trace of whats happend
		if ($full_trace)
		{
			$backtrace_data_i_want = [];

			if (! empty($backtrace))
			{
				foreach ($backtrace as $back)
				{
					$backtrace_data_i_want[] = $back['file'] . ' - Line: ' . $back['line'];
				}
			}

			if ($trace_line !== null)
			{
				dump($backtrace_data_i_want[$trace_line], $object);
			}
			else
			{
				dump($backtrace_data_i_want, $object);
			}

			//otherwise we only want the last trace data
		}
		else
		{
			$depth = $ddd === false ? 0 : 1;
			if (! empty($backtrace[$depth]) && is_array($backtrace[$depth]))
			{
				$fileinfo = $backtrace[$depth]['file'] . ' - Line: ' . $backtrace[$depth]['line'];
			}
			dump($fileinfo, $object);
		}

		//should we die?
		if ($die)
		{
			die();
		}
	}
}
