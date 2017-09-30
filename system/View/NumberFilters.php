<?php namespace CodeIgniter\View;

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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

class NumberFilters
{
	/**
	 * describe method
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function localizedNumber($number, $style = 'decimal', $type = 'default', $locale = null):string
	{
		self::raiseExceptionIfIntlExtensionNotAvailable();
		static $typeValues = [
			'default'  => \NumberFormatter::TYPE_DEFAULT,
			'int32'    => \NumberFormatter::TYPE_INT32,
			'int64'    => \NumberFormatter::TYPE_INT64,
			'double'   => \NumberFormatter::TYPE_DOUBLE,
			'currency' => \NumberFormatter::TYPE_CURRENCY,
		];

			$formatter = self::getNumberFormatter($locale, $style);
		if (! isset($typeValues[$type]))
		{
			throw new \Exception('syntax error');
			//todo: raise mor appropriate /specific exception
		}
		return $formatter->format($number, $typeValues[$type]);
	}

	/**
	 * describe method
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function localizedCurrency($number, $currency = null, $locale = null)
	{
		$locale = $locale ?? \CodeIgniter\Config\Services::request()->getLocale();

			$formatter = self::getNumberFormatter($locale, 'currency');
		return $formatter->formatCurrency($number, $currency);
	}
	//--------------------------------------------------------------------

	/**
	 * describe method
	 *
	 * @param $value
	 *
	 * @return string
	 */
	private static function getNumberFormatter($locale, $style):\NumberFormatter
	{
		static $formatter, $currentStyle;

		$locale = $locale ?? \CodeIgniter\Config\Services::request()->getLocale();
log_message('info',\CodeIgniter\Config\Services::request()->getLocale());
		if ($formatter && $formatter->getLocale() === $locale && $currentStyle === $style)
		{
			// Return same instance of NumberFormatter if parameters are the same
			// to those in previous call
			return $formatter;
		}
		static $styleValues = [
			'decimal'    => \NumberFormatter::DECIMAL,
			'currency'   => \NumberFormatter::CURRENCY,
			'percent'    => \NumberFormatter::PERCENT,
			'scientific' => \NumberFormatter::SCIENTIFIC,
			'spellout'   => \NumberFormatter::SPELLOUT,
			'ordinal'    => \NumberFormatter::ORDINAL,
			'duration'   => \NumberFormatter::DURATION,
		];
		if (! isset($styleValues[$style]))
		{
			throw new \Exception('syntax error');
			//todo: raise more appropriate /specific exception
		}
		$currentStyle = $style;
		$formatter    = \NumberFormatter::create($locale, $styleValues[$style]);
		return $formatter;
	}
	//--------------------------------------------------------------------

	/**
	 * describe metho
	 */
	private static function raiseExceptionIfIntlExtensionNotAvailable()
	{
		if (! class_exists('IntlDateFormatter'))
		{
			throw new \RuntimeException('The intl extension is needed to use intl-based filters.');
		}
	}
	//--------------------------------------------------------------------

}
