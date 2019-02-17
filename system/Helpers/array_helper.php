<?php

if (! function_exists('dot_array_search'))
{
	/**
	 * Searches an array through dot syntax. Supports
	 * wildcard searches, like foo.*.bar
	 *
	 * @param string $index
	 * @param array  $array
	 *
	 * @return mixed|null
	 */
	function dot_array_search(string $index, array $array)
	{
		$segments = explode('.', rtrim(rtrim($index, '* '), '.'));

		return _array_search_dot($segments, $array);
	}
}

if (! function_exists('_array_search_dot'))
{
	/**
	 * Used by dot_array_search to recursively search the
	 * array with wildcards.
	 *
	 * @param array $indexes
	 * @param array $array
	 *
	 * @return mixed|null
	 */
	function _array_search_dot(array $indexes, array $array)
	{
		// Grab the current index
		$currentIndex = $indexes
			? array_shift($indexes)
			: null;

		if (empty($currentIndex) || (! isset($array[$currentIndex]) && $currentIndex !== '*'))
		{
			return null;
		}

		// Handle Wildcard (*)
		if ($currentIndex === '*')
		{
			// If $array has more than 1 item, we have to loop over each.
			if (is_array($array))
			{
				foreach ($array as $key => $value)
				{
					$answer = _array_search_dot($indexes, $value);

					if ($answer !== null)
					{
						return $answer;
					}
				}

				// Still here after searching all child nodes?
				return null;
			}
		}

		// If this is the last index, make sure to return it now,
		// and not try to recurse through things.
		if (empty($indexes))
		{
			return $array[$currentIndex];
		}

		// Do we need to recursively search this value?
		if (is_array($array[$currentIndex]) && $array[$currentIndex])
		{
			return _array_search_dot($indexes, $array[$currentIndex]);
		}

		// Otherwise we've found our match!
		return $array[$currentIndex];
	}
}

if (!function_exists('sort_array_by_column'))
{
	/**
	 * Sort an array by column name in multidimensional array
	 *
	 * @param array  $array
	 * @param string $column
	 * @param int    $direction
	 * @param int    $sort_type
	 *
	 * @return void
	 */
	function sort_array_by_column(array &$array, string $column, int $direction = SORT_ASC, int $sort_type = SORT_REGULAR)
	{
		$sort_col = [];
		foreach ($array as $key => $row)
		{
			$sort_col[$key] = $row[$column];
		}
		array_multisort($sort_col, $direction, $array, $sort_type);
	}
}

if (!function_exists('reduce_array'))
{
	/**
	 * Eliminate the keys from single dimensional array
	 * 
	 * @param array $array
	 * @param array $keys
	 *
	 * @return array
	 */
	function reduce_array(array $array, array $keys)
	{
		$return_array = [];
		foreach ($array as $key => $value)
		{
			if (!in_array($key, $keys))
			{
				$return_array[$key] = $value;
			}
		}

		return $return_array;
	}
}

if (!function_exists('array_non_empty_items'))
{
	/**
	 * Return all non-empty elements of array
	 * 
	 * @param array|string $array
	 *
	 * @return array|string
	 */
	function array_non_empty_items($array)
	{
		// If it is an element, then just return it
		if (!is_array($array))
		{
			return $array;
		}
		else
		{
			$array = array_filter($array);
		}
		$non_empty_items = [];
		foreach ($array as $key => $value)
		{
			// Ignore empty cells
			if (is_array($value))
			{
				$value = array_filter($value);
			}
			if (count($value))
			{
				// Use recursion to evaluate cells
				$non_empty_items[$key] = array_non_empty_items($value);
			}
		}
		// Finally return the array without empty items
		return $non_empty_items;
	}
}

if (!function_exists('array_search_by_key'))
{
	/**
	 * Search key in array and return the value
	 *
	 * @param string $needle
	 * @param array $array
	 *
	 * @return bool|string|array
	 */
	function array_search_by_key(string $needle, array $array)
	{
		foreach ($array as $key => $value)
		{
			if ($key == $needle)
			{
				return $value;
			}
			if (is_array($value))
			{
				if (($result = array_search_by_key($needle, $value)) !== false)
				{
					return $result;
				}
			}
		}

		return false;
	}
}

if (!function_exists('array_change_key_case_recursive'))
{
	/**
	 * Change the case of keys of an array
	 *
	 * @param array $array
	 * @param bool  $is_lower
	 *
	 * @return array
	 */
	function array_change_key_case_recursive(array $array, bool $is_lower = true)
	{
		return array_map(function ($item) {
			if (is_array($item))
			{
				$item = array_change_key_case_recursive($item);
			}

			return $item;
		}, array_change_key_case($array, ($is_lower) ? CASE_LOWER : CASE_UPPER));
	}
}

if (!function_exists('trim_array'))
{
	/**
	 * Trim the array recursively
	 * 
	 * @param array $array
	 *
	 * @return array
	 */
	function trim_array(array $array)
	{
		$result = [];
		foreach ($array as $key => $val)
		{
			$result[$key] = (is_array($val) ? trim_array($val) : trim($val));
		}

		return $result;
	}
}

if (!function_exists('search_in_array'))
{
	function search_in_array($needle, $array)
	{
		if (is_array($array))
		{
			$foundkey = array_search($needle, $array);
			if ($foundkey === false)
			{
				foreach ($array as $key => $value)
				{
					if (is_array($value) && count($value) > 0)
					{
						$foundkey = search_in_array($needle, $value);
						if ($foundkey != false)
						{
							return $foundkey;
						}
					}
				}
			}
			else
			{
				return $array;
			}
		}
		return false;
	}
}