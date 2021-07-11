<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CodeIgniter Date Helpers
 */

if (! function_exists('now')) {
    /**
     * Get "now" time
     *
     * Returns time() based on the timezone parameter or on the
     * app_timezone() setting
     *
     * @param string $timezone
     *
     * @throws Exception
     *
     * @return int
     */
    function now(?string $timezone = null): int
    {
        $timezone = empty($timezone) ? app_timezone() : $timezone;

        if ($timezone === 'local' || $timezone === date_default_timezone_get()) {
            return time();
        }

        $datetime = new DateTime('now', new DateTimeZone($timezone));
        sscanf($datetime->format('j-n-Y G:i:s'), '%d-%d-%d %d:%d:%d', $day, $month, $year, $hour, $minute, $second);

        return mktime($hour, $minute, $second, $month, $day, $year);
    }
}

if (! function_exists('timezone_select')) {
    /**
     * Generates a select field of all available timezones
     *
     * Returns a string with the formatted HTML
     *
     * @param string $class   Optional class to apply to the select field
     * @param string $default Default value for initial selection
     * @param int    $what    One of the DateTimeZone class constants (for listIdentifiers)
     * @param string $country A two-letter ISO 3166-1 compatible country code (for listIdentifiers)
     *
     * @throws Exception
     *
     * @return string
     */
    function timezone_select(string $class = '', string $default = '', int $what = DateTimeZone::ALL, ?string $country = null): string
    {
        $timezones = DateTimeZone::listIdentifiers($what, $country);

        $buffer = "<select name='timezone' class='{$class}'>" . PHP_EOL;

        foreach ($timezones as $timezone) {
            $selected = ($timezone === $default) ? 'selected' : '';
            $buffer .= "<option value='{$timezone}' {$selected}>{$timezone}</option>" . PHP_EOL;
        }

        return $buffer . ('</select>' . PHP_EOL);
    }
}
