<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// CodeIgniter Number Helpers

if (! function_exists('number_to_size')) {
    /**
     * Formats a numbers as bytes, based on size, and adds the appropriate suffix
     *
     * @param int|string $num Will be cast as int
     *
     * @return bool|string
     */
    function number_to_size($num, int $precision = 1, ?string $locale = null)
    {
        // Strip any formatting & ensure numeric input
        try {
            // @phpstan-ignore-next-line
            $num = 0 + str_replace(',', '', $num);
        } catch (ErrorException $ee) {
            // Catch "Warning:  A non-numeric value encountered"
            return false;
        }

        // ignore sub part
        $generalLocale = $locale;
        if (! empty($locale) && ($underscorePos = strpos($locale, '_'))) {
            $generalLocale = substr($locale, 0, $underscorePos);
        }

        if ($num >= 1_000_000_000_000) {
            $num  = round($num / 1_099_511_627_776, $precision);
            $unit = lang('Number.terabyteAbbr', [], $generalLocale);
        } elseif ($num >= 1_000_000_000) {
            $num  = round($num / 1_073_741_824, $precision);
            $unit = lang('Number.gigabyteAbbr', [], $generalLocale);
        } elseif ($num >= 1_000_000) {
            $num  = round($num / 1_048_576, $precision);
            $unit = lang('Number.megabyteAbbr', [], $generalLocale);
        } elseif ($num >= 1000) {
            $num  = round($num / 1024, $precision);
            $unit = lang('Number.kilobyteAbbr', [], $generalLocale);
        } else {
            $unit = lang('Number.bytes', [], $generalLocale);
        }

        return format_number($num, $precision, $locale, ['after' => ' ' . $unit]);
    }
}

if (! function_exists('number_to_amount')) {
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
     * @param int|string  $num       Will be cast as int
     * @param int         $precision [optional] The optional number of decimal digits to round to.
     * @param string|null $locale    [optional]
     *
     * @return bool|string
     */
    function number_to_amount($num, int $precision = 0, ?string $locale = null)
    {
        // Strip any formatting & ensure numeric input
        try {
            // @phpstan-ignore-next-line
            $num = 0 + str_replace(',', '', $num);
        } catch (ErrorException $ee) {
            return false;
        }

        $suffix = '';

        // ignore sub part
        $generalLocale = $locale;
        if (! empty($locale) && ($underscorePos = strpos($locale, '_'))) {
            $generalLocale = substr($locale, 0, $underscorePos);
        }

        if ($num >= 1_000_000_000_000_000) {
            $suffix = lang('Number.quadrillion', [], $generalLocale);
            $num    = round(($num / 1_000_000_000_000_000), $precision);
        } elseif ($num >= 1_000_000_000_000) {
            $suffix = lang('Number.trillion', [], $generalLocale);
            $num    = round(($num / 1_000_000_000_000), $precision);
        } elseif ($num >= 1_000_000_000) {
            $suffix = lang('Number.billion', [], $generalLocale);
            $num    = round(($num / 1_000_000_000), $precision);
        } elseif ($num >= 1_000_000) {
            $suffix = lang('Number.million', [], $generalLocale);
            $num    = round(($num / 1_000_000), $precision);
        } elseif ($num >= 1000) {
            $suffix = lang('Number.thousand', [], $generalLocale);
            $num    = round(($num / 1000), $precision);
        }

        return format_number($num, $precision, $locale, ['after' => $suffix]);
    }
}

if (! function_exists('number_to_currency')) {
    function number_to_currency(float $num, string $currency, ?string $locale = null, int $fraction = 0): string
    {
        return format_number($num, 1, $locale, [
            'type'     => NumberFormatter::CURRENCY,
            'currency' => $currency,
            'fraction' => $fraction,
        ]);
    }
}

if (! function_exists('format_number')) {
    /**
     * A general purpose, locale-aware, number_format method.
     * Used by all of the functions of the number_helper.
     */
    function format_number(float $num, int $precision = 1, ?string $locale = null, array $options = []): string
    {
        // If locale is not passed, get from the default locale that is set from our config file
        // or set by HTTP content negotiation.
        $locale ??= Locale::getDefault();

        // Type can be any of the NumberFormatter options, but provide a default.
        $type = (int) ($options['type'] ?? NumberFormatter::DECIMAL);

        $formatter = new NumberFormatter($locale, $type);

        // Try to format it per the locale
        if ($type === NumberFormatter::CURRENCY) {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $options['fraction']);
            $output = $formatter->formatCurrency($num, $options['currency']);
        } else {
            // In order to specify a precision, we'll have to modify
            // the pattern used by NumberFormatter.
            $pattern = '#,##0.' . str_repeat('#', $precision);

            $formatter->setPattern($pattern);
            $output = $formatter->format($num);
        }

        // This might lead a trailing period if $precision == 0
        $output = trim($output, '. ');

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new BadFunctionCallException($formatter->getErrorMessage());
        }

        // Add on any before/after text.
        if (isset($options['before']) && is_string($options['before'])) {
            $output = $options['before'] . $output;
        }

        if (isset($options['after']) && is_string($options['after'])) {
            $output .= $options['after'];
        }

        return $output;
    }
}

if (! function_exists('number_to_roman')) {
    /**
     * Convert a number to a roman numeral.
     *
     * @param int|string $num it will convert to int
     */
    function number_to_roman($num): ?string
    {
        static $map = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1,
        ];

        $num = (int) $num;

        if ($num < 1 || $num > 3999) {
            return null;
        }

        $result = '';

        foreach ($map as $roman => $arabic) {
            $repeat = (int) floor($num / $arabic);
            $result .= str_repeat($roman, $repeat);
            $num %= $arabic;
        }

        return $result;
    }
}
