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
 * @copyright  2008-2014 EllisLab, Inc. (https://ellislab.com/)
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 1.0.0
 * @filesource
 */

/**
 * CodeIgniter Number Helpers
 *
 * @package CodeIgniter
 */

if (! function_exists('number_to_size'))
{
	/**
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param mixed   $num       Will be cast as int
	 * @param integer $precision
	 * @param string  $locale
	 *
	 * @return boolean|string
	 */
	function number_to_size($num, int $precision = 1, string $locale = null)
	{
		// Strip any formatting & ensure numeric input
		try
		{
			$num = 0 + str_replace(',', '', $num); // @phpstan-ignore-line
		}
		catch (\ErrorException $ee)
		{
			return false;
		}

		// ignore sub part
		$generalLocale = $locale;
		if (! empty($locale) && ( $underscorePos = strpos($locale, '_')))
		{
			$generalLocale = substr($locale, 0, $underscorePos);
		}

		if ($num >= 1000000000000)
		{
			$num  = round($num / 1099511627776, $precision);
			$unit = lang('Number.terabyteAbbr', [], $generalLocale);
		}
		elseif ($num >= 1000000000)
		{
			$num  = round($num / 1073741824, $precision);
			$unit = lang('Number.gigabyteAbbr', [], $generalLocale);
		}
		elseif ($num >= 1000000)
		{
			$num  = round($num / 1048576, $precision);
			$unit = lang('Number.megabyteAbbr', [], $generalLocale);
		}
		elseif ($num >= 1000)
		{
			$num  = round($num / 1024, $precision);
			$unit = lang('Number.kilobyteAbbr', [], $generalLocale);
		}
		else
		{
			$unit = lang('Number.bytes', [], $generalLocale);
		}

		return format_number($num, $precision, $locale, ['after' => ' ' . $unit]);
	}
}

//--------------------------------------------------------------------

if (! function_exists('number_to_amount'))
{
	/**
	 * Converts numbers to a more readable representation
	 * when dealing with very large numbers (in the thousands or above),
	 * up to the quadrillions, because you won't often deal with numbers
	 * larger than that.
	 *
	 * It uses the "short form" numbering system as this is most commonly
	 * used within most English-speaking countries today.
	 *
	 * @see https://simple.wikipedia.org/wiki/Names_for_large_numbers
	 *
	 * @param string      $num
	 * @param integer     $precision
	 * @param string|null $locale
	 *
	 * @return boolean|string
	 */
	function number_to_amount($num, int $precision = 0, string $locale = null)
	{
		// Strip any formatting & ensure numeric input
		try
		{
			$num = 0 + str_replace(',', '', $num); // @phpstan-ignore-line
		}
		catch (\ErrorException $ee)
		{
			return false;
		}

		$suffix = '';

		// ignore sub part
		$generalLocale = $locale;
		if (! empty($locale) && ( $underscorePos = strpos($locale, '_')))
		{
			$generalLocale = substr($locale, 0, $underscorePos);
		}

		if ($num > 1000000000000000)
		{
			$suffix = lang('Number.quadrillion', [], $generalLocale);
			$num    = round(($num / 1000000000000000), $precision);
		}
		elseif ($num > 1000000000000)
		{
			$suffix = lang('Number.trillion', [], $generalLocale);
			$num    = round(($num / 1000000000000), $precision);
		}
		else if ($num > 1000000000)
		{
			$suffix = lang('Number.billion', [], $generalLocale);
			$num    = round(($num / 1000000000), $precision);
		}
		else if ($num > 1000000)
		{
			$suffix = lang('Number.million', [], $generalLocale);
			$num    = round(($num / 1000000), $precision);
		}
		else if ($num > 1000)
		{
			$suffix = lang('Number.thousand', [], $generalLocale);
			$num    = round(($num / 1000), $precision);
		}

		return format_number($num, $precision, $locale, ['after' => $suffix]);
	}
}

//--------------------------------------------------------------------

if (! function_exists('number_to_currency'))
{
	/**
	 * @param float   $num
	 * @param string  $currency
	 * @param string  $locale
	 * @param integer $fraction
	 *
	 * @return string
	 */
	function number_to_currency(float $num, string $currency, string $locale = null, int $fraction = null): string
	{
		return format_number($num, 1, $locale, [
			'type'     => NumberFormatter::CURRENCY,
			'currency' => $currency,
			'fraction' => $fraction,
		]);
	}
}

//--------------------------------------------------------------------

if (! function_exists('format_number'))
{
	/**
	 * A general purpose, locale-aware, number_format method.
	 * Used by all of the functions of the number_helper.
	 *
	 * @param float       $num
	 * @param integer     $precision
	 * @param string|null $locale
	 * @param array       $options
	 *
	 * @return string
	 */
	function format_number(float $num, int $precision = 1, string $locale = null, array $options = []): string
	{
		// Locale is either passed in here, negotiated with client, or grabbed from our config file.
		$locale = $locale ?? \Config\Services::request()->getLocale();

		// Type can be any of the NumberFormatter options, but provide a default.
		$type = (int) ($options['type'] ?? NumberFormatter::DECIMAL);

		$formatter = new NumberFormatter($locale, $type);

		// Try to format it per the locale
		if ($type === NumberFormatter::CURRENCY)
		{
			$formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $options['fraction']);
			$output = $formatter->formatCurrency($num, $options['currency']);
		}
		else
		{
			// In order to specify a precision, we'll have to modify
			// the pattern used by NumberFormatter.
			$pattern = '#,##0.' . str_repeat('#', $precision);

			$formatter->setPattern($pattern);
			$output = $formatter->format($num);
		}

		// This might lead a trailing period if $precision == 0
		$output = trim($output, '. ');

		if (intl_is_failure($formatter->getErrorCode()))
		{
			throw new BadFunctionCallException($formatter->getErrorMessage());
		}

		// Add on any before/after text.
		if (isset($options['before']) && is_string($options['before']))
		{
			$output = $options['before'] . $output;
		}

		if (isset($options['after']) && is_string($options['after']))
		{
			$output .= $options['after'];
		}

		return $output;
	}
}

