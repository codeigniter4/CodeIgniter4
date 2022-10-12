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

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Validation\FormatRules as NonStrictFormatRules;

/**
 * Format validation Rules.
 */
class FormatRules
{
    private NonStrictFormatRules $nonStrictFormatRules;

    public function __construct()
    {
        $this->nonStrictFormatRules = new NonStrictFormatRules();
    }

    /**
     * Alpha
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function alpha($str = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->alpha($str);
    }

    /**
     * Alpha with spaces.
     *
     * @param array|bool|float|int|object|string|null $value Value.
     *
     * @return bool True if alpha with spaces, else false.
     */
    public function alpha_space($value = null): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return $this->nonStrictFormatRules->alpha_space($value);
    }

    /**
     * Alphanumeric with underscores and dashes
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function alpha_dash($str = null): bool
    {
        if (is_int($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->alpha_dash($str);
    }

    /**
     * Alphanumeric, spaces, and a limited set of punctuation characters.
     * Accepted punctuation characters are: ~ tilde, ! exclamation,
     * # number, $ dollar, % percent, & ampersand, * asterisk, - dash,
     * _ underscore, + plus, = equals, | vertical bar, : colon, . period
     * ~ ! # $ % & * - _ + = | : .
     *
     * @param array|bool|float|int|object|string|null $str
     *
     * @return bool
     */
    public function alpha_numeric_punct($str)
    {
        if (is_int($str) || is_float($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->alpha_numeric_punct($str);
    }

    /**
     * Alphanumeric
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function alpha_numeric($str = null): bool
    {
        if (is_int($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->alpha_numeric($str);
    }

    /**
     * Alphanumeric w/ spaces
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function alpha_numeric_space($str = null): bool
    {
        if (is_int($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->alpha_numeric_space($str);
    }

    /**
     * Any type of string
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function string($str = null): bool
    {
        return $this->nonStrictFormatRules->string($str);
    }

    /**
     * Decimal number
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function decimal($str = null): bool
    {
        if (is_int($str) || is_float($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->decimal($str);
    }

    /**
     * String of hexidecimal characters
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function hex($str = null): bool
    {
        if (is_int($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->hex($str);
    }

    /**
     * Integer
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function integer($str = null): bool
    {
        if (is_int($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->integer($str);
    }

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function is_natural($str = null): bool
    {
        if (is_int($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->is_natural($str);
    }

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function is_natural_no_zero($str = null): bool
    {
        if (is_int($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->is_natural_no_zero($str);
    }

    /**
     * Numeric
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function numeric($str = null): bool
    {
        if (is_int($str) || is_float($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->numeric($str);
    }

    /**
     * Compares value against a regular expression pattern.
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function regex_match($str, string $pattern): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->regex_match($str, $pattern);
    }

    /**
     * Validates that the string is a valid timezone as per the
     * timezone_identifiers_list function.
     *
     * @see http://php.net/manual/en/datetimezone.listidentifiers.php
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function timezone($str = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->timezone($str);
    }

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function valid_base64($str = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_base64($str);
    }

    /**
     * Valid JSON
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function valid_json($str = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_json($str);
    }

    /**
     * Checks for a correctly formatted email address
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function valid_email($str = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_email($str);
    }

    /**
     * Validate a comma-separated list of email addresses.
     *
     * Example:
     *     valid_emails[one@example.com,two@example.com]
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function valid_emails($str = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_emails($str);
    }

    /**
     * Validate an IP address (human readable format or binary string - inet_pton)
     *
     * @param array|bool|float|int|object|string|null $ip
     * @param string|null                             $which IP protocol: 'ipv4' or 'ipv6'
     */
    public function valid_ip($ip = null, ?string $which = null): bool
    {
        if (! is_string($ip)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_ip($ip, $which);
    }

    /**
     * Checks a string to ensure it is (loosely) a URL.
     *
     * Warning: this rule will pass basic strings like
     * "banana"; use valid_url_strict for a stricter rule.
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function valid_url($str = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_url($str);
    }

    /**
     * Checks a URL to ensure it's formed correctly.
     *
     * @param array|bool|float|int|object|string|null $str
     * @param string|null                             $validSchemes comma separated list of allowed schemes
     */
    public function valid_url_strict($str = null, ?string $validSchemes = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_url_strict($str, $validSchemes);
    }

    /**
     * Checks for a valid date and matches a given date format
     *
     * @param array|bool|float|int|object|string|null $str
     */
    public function valid_date($str = null, ?string $format = null): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictFormatRules->valid_date($str, $format);
    }
}
