<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use Config\Services;
use NumberFormatter;

/**
 * View filters
 */
class Filters
{
    /**
     * Returns $value as all lowercase with the first letter capitalized.
     *
     * @param string $value
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
     * @param mixed  $value
     * @param string $format
     *
     * @return string
     */
    public static function date($value, string $format): string
    {
        if (is_string($value) && ! is_numeric($value)) {
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
     * @param string $value
     * @param string $adjustment
     *
     * @return false|int
     */
    public static function date_modify($value, string $adjustment)
    {
        $value = static::date($value, 'Y-m-d H:i:s');

        return strtotime($adjustment, strtotime($value));
    }

    //--------------------------------------------------------------------

    /**
     * Returns the given default value if $value is empty or undefined.
     *
     * @param mixed  $value
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
     * @param string $value
     * @param string $context
     *
     * @return string
     */
    public static function esc($value, string $context = 'html'): string
    {
        return esc($value, $context);
    }

    //--------------------------------------------------------------------

    /**
     * Returns an excerpt of the given string.
     *
     * @param string $value
     * @param string $phrase
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
     * @param string $value
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
     * Limits the number of characters to $limit, and trails of with an ellipsis.
     * Will break at word break so may be more or less than $limit.
     *
     * @param string $value
     * @param int    $limit
     *
     * @return string
     */
    public static function limit_chars($value, int $limit = 500): string
    {
        helper('text');

        return character_limiter($value, $limit);
    }

    //--------------------------------------------------------------------

    /**
     * Limits the number of words to $limit, and trails of with an ellipsis.
     *
     * @param string $value
     * @param int    $limit
     *
     * @return string
     */
    public static function limit_words($value, int $limit = 100): string
    {
        helper('text');

        return word_limiter($value, $limit);
    }

    //--------------------------------------------------------------------

    /**
     * Returns the $value displayed in a localized manner.
     *
     * @param float|int   $value
     * @param int         $precision
     * @param string      $type
     * @param string|null $locale
     *
     * @return string
     */
    public static function local_number($value, string $type = 'decimal', int $precision = 4, ?string $locale = null): string
    {
        helper('number');

        $types = [
            'decimal'    => NumberFormatter::DECIMAL,
            'currency'   => NumberFormatter::CURRENCY,
            'percent'    => NumberFormatter::PERCENT,
            'scientific' => NumberFormatter::SCIENTIFIC,
            'spellout'   => NumberFormatter::SPELLOUT,
            'ordinal'    => NumberFormatter::ORDINAL,
            'duration'   => NumberFormatter::DURATION,
        ];

        return format_number($value, $precision, $locale, ['type' => $types[$type]]);
    }

    //--------------------------------------------------------------------

    /**
     * Returns the $value displayed as a currency string.
     *
     * @param float|int   $value
     * @param string      $currency
     * @param string|null $locale
     * @param int         $fraction
     *
     * @return string
     */
    public static function local_currency($value, string $currency, ?string $locale = null, $fraction = null): string
    {
        helper('number');

        $options = [
            'type'     => NumberFormatter::CURRENCY,
            'currency' => $currency,
            'fraction' => $fraction,
        ];

        return format_number($value, 2, $locale, $options);
    }

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
        $typography = Services::typography();

        return $typography->nl2brExceptPre($value);
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
        $typography = Services::typography();

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
     * @param mixed  $precision
     * @param string $type
     *
     * @return float|string
     */
    public static function round(string $value, $precision = 2, string $type = 'common')
    {
        if (! is_numeric($precision)) {
            $type      = $precision;
            $precision = 2;
        }

        switch ($type) {
            case 'common':
                return round((float) $value, $precision);

            case 'ceil':
                return ceil((float) $value);

            case 'floor':
                return floor((float) $value);
        }

        // Still here, just return the value.
        return $value;
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
}