//--------------------------------------------------------------------

if (! function_exists('number_to_roman'))
{
	/**
	 * Convert a number to a roman numeral.
	 *
	 * @param string $num it will convert to int
	 *
	 * @return string|null
	 */
	function number_to_roman(string $num): ?string
	{
		$num = (int) $num;
		if ($num < 1 || $num > 3999)
		{
			return null;
		}

		$_number_to_roman = function ($num, $th) use (&$_number_to_roman) {
			$return = '';
			$key1   = null;
			$key2   = null;
			switch ($th) {
				case 1:
					$key1  = 'I';
					$key2  = 'V';
					$key_f = 'X';
					break;
				case 2:
					$key1  = 'X';
					$key2  = 'L';
					$key_f = 'C';
					break;
				case 3:
					$key1  = 'C';
					$key2  = 'D';
					$key_f = 'M';
					break;
				case 4:
					$key1 = 'M';
					break;
			}
			$n = $num % 10;
			switch ($n) {
				case 1:
				case 2:
				case 3:
					$return = str_repeat($key1, $n);
					break;
				case 4:
					$return = $key1 . $key2;
					break;
				case 5:
					$return = $key2;
					break;
				case 6:
				case 7:
				case 8:
					$return = $key2 . str_repeat($key1, $n - 5);
					break;
				case 9:
					$return = $key1 . $key_f; // @phpstan-ignore-line
					break;
			}
			switch ($num) {
				case 10:
					$return = $key_f; // @phpstan-ignore-line
					break;
			}
			if ($num > 10)
			{
				$return = $_number_to_roman($num / 10, ++ $th) . $return;
			}
			return $return;
		};
		return $_number_to_roman($num, 1);
	}
}
