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

use Config\Database;
use InvalidArgumentException;

/**
 * Validation Rules.
 */
class Rules
{
    /**
     * The value does not match another field in $data.
     *
     * @param array $data Other field/value pairs
     */
    public function differs(?string $str, string $field, array $data): bool
    {
        if (strpos($field, '.') !== false) {
            return $str !== dot_array_search($field, $data);
        }

        return array_key_exists($field, $data) && $str !== $data[$field];
    }

    /**
     * Equals the static value provided.
     */
    public function equals(?string $str, string $val): bool
    {
        return $str === $val;
    }

    /**
     * Returns true if $str is $val characters long.
     * $val = "5" (one) | "5,8,12" (multiple values)
     */
    public function exact_length(?string $str, string $val): bool
    {
        $val = explode(',', $val);

        foreach ($val as $tmp) {
            if (is_numeric($tmp) && (int) $tmp === mb_strlen($str ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Greater than
     */
    public function greater_than(?string $str, string $min): bool
    {
        return is_numeric($str) && $str > $min;
    }

    /**
     * Equal to or Greater than
     */
    public function greater_than_equal_to(?string $str, string $min): bool
    {
        return is_numeric($str) && $str >= $min;
    }

    /**
     * Checks the database to see if the given value exist.
     * Can ignore records by field/value to filter (currently
     * accept only one filter).
     *
     * Example:
     *    is_not_unique[table.field,where_field,where_value]
     *    is_not_unique[menu.id,active,1]
     */
    public function is_not_unique(?string $str, string $field, array $data): bool
    {
        // Grab any data for exclusion of a single row.
        [$field, $whereField, $whereValue] = array_pad(explode(',', $field), 3, null);

        // Break the table and field apart
        sscanf($field, '%[^.].%[^.]', $table, $field);

        $row = Database::connect($data['DBGroup'] ?? null)
            ->table($table)
            ->select('1')
            ->where($field, $str)
            ->limit(1);

        if (! empty($whereField) && ! empty($whereValue) && ! preg_match('/^\{(\w+)\}$/', $whereValue)) {
            $row = $row->where($whereField, $whereValue);
        }

        return $row->get()->getRow() !== null;
    }

    /**
     * Value should be within an array of values
     */
    public function in_list(?string $value, string $list): bool
    {
        $list = array_map('trim', explode(',', $list));

        return in_array($value, $list, true);
    }

    /**
     * Checks the database to see if the given value is unique. Can
     * ignore a single record by field/value to make it useful during
     * record updates.
     *
     * Example:
     *    is_unique[table.field,ignore_field,ignore_value]
     *    is_unique[users.email,id,5]
     */
    public function is_unique(?string $str, string $field, array $data): bool
    {
        [$field, $ignoreField, $ignoreValue] = array_pad(explode(',', $field), 3, null);

        sscanf($field, '%[^.].%[^.]', $table, $field);

        $row = Database::connect($data['DBGroup'] ?? null)
            ->table($table)
            ->select('1')
            ->where($field, $str)
            ->limit(1);

        if (! empty($ignoreField) && ! empty($ignoreValue) && ! preg_match('/^\{(\w+)\}$/', $ignoreValue)) {
            $row = $row->where("{$ignoreField} !=", $ignoreValue);
        }

        return $row->get()->getRow() === null;
    }

    /**
     * Less than
     */
    public function less_than(?string $str, string $max): bool
    {
        return is_numeric($str) && $str < $max;
    }

    /**
     * Equal to or Less than
     */
    public function less_than_equal_to(?string $str, string $max): bool
    {
        return is_numeric($str) && $str <= $max;
    }

    /**
     * Matches the value of another field in $data.
     *
     * @param array $data Other field/value pairs
     */
    public function matches(?string $str, string $field, array $data): bool
    {
        if (strpos($field, '.') !== false) {
            return $str === dot_array_search($field, $data);
        }

        return array_key_exists($field, $data) && $str === $data[$field];
    }

    /**
     * Returns true if $str is $val or fewer characters in length.
     */
    public function max_length(?string $str, string $val): bool
    {
        return is_numeric($val) && $val >= mb_strlen($str ?? '');
    }

    /**
     * Returns true if $str is at least $val length.
     */
    public function min_length(?string $str, string $val): bool
    {
        return is_numeric($val) && $val <= mb_strlen($str ?? '');
    }

    /**
     * Does not equal the static value provided.
     *
     * @param string $str
     */
    public function not_equals(?string $str, string $val): bool
    {
        return $str !== $val;
    }

    /**
     * Value should not be within an array of values.
     *
     * @param string $value
     */
    public function not_in_list(?string $value, string $list): bool
    {
        return ! $this->in_list($value, $list);
    }

    /**
     * @param mixed $str
     */
    public function required($str = null): bool
    {
        if ($str === null) {
            return false;
        }

        if (is_object($str)) {
            return true;
        }

        if (is_array($str)) {
            return $str !== [];
        }

        return trim((string) $str) !== '';
    }

    /**
     * The field is required when any of the other required fields are present
     * in the data.
     *
     * Example (field is required when the password field is present):
     *
     *     required_with[password]
     *
     * @param string|null $str
     * @param string|null $fields List of fields that we should check if present
     * @param array       $data   Complete list of fields from the form
     */
    public function required_with($str = null, ?string $fields = null, array $data = []): bool
    {
        if ($fields === null || empty($data)) {
            throw new InvalidArgumentException('You must supply the parameters: fields, data.');
        }

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $fields  = explode(',', $fields);
        $present = $this->required($str ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are present in $data
        // as $fields is the lis
        $requiredFields = [];

        foreach ($fields as $field) {
            if ((array_key_exists($field, $data) && ! empty($data[$field])) || (strpos($field, '.') !== false && ! empty(dot_array_search($field, $data)))) {
                $requiredFields[] = $field;
            }
        }

        return empty($requiredFields);
    }

    /**
     * The field is required when all of the other fields are present
     * in the data but not required.
     *
     * Example (field is required when the id or email field is missing):
     *
     *     required_without[id,email]
     *
     * @param string|null $str
     */
    public function required_without($str = null, ?string $fields = null, array $data = []): bool
    {
        if ($fields === null || empty($data)) {
            throw new InvalidArgumentException('You must supply the parameters: fields, data.');
        }

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $fields  = explode(',', $fields);
        $present = $this->required($str ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are not present in $data
        foreach ($fields as $field) {
            if ((strpos($field, '.') === false && (! array_key_exists($field, $data) || empty($data[$field]))) || (strpos($field, '.') !== false && empty(dot_array_search($field, $data)))) {
                return false;
            }
        }

        return true;
    }
}
