<?php namespace CodeIgniter\Validation\Rules;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Rules.
 *
 * @package CodeIgniter\Validation
 */
class Format
{
	/**
	 * Value should be within an array of values
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function in_list(string $value=null, string $list, array $data): bool
	{
	    $list = explode(',', $list);
	    $list = array_map(function($value) { return trim($value); }, $list);
		return in_array($value, $list, TRUE);
	}

	//--------------------------------------------------------------------

	/**
	 * Compares value against a regular expression pattern.
	 *
	 * @param string $str
	 * @param string $pattern
	 * @param array  $data Other field/value pairs
	 *
	 * @return bool
	 */
	public function regex_match(string $str=null, string $pattern, array $data): bool
	{
		if (substr($pattern, 0, 1) != '/')
		{
			$pattern = "/{$pattern}/";
		}

		return (bool)preg_match($pattern, $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_base64(string $str=null): bool
	{
		return (base64_encode(base64_decode($str)) === $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks for a correctly formatted email address
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function valid_email(string $str=null): bool
	{
		if (function_exists('idn_to_ascii') && $atpos = strpos($str, '@'))
		{
			$str = substr($str, 0, ++$atpos).idn_to_ascii(substr($str, $atpos));
		}

		return (bool)filter_var($str, FILTER_VALIDATE_EMAIL);
	}

	//--------------------------------------------------------------------

	/**
	 * Validate a comma-separated list of email addresses.
	 *
	 * Example:
	 * 	valid_emails[one@example.com,two@example.com]
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function valid_emails(string $str=null): bool
	{
		if (strpos($str, ',') === false)
		{
			return $this->valid_email(trim($str));
		}

		foreach (explode(',', $str) as $email)
		{
			if (trim($email) !== '' && $this->valid_email(trim($email)) === false)
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Validate an IP address
	 *
	 * @param        $ip     IP Address
	 * @param string $which  IP protocol: 'ipv4' or 'ipv6'
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function valid_ip(string $ip=null, string $which = null, array $data): bool
	{
		switch (strtolower($which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = null;
				break;
		}

		return (bool)filter_var($ip, FILTER_VALIDATE_IP, $which);
	}

	//--------------------------------------------------------------------

	/**
	 * Checks a URL to ensure it's formed correctly.
	 *
	 * @param string $str
	 *
	 * @return bool
	 */
	public function valid_url(string $str=null): bool
	{
		if (empty($str))
		{
			return false;
		}
		elseif (preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches))
		{
			if (empty($matches[2]))
			{
				return false;
			}
			elseif (! in_array($matches[1], ['http', 'https'], true))
			{
				return false;
			}

			$str = $matches[2];
		}

		$str = 'http://'.$str;

		return (filter_var($str, FILTER_VALIDATE_URL) !== false);
	}

	//--------------------------------------------------------------------

}
