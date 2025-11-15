<?php

declare(strict_types=1);

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
     *
     * @param string|null $str
     */
    public function alpha($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        return ctype_alpha($str);
    }

    /**
     * Alpha with spaces.
     *
     * @param string|null $value Value.
     *
     * @return bool True if alpha with spaces, else false.
     */
    public function alpha_space($value = null): bool
    {
        if ($value === null) {
            return true;
        }

        if (! is_string($value)) {
            $value = (string) $value;
        }

        // @see https://regex101.com/r/LhqHPO/1
        return (bool) preg_match('/\A[A-Z ]+\z/i', $value);
    }

    /**
     * Alphanumeric with underscores and dashes
     *
     * @see https://regex101.com/r/XfVY3d/1
     *
     * @param string|null $str
     */
    public function alpha_dash($str = null): bool
    {
        if ($str === null) {
            return false;
        }

        if (! is_string($str)) {
            $str = (string) $str;
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

        if (! is_string($str)) {
            $str = (string) $str;
        }

        return preg_match('/\A[A-Z0-9 ~!#$%\&\*\-_+=|:.]+\z/i', $str) === 1;
    }

    /**
     * Alphanumeric
     *
     * @param string|null $str
     */
    public function alpha_numeric($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        return ctype_alnum($str);
    }

    /**
     * Alphanumeric w/ spaces
     *
     * @param string|null $str
     */
    public function alpha_numeric_space($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        // @see https://regex101.com/r/0AZDME/1
        return (bool) preg_match('/\A[A-Z0-9 ]+\z/i', $str);
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
     *
     * @param string|null $str
     */
    public function decimal($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        // @see https://regex101.com/r/HULifl/2/
        return (bool) preg_match('/\A[-+]?\d{0,}\.?\d+\z/', $str);
    }

    /**
     * String of hexidecimal characters
     *
     * @param string|null $str
     */
    public function hex($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        return ctype_xdigit($str);
    }

    /**
     * Integer
     *
     * @param string|null $str
     */
    public function integer($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        return (bool) preg_match('/\A[\-+]?\d+\z/', $str);
    }

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @param string|null $str
     */
    public function is_natural($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        return ctype_digit($str);
    }

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @param string|null $str
     */
    public function is_natural_no_zero($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        return $str !== '0' && ctype_digit($str);
    }

    /**
     * Numeric
     *
     * @param string|null $str
     */
    public function numeric($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        // @see https://regex101.com/r/bb9wtr/2
        return (bool) preg_match('/\A[\-+]?\d*\.?\d+\z/', $str);
    }

    /**
     * Compares value against a regular expression pattern.
     *
     * @param string|null $str
     */
    public function regex_match($str, string $pattern): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        if (! str_starts_with($pattern, '/')) {
            $pattern = "/{$pattern}/";
        }

        return (bool) preg_match($pattern, $str);
    }

    /**
     * Validates that the string is a valid timezone as per the
     * timezone_identifiers_list function.
     *
     * @see http://php.net/manual/en/datetimezone.listidentifiers.php
     *
     * @param string|null $str
     */
    public function timezone($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        return in_array($str, timezone_identifiers_list(), true);
    }

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @param string|null $str
     */
    public function valid_base64($str = null): bool
    {
        if ($str === null) {
            return false;
        }

        if (! is_string($str)) {
            $str = (string) $str;
        }

        $decoded = base64_decode($str, true);

        if ($decoded === false) {
            return false;
        }

        return base64_encode($decoded) === $str;
    }

    /**
     * Valid JSON
     *
     * @param string|null $str
     */
    public function valid_json($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        json_decode($str);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Checks for a correctly formatted email address
     *
     * @param string|null $str
     */
    public function valid_email($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        // @see https://regex101.com/r/wlJG1t/1/
        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && preg_match('#\A([^@]+)@(.+)\z#', $str, $matches)) {
            $str = $matches[1] . '@' . idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
        }

        return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate a comma-separated list of email addresses.
     *
     * Example:
     *     valid_emails[one@example.com,two@example.com]
     *
     * @param string|null $str
     */
    public function valid_emails($str = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        foreach (explode(',', $str) as $email) {
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
     * @param string|null $ip
     * @param string|null $which IP protocol: 'ipv4' or 'ipv6'
     */
    public function valid_ip($ip = null, ?string $which = null): bool
    {
        if (! is_string($ip)) {
            $ip = (string) $ip;
        }

        if ($ip === '') {
            return false;
        }

        $option = match (strtolower($which ?? '')) {
            'ipv4'  => FILTER_FLAG_IPV4,
            'ipv6'  => FILTER_FLAG_IPV6,
            default => 0,
        };

        return filter_var($ip, FILTER_VALIDATE_IP, $option) !== false
            || (! ctype_print($ip) && filter_var(inet_ntop($ip), FILTER_VALIDATE_IP, $option) !== false);
    }

    /**
     * Checks a string to ensure it is (loosely) a URL.
     *
     * Warning: this rule will pass basic strings like
     * "banana"; use valid_url_strict for a stricter rule.
     *
     * @param string|null $str
     */
    public function valid_url($str = null): bool
    {
        if ($str === null || $str === '') {
            return false;
        }

        if (! is_string($str)) {
            $str = (string) $str;
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
     * @param string|null $str
     * @param string|null $validSchemes comma separated list of allowed schemes
     */
    public function valid_url_strict($str = null, ?string $validSchemes = null): bool
    {
        if (in_array($str, [null, '', '0'], true)) {
            return false;
        }

        if (! is_string($str)) {
            $str = (string) $str;
        }

        // parse_url() may return null and false
        $scheme       = strtolower((string) parse_url($str, PHP_URL_SCHEME));
        $validSchemes = explode(
            ',',
            strtolower($validSchemes ?? 'http,https'),
        );

        return in_array($scheme, $validSchemes, true)
            && filter_var($str, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Checks for a valid date and matches a given date format
     *
     * @param string|null           $str
     * @param non-empty-string|null $format
     */
    public function valid_date($str = null, ?string $format = null): bool
    {
        if (! is_string($str)) {
            $str = (string) $str;
        }

        if ($str === '') {
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
