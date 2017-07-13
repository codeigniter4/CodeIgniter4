<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @author	EllisLab Dev Team
 * @copyright	2008-2014 EllisLab, Inc. (https://ellislab.com/)
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
if ( ! function_exists('number_to_size'))
{

	/**
	 * Formats a numbers as bytes, based on size, and adds the appropriate suffix
	 *
	 * @param	mixed	will be cast as int
	 * @param	int
	 * @return	string
	 */
	function number_to_size($num, int $precision = 1, string $locale = null)
	{
		// Strip any formatting
		$num = 0 + str_replace(',', '', $num);

		// Can't work with non-numbers...
		if ( ! is_numeric($num))
		{
			return false;
		}

		if ($num >= 1000000000000)
		{
			$num = round($num / 1099511627776, $precision);
			$unit = lang('Number.terabyteAbbr');
		}
		elseif ($num >= 1000000000)
		{
			$num = round($num / 1073741824, $precision);
			$unit = lang('Number.gigabyteAbbr');
		}
		elseif ($num >= 1000000)
		{
			$num = round($num / 1048576, $precision);
			$unit = lang('Number.megabyteAbbr');
		}
		elseif ($num >= 1000)
		{
			$num = round($num / 1024, $precision);
			$unit = lang('Number.kilobyteAbbr');
		}
		else
		{
			$unit = lang('Number.bytes');
		}

		return format_number($num, $precision, $locale, ['after' => ' ' . $unit]);
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('number_to_amount'))
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
	 * @param             $num
	 * @param int         $precision
	 * @param string|null $locale
	 *
	 * @return bool|string
	 */
	function number_to_amount($num, int $precision = 0, string $locale = null)
	{
		// Strip any formatting
		$num = 0 + str_replace(',', '', $num);

		// Can't work with non-numbers...
		if ( ! is_numeric($num))
		{
			return false;
		}

		$suffix = '';

		if ($num > 1000000000000000)
		{
			$suffix = lang('Number.quadrillion');
			$num = round(($num / 1000000000000000), $precision);
		}
		elseif ($num > 1000000000000)
		{
			$suffix = lang('Number.trillion');
			$num = round(($num / 1000000000000), $precision);
		}
		else if ($num > 1000000000)
		{
			$suffix = lang('Number.billion');
			$num = round(($num / 1000000000), $precision);
		}
		else if ($num > 1000000)
		{
			$suffix = lang('Number.million');
			$num = round(($num / 1000000), $precision);
		}
		else if ($num > 1000)
		{
			$suffix = lang('Number.thousand');
			$num = round(($num / 1000), $precision);
		}

		return format_number($num, $precision, $locale, ['after' => $suffix]);
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('number_to_currency'))
{

	function number_to_currency($num, string $currency, string $locale = null)
	{
		return format_number($num, 1, $locale, [
			'type'		 => NumberFormatter::CURRENCY,
			'currency'	 => $currency
		]);
	}

}

//--------------------------------------------------------------------

if ( ! function_exists('format_number'))
{

	/**
	 * A general purpose, locale-aware, number_format method.
	 * Used by all of the functions of the number_helper.
	 *
	 * @param             $num
	 * @param int         $precision
	 * @param string|null $locale
	 * @param array       $options
	 *
	 * @return string
	 */
	function format_number($num, int $precision = 1, string $locale = null, array $options = [])
	{
		// Locale is either passed in here, negotiated with client, or grabbed from our config file.
		$locale = $locale ?? \CodeIgniter\Config\Services::request()->getLocale();

		// Type can be any of the NumberFormatter options, but provide a default.
		$type = isset($options['type']) ? (int) $options['type'] :
				NumberFormatter::DECIMAL;

		// In order to specify a precision, we'll have to modify
		// the pattern used by NumberFormatter.
		$pattern = '#,##0.' . str_repeat('#', $precision);

		$formatter = new NumberFormatter($locale, $type);

		// Try to format it per the locale
		if ($type == NumberFormatter::CURRENCY)
		{
			$output = $formatter->formatCurrency($num, $options['currency']);
		}
		else
		{
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
