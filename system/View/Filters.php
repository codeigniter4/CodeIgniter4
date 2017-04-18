<?php namespace CodeIgniter\View;

class Filters {

	/**
	 * Returns the absolute value of $value.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function abs($value): string
	{
		return abs($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns $value as all lowercase with the first letter capitalized.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function capitalize(string $value): string
	{
		return ucfirst(strtolower($value));
	}

	//--------------------------------------------------------------------

	/**
	 * Formats a date into the given $format.
	 *
	 * @param        $value
	 * @param string $format
	 *
	 * @return string
	 */
	public static function date($value, string $format): string
	{
		if (is_string($value) && ! is_numeric($value))
		{
			$value = strtotime($value);
		}

		return date($format, $value);
	}

	//--------------------------------------------------------------------

	/**
	 * Given a string or DateTime object, will return the date modified
	 * by the given value. Returns the value as a unix timestamp
	 *
	 * Example:
	 *      my_date|date_modify(+1 day)
	 *
	 * @param        $value
	 * @param string $adjustment
	 *
	 * @return string
	 * @internal param string $format
	 *
	 */
	public static function date_modify($value, string $adjustment): string
	{
		$value = self::date($value, 'Y-m-d H:i:s');
		return strtotime($adjustment, strtotime($value));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the given default value if $value is empty or undefined.
	 *
	 * @param        $value
	 * @param string $default
	 *
	 * @return string
	 */
	public static function default($value, string $default): string
	{
		return empty($value)
			? $default
			: $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Escapes the given value with our `esc()` helper function.
	 *
	 * @param        $value
	 * @param string $context
	 *
	 * @return string
	 */
	public static function esc($value, string $context='html'): string
	{
		return esc($value, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an excerpt of the given string.
	 *
	 * @param string $value
	 * @param int    $radius
	 *
	 * @return string
	 */
	public static function excerpt(string $value, string $phrase, int $radius = 100): string
	{
		helper('text');

		return excerpt($value, $phrase, $radius);
	}

	//--------------------------------------------------------------------

	/**
	 * Highlights a given phrase within the text using '<mark></mark>' tags.
	 *
	 * @param string $value
	 * @param string $phrase
	 *
	 * @return string
	 */
	public static function highlight(string $value, string $phrase): string
	{
		helper('text');

		return highlight_phrase($value, $phrase);
	}

	//--------------------------------------------------------------------

	/**
	 * Highlights code samples with HTML/CSS.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function highlight_code($value): string
	{
		helper('text');

		return highlight_code($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Limits the number of chracters to $limit, and trails of with an ellipsis.
	 * Will break at word break so may be more or less than $limit.
	 *
	 * @param     $value
	 * @param int $limit
	 *
	 * @return string
	 */
	public static function limit_chars($value, int $limit=500): string
	{
		helper('text');

		return character_limiter($value, $limit);
	}

	//--------------------------------------------------------------------

	/**
	 * Limits the number of words to $limit, and trails of with an ellipsis.
	 *
	 * @param     $value
	 * @param int $limit
	 *
	 * @return string
	 */
	public static function limit_words($value, int $limit=100): string
	{
		helper('text');

		return word_limiter($value, $limit);
	}

	//--------------------------------------------------------------------

	/**
	 * Converts a string to lowercase.
	 *
	 * @param string  $value
	 *
	 * @return string
	 */
	public static function lower(string $value): string
	{
		return strtolower($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string with all instances of newline character (\n)
	 * converted to an HTML <br/> tag.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function nl2br(string $value): string
	{
		$typography = \Config\Services::typography();

		return $typography->nl2brExceptPre($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Wraps PHP number_format function for use within the parser.
	 *
	 * @param string $value
	 * @param int    $places
	 * @param string $decimal
	 * @param string $separator
	 *
	 * @return string
	 */
	public static function number_format(string $value, int $places): string
	{
		return number_format($value, $places);
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a body of text and uses the auto_typography() method to
	 * turn it into prettier, easier-to-read, prose.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function prose(string $value): string
	{
		$typography = \Config\Services::typography();

		return $typography->autoTypography($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Rounds a given $value in one of 3 ways;
	 *
	 *  - common    Normal rounding
	 *  - ceil      always rounds up
	 *  - floor     always rounds down
	 *
	 * @param string $value
	 * @param int    $precision
	 * @param string $type
	 *
	 * @return string
	 */
	public static function round($value, $precision=2, $type='common')
	{

		if (! is_numeric($precision))
		{
			$type = $precision;
			$precision = 2;
		}

		switch ($type)
		{
			case 'common':
				return round($value, $precision);
				break;
			case 'ceil':
				return ceil($value);
				break;
			case 'floor':
				return floor($value);
				break;
		}

		// Still here, just return the value.
		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Wraps PHP's striptags function for use in the Parser.
	 *
	 * @param string $value
	 * @param string $allowed
	 *
	 * @return string
	 */
	public static function strip_tags(string $value, string $allowed=''): string
	{
		return strip_tags($value, $allowed);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a "title case" version of the string.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function title(string $value): string
	{
		return ucwords(strtolower($value));
	}

	//--------------------------------------------------------------------

	/**
	 * Converts text to all uppercase.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function upper(string $value): string
	{
		return strtoupper($value);
	}

	//--------------------------------------------------------------------
}
