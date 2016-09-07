<?php namespace CodeIgniter\Validation;

use Config\Database;

class Rules
{
	/**
	 * Alpha
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha(string $str): bool
	{
		return ctype_alpha($str);
	}

	//--------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha_dash(string $str): bool
	{
		return (bool)preg_match('/^[a-z0-9_-]+$/i', $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Alpha-numeric
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha_numeric(string $str): bool
	{
		return ctype_alnum((string)$str);
	}

	//--------------------------------------------------------------------

	/**
	 * Alpha-numeric w/ spaces
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function alpha_numeric_spaces(string $str): bool
	{
		return (bool)preg_match('/^[A-Z0-9 ]+$/i', $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Decimal number
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function decimal(string $str): bool
	{
		return (bool)preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
	}

	//--------------------------------------------------------------------

	/**
	 * The value does not match another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data Other field/value pairs
	 *
	 * @return bool
	 */
	public function differs(string $str, string $field, array $data): bool
	{
		return isset($data[$field])
			? ($str !== $data[$field])
			: false;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if $str is $val characters long.
	 *
	 * @param string $str
	 * @param string $val
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function exact_length(string $str, string $val, array $data): bool
	{
		if (! is_numeric($val))
		{
			return false;
		}

		return ((int)$val == mb_strlen($str));
	}

	//--------------------------------------------------------------------

	/**
	 * Greater than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function greater_than(string $str, string $min, array $data): bool
	{
		return is_numeric($str) ? ($str > $min) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Equal to or Greater than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function greater_than_equal_to(string $str, string $min, array $data): bool
	{
		return is_numeric($str) ? ($str >= $min) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Value should be within an array of values
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function in_list(string $value, string $list, array $data): bool
	{
		return in_array($value, explode(',', $list), TRUE);
	}

	//--------------------------------------------------------------------

	/**
	 * Integer
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function integer(string $str): bool
	{
		return (bool)preg_match('/^[\-+]?[0-9]+$/', $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @param	string
	 * @return	bool
	 */
	public function is_natural(string $str): bool
	{
		return ctype_digit((string) $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Is a Natural number, but not a zero  (1,2,3, etc.)
	 *
	 * @param	string
	 * @return	bool
	 */
	public function is_natural_no_zero(string $str): bool
	{
		return ($str != 0 && ctype_digit((string) $str));
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the database to see if the given value is unique. Can
	 * ignore a single record by field/value to make it useful during
	 * record updates.
	 *
	 * Example:
	 *    is_unique[table.field,ignore_field,ignore_value]
	 *    is_unique[users.email,id,5]
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function is_unique(string $str, string $field, array $data): bool
	{
		// Grab any data for exclusion of a single row.
		list($field, $ignoreField, $ignoreValue) = array_pad(explode(',', $field), 3, null);

		// Break the table and field apart
		sscanf($field, '%[^.].%[^.]', $table, $field);

		$db  = Database::connect();
		$row = $db->table($table)
				  ->where($field, $str);

		if (! empty($ignoreField) && ! empty($ignoreValue))
		{
			$row = $row->where("{$ignoreField} !=", $ignoreValue);
		}

		return (bool)($row->get()
						  ->getRow() === null);
	}

	//--------------------------------------------------------------------

	/**
	 * Less than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function less_than(string $str, string $max): bool
	{
		return is_numeric($str) ? ($str < $max) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Equal to or Less than
	 *
	 * @param    string
	 * @param    int
	 *
	 * @return    bool
	 */
	public function less_than_equal_to(string $str, string $max): bool
	{
		return is_numeric($str) ? ($str <= $max) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Matches the value of another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data Other field/value pairs
	 *
	 * @return bool
	 */
	public function matches(string $str, string $field, array $data): bool
	{
		return isset($data[$field])
			? ($str === $data[$field])
			: false;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if $str is $val or fewer characters in length.
	 *
	 * @param string $str
	 * @param string $val
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function max_length(string $str, string $val, array $data): bool
	{
		if (! is_numeric($val))
		{
			return false;
		}

		return ($val >= mb_strlen($str));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if $str is at least $val length.
	 *
	 * @param string $str
	 * @param string $val
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function min_length(string $str, string $val, array $data): bool
	{
		if (! is_numeric($val))
		{
			return false;
		}

		return ($val <= mb_strlen($str));
	}

	//--------------------------------------------------------------------

	/**
	 * Numeric
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function numeric(string $str): bool
	{
		return (bool)preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

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
	public function regex_match(string $str, string $pattern, array $data): bool
	{
		if (substr($pattern, 0, 1) != '/')
		{
			$pattern = "/{$pattern}/";
		}

		return (bool)preg_match($pattern, $str);
	}

	//--------------------------------------------------------------------

	/**
	 * Required
	 *
	 * @param    string
	 *
	 * @return    bool
	 */
	public function required($str): bool
	{
		return is_array($str) ? (bool)count($str) : (trim($str) !== '');
	}

	//--------------------------------------------------------------------

	/**
	 * The field is required when any of the other fields are present
	 * in the data.
	 *
	 * Example (field is required when the password field is present):
	 *
	 * 	required_with[password]
	 *
	 * @param        $str
	 * @param string $fields
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function required_with($str, string $fields, array $data): bool
	{
	    $fields = explode(',', $fields);

		// If the field is present we can safely assume that
		// the field is here, no matter whether the corresponding
		// search field is present or not.
		$present = $this->required($data[$str] ?? null);

		if ($present === true)
		{
			return true;
		}

		// Still here? Then we fail this test if
		// any of the fields are present in $data
		$requiredFields = array_intersect($fields, $data);

		$requiredFields = array_filter($requiredFields, function($item)
		{
			return ! empty($item);
		});

		return ! (bool)count($requiredFields);
	}

	//--------------------------------------------------------------------

	/**
	 * The field is required when all of the other fields are not present
	 * in the data.
	 *
	 * Example (field is required when the id or email field is missing):
	 *
	 * 	required_without[id,email]
	 *
	 * @param        $str
	 * @param string $fields
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function required_without($str, string $fields, array $data): bool
	{
		$fields = explode(',', $fields);

		// If the field is present we can safely assume that
		// the field is here, no matter whether the corresponding
		// search field is present or not.
		$present = $this->required($data[$str] ?? null);

		if ($present === true)
		{
			return true;
		}

		// Still here? Then we fail this test if
		// any of the fields are not present in $data
		foreach ($fields as $field)
		{
			if (! array_key_exists($field, $data))
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Validates that the string is a valid timezone as per the
	 * timezone_identifiers_list function.
	 *
	 * @see http://php.net/manual/en/datetimezone.listidentifiers.php
	 *
	 * @param string $str
	 *
	 * @return bool
	 */
	public function timezone(string $str): bool
	{
		return in_array($str, timezone_identifiers_list());
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
	public function valid_base64(string $str): bool
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
	public function valid_email(string $str): bool
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
	public function valid_emails(string $str): bool
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
	public function valid_ip(string $ip, string $which = null, array $data): bool
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
	public function valid_url(string $str): bool
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
