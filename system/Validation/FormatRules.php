<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use DateTime;

/**
 * Format validation Rules.
 *
 * @see \CodeIgniter\Validation\FormatRulesTest
 */
class FormatRules
{
    /**
     * Alpha
     */
    public function alpha(?string $str = null): bool
    {
        return ctype_alpha($str ?? '');
    }

    /**
     * Alpha with spaces.
     *
     * @param string|null $value Value.
     *
     * @return bool True if alpha with spaces, else false.
     */
    public function alpha_space(?string $value = null): bool
    {
        if ($value === null) {
            return true;
        }

        // @see https://regex101.com/r/LhqHPO/1
        return (bool) preg_match('/\A[A-Z ]+\z/i', $value);
    }

    /**
     * Alphanumeric with underscores and dashes
     *
     * @see https://regex101.com/r/XfVY3d/1
     */
    public function alpha_dash(?string $str = null): bool
    {
        if ($str === null) {
            return false;
        }

        return preg_match('/\A[a-z0-9_-]+\z/i', $str) === 1;
    }

    /**
     * Alphanumeric, spaces, and a limited set of punctuation characters.
     * Accepted punctuation characters are: ~ tilde, ! exclamation,
     * # number, $ dollar, % percent, & ampersand, * asterisk, - dash,
     * _ underscore, + plus, = equals, | vertical bar, : colon, . period
     * ~ ! # $ % & * - _ + = | : .
     *
     * @param string|null $str
     *
     * @return bool
     *
     * @see https://regex101.com/r/6N8dDY/1
     */
    public function alpha_numeric_punct($str)
    {
        if ($str === null) {
            return false;
        }

        return preg_match('/\A[A-Z0-9 ~!#$%\&\*\-_+=|:.]+\z/i', $str) === 1;
    }

    /**
     * Alphanumeric
     */
    public function alpha_numeric(?string $str = null): bool
    {
        return ctype_alnum($str ?? '');
    }

    /**
     * Alphanumeric w/ spaces
     */
    public function alpha_numeric_space(?string $str = null): bool
    {
        // @see https://regex101.com/r/0AZDME/1
        return (bool) preg_match('/\A[A-Z0-9 ]+\z/i', $str ?? '');
    }

    /**
     * Any type of string
     *
     * Note: we specifically do NOT type hint $str here so that
     * it doesn't convert numbers into strings.
     *
     * @param string|null $str
     */
    public function string($str = null): bool
    {
        return is_string($str);
    }

    /**
     * Decimal number
     */
    public function decimal(?string $str = null): bool
    {
        // @see https://regex101.com/r/HULifl/2/
        return (bool) preg_match('/\A[-+]?\d{0,}\.?\d+\z/', $str ?? '');
    }

    /**
     * String of hexidecimal characters
     */
    public function hex(?string $str = null): bool
    {
        return ctype_xdigit($str ?? '');
    }

    /**
     * Integer
     */
    public function integer(?string $str = null): bool
    {
        return (bool) preg_match('/\A[\-+]?\d+\z/', $str ?? '');
    }

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     */
    public function is_natural(?string $str = null): bool
    {
        return ctype_digit($str ?? '');
    }

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     */
    public function is_natural_no_zero(?string $str = null): bool
    {
        return $str !== '0' && ctype_digit($str ?? '');
    }

    /**
     * Numeric
     */
    public function numeric(?string $str = null): bool
    {
        // @see https://regex101.com/r/bb9wtr/2
        return (bool) preg_match('/\A[\-+]?\d*\.?\d+\z/', $str ?? '');
    }

    /**
     * Compares value against a regular expression pattern.
     */
    public function regex_match(?string $str, string $pattern): bool
    {
        if (strpos($pattern, '/') !== 0) {
            $pattern = "/{$pattern}/";
        }

        return (bool) preg_match($pattern, $str ?? '');
    }

    /**
     * Validates that the string is a valid timezone as per the
     * timezone_identifiers_list function.
     *
     * @see http://php.net/manual/en/datetimezone.listidentifiers.php
     */
    public function timezone(?string $str = null): bool
    {
        return in_array($str ?? '', timezone_identifiers_list(), true);
    }

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     */
    public function valid_base64(?string $str = null): bool
    {
        if ($str === null) {
            return false;
        }

        return base64_encode(base64_decode($str, true)) === $str;
    }

    /**
     * Valid JSON
     */
    public function valid_json(?string $str = null): bool
    {
        json_decode($str ?? '');

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Checks for a correctly formatted email address
     */
    public function valid_email(?string $str = null): bool
    {
        // @see https://regex101.com/r/wlJG1t/1/
        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && preg_match('#\A([^@]+)@(.+)\z#', $str ?? '', $matches)) {
            $str = $matches[1] . '@' . idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
        }

        return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate a comma-separated list of email addresses.
     *
     * Example:
     *     valid_emails[one@example.com,two@example.com]
     */
    public function valid_emails(?string $str = null): bool
    {
        foreach (explode(',', $str ?? '') as $email) {
            $email = trim($email);

            if ($email === '') {
                return false;
            }

            if ($this->valid_email($email) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate an IP address (human readable format or binary string - inet_pton)
     *
     * @param string|null $which IP protocol: 'ipv4' or 'ipv6'
     */
    public function valid_ip(?string $ip = null, ?string $which = null): bool
    {
        if ($ip === null || $ip === '') {
            return false;
        }

        switch (strtolower($which ?? '')) {
            case 'ipv4':
                $option = FILTER_FLAG_IPV4;
                break;

            case 'ipv6':
                $option = FILTER_FLAG_IPV6;
                break;

            default:
                $option = 0;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, $option) !== false
            || (! ctype_print($ip) && filter_var(inet_ntop($ip), FILTER_VALIDATE_IP, $option) !== false);
    }

    /**
     * Checks a string to ensure it is (loosely) a URL.
     *
     * Warning: this rule will pass basic strings like
     * "banana"; use valid_url_strict for a stricter rule.
     */
    public function valid_url(?string $str = null): bool
    {
        if ($str === null || $str === '') {
            return false;
        }

        if (preg_match('/\A(?:([^:]*)\:)?\/\/(.+)\z/', $str, $matches)) {
            if (! in_array($matches[1], ['http', 'https'], true)) {
                return false;
            }

            $str = $matches[2];
        }

        $str = 'http://' . $str;

        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Checks a URL to ensure it's formed correctly.
     *
     * @param string|null $validSchemes comma separated list of allowed schemes
     */
    public function valid_url_strict(?string $str = null, ?string $validSchemes = null): bool
    {
        if ($str === null || $str === '' || $str === '0') {
            return false;
        }

        // parse_url() may return null and false
        $scheme       = strtolower((string) parse_url($str, PHP_URL_SCHEME));
        $validSchemes = explode(
            ',',
            strtolower($validSchemes ?? 'http,https')
        );

        return in_array($scheme, $validSchemes, true)
            && filter_var($str, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Checks for a valid date and matches a given date format
     *
     * @param non-empty-string|null $format
     */
    public function valid_date(?string $str = null, ?string $format = null): bool
    {
        if ($str === null) {
            return false;
        }

        if ($format === null || $format === '') {
            return strtotime($str) !== false;
        }

        $date   = DateTime::createFromFormat($format, $str);
        $errors = DateTime::getLastErrors();

        if ($date === false) {
            return false;
        }

        // PHP 8.2 or later.
        if ($errors === false) {
            return true;
        }

        return $errors['warning_count'] === 0 && $errors['error_count'] === 0;
    }
}
