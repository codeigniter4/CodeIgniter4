<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
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
     */
    public static function capitalize(string $value): string
    {
        return ucfirst(strtolower($value));
    }

    /**
     * Formats a date into the given $format.
     *
     * @param int|string|null $value
     */
    public static function date($value, string $format): string
    {
        if (is_string($value) && ! is_numeric($value)) {
            $value = strtotime($value);
        }

        return date($format, $value);
    }

    /**
     * Given a string or DateTime object, will return the date modified
     * by the given value. Returns the value as a unix timestamp
     *
     * Example:
     *      my_date|date_modify(+1 day)
     *
     * @param int|string|null $value
     *
     * @return false|int
     */
    public static function date_modify($value, string $adjustment)
    {
        $value = static::date($value, 'Y-m-d H:i:s');

        return strtotime($adjustment, strtotime($value));
    }

    /**
     * Returns the given default value if $value is empty or undefined.
     *
     * @param array|bool|float|int|object|resource|string|null $value
     */
    public static function default($value, string $default): string
    {
        return empty($value)
            ? $default
            : $value;
    }

    /**
     * Escapes the given value with our `esc()` helper function.
     *
     * @param string $value
     */
    public static function esc($value, string $context = 'html'): string
    {
        return esc($value, $context);
    }

    /**
     * Returns an excerpt of the given string.
     */
    public static function excerpt(string $value, string $phrase, int $radius = 100): string
    {
        helper('text');

        return excerpt($value, $phrase, $radius);
    }

    /**
     * Highlights a given phrase within the text using '<mark></mark>' tags.
     */
    public static function highlight(string $value, string $phrase): string
    {
        helper('text');

        return highlight_phrase($value, $phrase);
    }

    /**
     * Highlights code samples with HTML/CSS.
     *
     * @param string $value
     */
    public static function highlight_code($value): string
    {
        helper('text');

        return highlight_code($value);
    }

    /**
     * Limits the number of characters to $limit, and trails of with an ellipsis.
     * Will break at word break so may be more or less than $limit.
     *
     * @param string $value
     */
    public static function limit_chars($value, int $limit = 500): string
    {
        helper('text');

        return character_limiter($value, $limit);
    }

    /**
     * Limits the number of words to $limit, and trails of with an ellipsis.
     *
     * @param string $value
     */
    public static function limit_words($value, int $limit = 100): string
    {
        helper('text');

        return word_limiter($value, $limit);
    }

    /**
     * Returns the $value displayed in a localized manner.
     *
     * @param float|int $value
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

    /**
     * Returns the $value displayed as a currency string.
     *
     * @param float|int $value
     * @param int       $fraction
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
     */
    public static function nl2br(string $value): string
    {
        $typography = Services::typography();

        return $typography->nl2brExceptPre($value);
    }

    /**
     * Takes a body of text and uses the auto_typography() method to
     * turn it into prettier, easier-to-read, prose.
     */
    public static function prose(string $value): string
    {
        $typography = Services::typography();

        return $typography->autoTypography($value);
    }

    /**
     * Rounds a given $value in one of 3 ways;
     *
     *  - common    Normal rounding
     *  - ceil      always rounds up
     *  - floor     always rounds down
     *
     * @param int|string $precision precision or type
     *
     * @return float|string
     */
    public static function round(string $value, $precision = 2, string $type = 'common')
    {
        // In case that $precision is a type like `{ value1|round(ceil) }`
        if (! is_numeric($precision)) {
            $type      = $precision;
            $precision = 2;
        } else {
            $precision = (int) $precision;
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

    /**
     * Returns a "title case" version of the string.
     */
    public static function title(string $value): string
    {
        return ucwords(strtolower($value));
    }
}
