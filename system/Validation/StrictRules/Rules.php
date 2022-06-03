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

use CodeIgniter\Validation\Rules as NonStrictRules;
use Config\Database;

/**
 * Validation Rules.
 */
class Rules
{
    private NonStrictRules $nonStrictRules;

    public function __construct()
    {
        $this->nonStrictRules = new NonStrictRules();
    }

    /**
     * The value does not match another field in $data.
     *
     * @param mixed $str
     * @param array $data Other field/value pairs
     */
    public function differs($str, string $field, array $data): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictRules->differs($str, $field, $data);
    }

    /**
     * Equals the static value provided.
     *
     * @param mixed $str
     */
    public function equals($str, string $val): bool
    {
        return $this->nonStrictRules->equals($str, $val);
    }

    /**
     * Returns true if $str is $val characters long.
     * $val = "5" (one) | "5,8,12" (multiple values)
     *
     * @param mixed $str
     */
    public function exact_length($str, string $val): bool
    {
        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictRules->exact_length($str, $val);
    }

    /**
     * Greater than
     *
     * @param mixed $str
     */
    public function greater_than($str, string $min): bool
    {
        return $this->nonStrictRules->greater_than($str, $min);
    }

    /**
     * Equal to or Greater than
     *
     * @param mixed $str
     */
    public function greater_than_equal_to($str, string $min): bool
    {
        return $this->nonStrictRules->greater_than_equal_to($str, $min);
    }

    /**
     * Checks the database to see if the given value exist.
     * Can ignore records by field/value to filter (currently
     * accept only one filter).
     *
     * Example:
     *    is_not_unique[table.field,where_field,where_value]
     *    is_not_unique[menu.id,active,1]
     *
     * @param mixed $str
     */
    public function is_not_unique($str, string $field, array $data): bool
    {
        return $this->nonStrictRules->is_not_unique($str, $field, $data);
    }

    /**
     * Value should be within an array of values
     *
     * @param mixed $value
     */
    public function in_list($value, string $list): bool
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return false;
        }

        return $this->nonStrictRules->in_list($value, $list);
    }

    /**
     * Checks the database to see if the given value is unique. Can
     * ignore a single record by field/value to make it useful during
     * record updates.
     *
     * Example:
     *    is_unique[table.field,ignore_field,ignore_value]
     *    is_unique[users.email,id,5]
     *
     * @param mixed $str
     */
    public function is_unique($str, string $field, array $data): bool
    {
        return $this->nonStrictRules->is_unique($str, $field, $data);
    }

    /**
     * Less than
     *
     * @param mixed $str
     */
    public function less_than($str, string $max): bool
    {
        return $this->nonStrictRules->less_than($str, $max);
    }

    /**
     * Equal to or Less than
     *
     * @param mixed $str
     */
    public function less_than_equal_to($str, string $max): bool
    {
        return $this->nonStrictRules->less_than_equal_to($str, $max);
    }

    /**
     * Matches the value of another field in $data.
     *
     * @param mixed $str
     * @param array $data Other field/value pairs
     */
    public function matches($str, string $field, array $data): bool
    {
        return $this->nonStrictRules->matches($str, $field, $data);
    }

    /**
     * Returns true if $str is $val or fewer characters in length.
     *
     * @param mixed $str
     */
    public function max_length($str, string $val): bool
    {
        if (is_int($str) || is_float($str) || null === $str) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictRules->max_length($str, $val);
    }

    /**
     * Returns true if $str is at least $val length.
     *
     * @param mixed $str
     */
    public function min_length($str, string $val): bool
    {
        if (is_int($str) || is_float($str)) {
            $str = (string) $str;
        }

        if (! is_string($str)) {
            return false;
        }

        return $this->nonStrictRules->min_length($str, $val);
    }

    /**
     * Does not equal the static value provided.
     *
     * @param mixed $str
     */
    public function not_equals($str, string $val): bool
    {
        return $this->nonStrictRules->not_equals($str, $val);
    }

    /**
     * Value should not be within an array of values.
     *
     * @param mixed $value
     */
    public function not_in_list($value, string $list): bool
    {
        if (null === $value) {
            return true;
        }

        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return false;
        }

        return $this->nonStrictRules->not_in_list($value, $list);
    }

    /**
     * @param mixed $str
     */
    public function required($str = null): bool
    {
        return $this->nonStrictRules->required($str);
    }

    /**
     * The field is required when any of the other required fields are present
     * in the data.
     *
     * Example (field is required when the password field is present):
     *
     *     required_with[password]
     *
     * @param mixed       $str
     * @param string|null $fields List of fields that we should check if present
     * @param array       $data   Complete list of fields from the form
     */
    public function required_with($str = null, ?string $fields = null, array $data = []): bool
    {
        return $this->nonStrictRules->required_with($str, $fields, $data);
    }

    /**
     * The field is required when all of the other fields are present
     * in the data but not required.
     *
     * Example (field is required when the id or email field is missing):
     *
     *     required_without[id,email]
     *
     * @param mixed $str
     */
    public function required_without($str = null, ?string $fields = null, array $data = []): bool
    {
        return $this->nonStrictRules->required_without($str, $fields, $data);
    }
}
