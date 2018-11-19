<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT    MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

if (! function_exists('now'))
{
	/**
	 * Get "now" time
	 *
	 * Returns time() based on the timezone parameter or on the
	 * app_timezone() setting
	 *
	 * @param string $timezone
	 *
	 * @return integer
	 */
	function now(string $timezone = null)
	{
		$timezone = empty($timezone) ? app_timezone() : $timezone;

		if ($timezone === 'local' || $timezone === date_default_timezone_get())
		{
			return time();
		}

		$datetime = new DateTime('now', new DateTimeZone($timezone));
		sscanf($datetime->format('j-n-Y G:i:s'), '%d-%d-%d %d:%d:%d', $day, $month, $year, $hour, $minute, $second);

		return mktime($hour, $minute, $second, $month, $day, $year);
	}
}

if (! function_exists('validate_date'))
{
	/**
	 * Validate date string from <input type="date">.
	 * Check if the string is valid date string (yyyy-mm-dd)
	 * 
	 * @param string $dateString
	 *
	 * @return bool
	 */
	function validate_date(string $dateString):bool 
	{
    		$dateArr = explode('-', $dateString);
    		if(count($dateArr) === 3)
		{
			return checkdate($dateArr[1], $dateArr[2], $dateArr[0]);
    		}else{
			return FALSE;
    		}
	}
}
if (! function_exists('validate_time'))
{
	/**
	* Validate time string from <input type="time">.
 	* Check if time string is valid using 24-hour format HH:mm OR HH:mm:ss 
 	* @param string $timestring
 	* @param bool $withsecond true for format 'HH:mm:ss' instead (<input type="time" step="1"> will activate second part)
 	* @return bool valid or not
 	*/
	function validate_time(string $timestring,bool $withsecond = FALSE)
	{
    		$timeArr = explode(':', $timestring);
    		if($withsecond)
		{
			if(count($timeArr) !== 3)
			{
	    			return FALSE;
			}
    		}else{
			if(count($timeArr) !== 2)
			{
	    			return FALSE;
			}
    		}
		
    		// accepted range: 00:00:00 to 23:59:59
    		$iHour = (int)$timeArr[0];
    		if(($iHour < 0) || ($iHour > 23))
		{
			return FALSE;
    		}
    		$iMin = (int)$timeArr[1];
    		if(($iMin < 0) || ($iMin > 59))
		{
			return FALSE;
    		}
		//checks second if exist
    		if($withsecond)
		{
			$iSec = (int)$timeArr[2];
			if(($iSec < 0) || ($iSec > 59))
			{
	    			return FALSE;
			}else{
	    			// all checks passed means valid
	    			return TRUE;
			}
    		}else{
			// all checks passed means valid
			return TRUE;
    		}
	}
}
