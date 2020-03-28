<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Validation;

/**
 * Format validation Rules.
 *
 * @package CodeIgniter\Validation
 */
class FormatRules
{

	/**
	 * Alpha
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function alpha(string $str = null): bool
	{
		return ctype_alpha($str);
	}

	/**
	 * Alpha with spaces.
	 *
	 * @param string $value Value.
	 *
	 * @return boolean True if alpha with spaces, else false.
	 */
	public function alpha_space(string $value = null): bool
	{
		if ($value === null)
		{
			return true;
		}

		return (bool) preg_match('/^[A-Z ]+$/i', $value);
	}

	/**
	 * Alphanumeric with underscores and dashes
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function alpha_dash(string $str = null): bool
	{
			return (bool) preg_match('/^[a-z0-9_-]+$/i', $str);
	}

		/**
		 * Alphanumeric, spaces, and a limited set of punctuation characters.
		 * Accepted punctuation characters are: ~ tilde, ! exclamation,
		 * # number, $ dollar, % percent, & ampersand, * asterisk, - dash,
		 * _ underscore, + plus, = equals, | vertical bar, : colon, . period
		 * ~ ! # $ % & * - _ + = | : .
		 *
		 * @param string $str
		 *
		 * @return boolean
		 */
	public function alpha_numeric_punct($str)
	{
		return (bool) preg_match('/^[A-Z0-9 ~!#$%\&\*\-_+=|:.]+$/i', $str);
	}

	/**
	 * Alphanumeric
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function alpha_numeric(string $str = null): bool
	{
		return ctype_alnum($str);
	}

	/**
	 * Alphanumeric w/ spaces
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function alpha_numeric_space(string $str = null): bool
	{
		return (bool) preg_match('/^[A-Z0-9 ]+$/i', $str);
	}

	/**
	 * Any type of string
	 *
	 * Note: we specifically do NOT type hint $str here so that
	 * it doesn't convert numbers into strings.
	 *
	 * @param string|null $str
	 *
	 * @return boolean
	 */
	public function string($str = null): bool
	{
		return is_string($str);
	}

	/**
	 * Decimal number
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function decimal(string $str = null): bool
	{
		return (bool) preg_match('/^[-+]?[0-9]{0,}\.?[0-9]+$/', $str);
	}

	/**
	 * String of hexidecimal characters
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function hex(string $str = null): bool
	{
		return ctype_xdigit($str);
	}

	/**
	 * Integer
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function integer(string $str = null): bool
	{
		return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
	}

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @param  string $str
	 * @return boolean
	 */
	public function is_natural(string $str = null): bool
	{
		return ctype_digit($str);
	}

	/**
	 * Is a Natural number, but not a zero  (1,2,3, etc.)
	 *
	 * @param  string $str
	 * @return boolean
	 */
	public function is_natural_no_zero(string $str = null): bool
	{
		return ($str !== '0' && ctype_digit($str));
	}

	/**
	 * Numeric
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function numeric(string $str = null): bool
	{
		return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
	}

	/**
	 * Compares value against a regular expression pattern.
	 *
	 * @param string $str
	 * @param string $pattern
	 *
	 * @return boolean
	 */
	public function regex_match(string $str = null, string $pattern): bool
	{
		if (strpos($pattern, '/') !== 0)
		{
			$pattern = "/{$pattern}/";
		}

		return (bool) preg_match($pattern, $str);
	}

	/**
	 * Validates that the string is a valid timezone as per the
	 * timezone_identifiers_list function.
	 *
	 * @see http://php.net/manual/en/datetimezone.listidentifiers.php
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function timezone(string $str = null): bool
	{
		return in_array($str, timezone_identifiers_list());
	}

	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @param  string $str
	 * @return boolean
	 */
	public function valid_base64(string $str = null): bool
	{
		return (base64_encode(base64_decode($str)) === $str);
	}

	/**
	 * Valid JSON
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_json(string $str = null): bool
	{
		json_decode($str);
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Checks for a correctly formatted email address
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_email(string $str = null): bool
	{
		if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && preg_match('#\A([^@]+)@(.+)\z#', $str, $matches))
		{
			$str = $matches[1] . '@' . idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
		}

		return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validate a comma-separated list of email addresses.
	 *
	 * Example:
	 *     valid_emails[one@example.com,two@example.com]
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_emails(string $str = null): bool
	{
		foreach (explode(',', $str) as $email)
		{
			$email = trim($email);
			if ($email === '')
			{
				return false;
			}

			if ($this->valid_email($email) === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate an IP address (human readable format or binary string - inet_pton)
	 *
	 * @param string $ip    IP Address
	 * @param string $which IP protocol: 'ipv4' or 'ipv6'
	 *
	 * @return boolean
	 */
	public function valid_ip(string $ip = null, string $which = null): bool
	{
		if (empty($ip))
		{
			return false;
		}
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

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which) || (! ctype_print($ip) && (bool) filter_var(inet_ntop($ip), FILTER_VALIDATE_IP, $which));
	}

	/**
	 * Checks a URL to ensure it's formed correctly.
	 *
	 * @param string $str
	 *
	 * @return boolean
	 */
	public function valid_url(string $str = null): bool
	{
		if (empty($str))
		{
			return false;
		}
		elseif (preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches))
		{
			if (! in_array($matches[1], ['http', 'https'], true))
			{
				return false;
			}

			$str = $matches[2];
		}

		$str = 'http://' . $str;

		return (filter_var($str, FILTER_VALIDATE_URL) !== false);
	}

	/**
	 * Checks for a valid date and matches a given date format
	 *
	 * @param string $str
	 * @param string $format
	 *
	 * @return boolean
	 */
	public function valid_date(string $str = null, string $format = null): bool
	{
		if (empty($format))
		{
			return (bool) strtotime($str);
		}

		$date = \DateTime::createFromFormat($format, $str);

		return (bool) $date && \DateTime::getLastErrors()['warning_count'] === 0 && \DateTime::getLastErrors()['error_count'] === 0;
	}

}
