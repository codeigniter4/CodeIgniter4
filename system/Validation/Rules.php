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

use CodeIgniter\Helpers\Array\ArrayHelper;
use Config\Database;
use InvalidArgumentException;

/**
 * Validation Rules.
 *
 * @see \CodeIgniter\Validation\RulesTest
 */
class Rules
{
    /**
     * The value does not match another field in $data.
     *
     * @param string|null $str
     * @param array       $data Other field/value pairs
     */
    public function differs($str, string $field, array $data): bool
    {
        if (str_contains($field, '.')) {
            return $str !== dot_array_search($field, $data);
        }

        return array_key_exists($field, $data) && $str !== $data[$field];
    }

    /**
     * Equals the static value provided.
     *
     * @param string|null $str
     */
    public function equals($str, string $val): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        return $str === $val;
    }

    /**
     * Returns true if $str is $val characters long.
     * $val = "5" (one) | "5,8,12" (multiple values)
     *
     * @param string|null $str
     */
    public function exact_length($str, string $val): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

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
     *
     * @param string|null $str
     */
    public function greater_than($str, string $min): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        return is_numeric($str) && $str > $min;
    }

    /**
     * Equal to or Greater than
     *
     * @param string|null $str
     */
    public function greater_than_equal_to($str, string $min): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

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
     *
     * @param string|null $str
     */
    public function is_not_unique($str, string $field, array $data): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        // Grab any data for exclusion of a single row.
        [$field, $whereField, $whereValue] = array_pad(
            explode(',', $field),
            3,
            null
        );

        // Break the table and field apart
        sscanf($field, '%[^.].%[^.]', $table, $field);

        $row = Database::connect($data['DBGroup'] ?? null)
            ->table($table)
            ->select('1')
            ->where($field, $str)
            ->limit(1);

        if (
            $whereField !== null && $whereField !== ''
            && $whereValue !== null && $whereValue !== ''
            && ! preg_match('/^\{(\w+)\}$/', $whereValue)
        ) {
            $row = $row->where($whereField, $whereValue);
        }

        return $row->get()->getRow() !== null;
    }

    /**
     * Value should be within an array of values
     *
     * @param string|null $value
     */
    public function in_list($value, string $list): bool
    {
        if (! is_string($value) && $value !== null) {
            $value = (string) $value;
        }

        $list = array_map(trim(...), explode(',', $list));

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
     *
     * @param string|null $str
     */
    public function is_unique($str, string $field, array $data): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        [$field, $ignoreField, $ignoreValue] = array_pad(
            explode(',', $field),
            3,
            null
        );

        sscanf($field, '%[^.].%[^.]', $table, $field);

        $row = Database::connect($data['DBGroup'] ?? null)
            ->table($table)
            ->select('1')
            ->where($field, $str)
            ->limit(1);

        if (
            $ignoreField !== null && $ignoreField !== ''
            && $ignoreValue !== null && $ignoreValue !== ''
            && ! preg_match('/^\{(\w+)\}$/', $ignoreValue)
        ) {
            $row = $row->where("{$ignoreField} !=", $ignoreValue);
        }

        return $row->get()->getRow() === null;
    }

    /**
     * Less than
     *
     * @param string|null $str
     */
    public function less_than($str, string $max): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        return is_numeric($str) && $str < $max;
    }

    /**
     * Equal to or Less than
     *
     * @param string|null $str
     */
    public function less_than_equal_to($str, string $max): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        return is_numeric($str) && $str <= $max;
    }

    /**
     * Matches the value of another field in $data.
     *
     * @param string|null $str
     * @param array       $data Other field/value pairs
     */
    public function matches($str, string $field, array $data): bool
    {
        if (str_contains($field, '.')) {
            return $str === dot_array_search($field, $data);
        }

        return isset($data[$field]) && $str === $data[$field];
    }

    /**
     * Returns true if $str is $val or fewer characters in length.
     *
     * @param string|null $str
     */
    public function max_length($str, string $val): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        return is_numeric($val) && $val >= mb_strlen($str ?? '');
    }

    /**
     * Returns true if $str is at least $val length.
     *
     * @param string|null $str
     */
    public function min_length($str, string $val): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        return is_numeric($val) && $val <= mb_strlen($str ?? '');
    }

    /**
     * Does not equal the static value provided.
     *
     * @param string|null $str
     */
    public function not_equals($str, string $val): bool
    {
        if (! is_string($str) && $str !== null) {
            $str = (string) $str;
        }

        return $str !== $val;
    }

    /**
     * Value should not be within an array of values.
     *
     * @param string|null $value
     */
    public function not_in_list($value, string $list): bool
    {
        if (! is_string($value) && $value !== null) {
            $value = (string) $value;
        }

        return ! $this->in_list($value, $list);
    }

    /**
     * @param array|bool|float|int|object|string|null $str
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
        if ($fields === null || $data === []) {
            throw new InvalidArgumentException('You must supply the parameters: fields, data.');
        }

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $present = $this->required($str ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are present in $data
        // as $fields is the list
        $requiredFields = [];

        foreach (explode(',', $fields) as $field) {
            if (
                (array_key_exists($field, $data)
                    && ! empty($data[$field]))  // @phpstan-ignore-line Use empty()
                || (str_contains($field, '.')
                    && ! empty(dot_array_search($field, $data)))  // @phpstan-ignore-line Use empty()
            ) {
                $requiredFields[] = $field;
            }
        }

        return $requiredFields === [];
    }

    /**
     * The field is required when all the other fields are present
     * in the data but not required.
     *
     * Example (field is required when the id or email field is missing):
     *
     *     required_without[id,email]
     *
     * @param string|null $str
     * @param string|null $otherFields The param fields of required_without[].
     * @param string|null $field       This rule param fields aren't present, this field is required.
     */
    public function required_without(
        $str = null,
        ?string $otherFields = null,
        array $data = [],
        ?string $error = null,
        ?string $field = null
    ): bool {
        if ($otherFields === null || $data === []) {
            throw new InvalidArgumentException('You must supply the parameters: otherFields, data.');
        }

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $present = $this->required($str ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are not present in $data
        foreach (explode(',', $otherFields) as $otherField) {
            if (
                (! str_contains($otherField, '.'))
                && (! array_key_exists($otherField, $data)
                    || empty($data[$otherField])) // @phpstan-ignore-line Use empty()
            ) {
                return false;
            }

            if (str_contains($otherField, '.')) {
                if ($field === null) {
                    throw new InvalidArgumentException('You must supply the parameters: field.');
                }

                $fieldData       = dot_array_search($otherField, $data);
                $fieldSplitArray = explode('.', $field);
                $fieldKey        = $fieldSplitArray[1];

                if (is_array($fieldData)) {
                    return ! empty(dot_array_search($otherField, $data)[$fieldKey]);  // @phpstan-ignore-line Use empty()
                }
                $nowField      = str_replace('*', $fieldKey, $otherField);
                $nowFieldVaule = dot_array_search($nowField, $data);

                return null !== $nowFieldVaule;
            }
        }

        return true;
    }

    /**
     * The field exists in $data.
     *
     * @param array|bool|float|int|object|string|null $value The field value.
     * @param string|null                             $param The rule's parameter.
     * @param array                                   $data  The data to be validated.
     * @param string|null                             $field The field name.
     */
    public function field_exists(
        $value = null,
        ?string $param = null,
        array $data = [],
        ?string $error = null,
        ?string $field = null
    ): bool {
        if (str_contains($field, '.')) {
            return ArrayHelper::dotKeyExists($field, $data);
        }

        return array_key_exists($field, $data);
    }
}
