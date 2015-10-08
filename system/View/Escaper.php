<?php

if (! function_exists('esc'))
{
	/**
	 * Performs simple auto-escaping of data for security reasons.
	 * Might consider making this more complex at a later date.
	 *
	 * If $data is a string, then it simply escapes and returns it.
	 * If $data is an array, then it loops over it, escaping each
	 * 'value' of the key/value pairs.
	 *
	 * @param $data
	 *
	 * @return $data
	 */
	function esc($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				if (is_string($value) || is_numeric($value))
				{
					$value = htmlspecialchars($value, ENT_SUBSTITUTE, 'UTF-8');
				}
			}
		}
		else if (is_string($data))
		{
			$data = htmlspecialchars($data, ENT_SUBSTITUTE, 'UTF-8');
		}

		return $data;
	}
}

//--------------------------------------------------------------------

