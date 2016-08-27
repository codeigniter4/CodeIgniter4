<?php namespace CodeIgniter\Validation;

use Config\Database;

class Rules
{
	/**
	 * The value does not match another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data   Other field/value pairs
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
	 * Checks the database to see if the given value is unique. Can
	 * ignore a single record by field/value to make it useful during
	 * record updates.
	 *
	 * Example:
	 * 	is_unique[table.field,ignore_field,ignore_value]
	 * 	is_unique[users.email,id,5]
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

		$db = Database::connect();
		$row = $db->table($table)->where($field, $str);

		if (! empty($ignoreField) && !empty($ignoreValue))
		{
			$row = $row->where("{$ignoreField} !=", $ignoreValue);
		}

		return (bool)($row->get()->getRow() === null);
	}

	//--------------------------------------------------------------------

	/**
	 * Matches the value of another field in $data.
	 *
	 * @param string $str
	 * @param string $field
	 * @param array  $data   Other field/value pairs
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
	 * Compares value against a regular expression pattern.
	 *
	 * @param string $str
	 * @param string $pattern
	 * @param array  $data     Other field/value pairs
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
	 * @param	string
	 * @return	bool
	 */
	public function required($str): bool
	{
		return is_array($str) ? (bool) count($str) : (trim($str) !== '');
	}

	//--------------------------------------------------------------------

}
