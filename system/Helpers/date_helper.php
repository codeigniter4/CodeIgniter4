<?php

if (! function_exists('now'))
{
	/**
	 * Get "now" time
	 *
	 * Returns time() based on the timezone parameter or on the
	 * app_timezone() setting
	 *
	 * @param	string
	 * @return	int
	 */
	function now(string $timezone=null)
	{
		$timezone = empty($timezone)
			? app_timezone()
			: $timezone;

		if ($timezone === 'local' || $timezone === date_default_timezone_get())
		{
			return time();
		}

		$datetime = new DateTime('now', new DateTimeZone($timezone));
		sscanf($datetime->format('j-n-Y G:i:s'), '%d-%d-%d %d:%d:%d', $day, $month, $year, $hour, $minute, $second);

		return mktime($hour, $minute, $second, $month, $day, $year);
	}
}
