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

use CodeIgniter\HTTP\RequestInterface;

/**
 * Expected behavior of a validator
 */
interface ValidationInterface
{
    /**
     * Runs the validation process, returning true/false determining whether
     * validation was successful or not.
     *
     * @param array|null  $data    The array of data to validate.
     * @param string|null $group   The predefined group of rules to apply.
     * @param string|null $dbGroup The database group to use.
     */
    public function run(?array $data = null, ?string $group = null, ?string $dbGroup = null): bool;

    /**
     * Check; runs the validation process, returning true or false
     * determining whether or not validation was successful.
     *
     * @param mixed    $value  Value to validation.
     * @param string   $rule   Rule.
     * @param string[] $errors Errors.
     *
     * @return bool True if valid, else false.
     */
    public function check($value, string $rule, array $errors = []): bool;

    /**
     * Takes a Request object and grabs the input data to use from its
     * array values.
     */
    public function withRequest(RequestInterface $request): ValidationInterface;

    /**
     * Stores the rules that should be used to validate the items.
     */
    public function setRules(array $rules, array $messages = []): ValidationInterface;

    /**
     * Checks to see if the rule for key $field has been set or not.
     */
    public function hasRule(string $field): bool;

    /**
     * Returns the error for a specified $field (or empty string if not set).
     */
    public function getError(string $field): string;

    /**
     * Returns the array of errors that were encountered during
     * a run() call. The array should be in the following format:
     *
     *    [
     *        'field1' => 'error message',
     *        'field2' => 'error message',
     *    ]
     *
     * @return array<string,string>
     */
    public function getErrors(): array;

    /**
     * Sets the error for a specific field. Used by custom validation methods.
     */
    public function setError(string $alias, string $error): ValidationInterface;

    /**
     * Resets the class to a blank slate. Should be called whenever
     * you need to process more than one array.
     */
    public function reset(): ValidationInterface;

    /**
     * Loads custom rule groups (if set) into the current rules.
     *
     * Rules can be pre-defined in Config\Validation and can
     * be any name, but must all still be an array of the
     * same format used with setRules(). Additionally, check
     * for {group}_errors for an array of custom error messages.
     *
     * @return array
     */
    public function loadRuleGroup(?string $group = null);

    /**
     * Checks to see if an error exists for the given field.
     */
    public function hasError(string $field): bool;

    /**
     * Returns the rendered HTML of the errors as defined in $template.
     */
    public function listErrors(string $template = 'list'): string;

    /**
     * Displays a single error in formatted HTML as defined in the $template view.
     */
    public function showError(string $field, string $template = 'single'): string;
}
