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

use Closure;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Method;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\View\RendererInterface;

/**
 * Validator
 *
 * @see \CodeIgniter\Validation\ValidationTest
 */
class Validation implements ValidationInterface
{
    /**
     * Files to load with validation functions.
     *
     * @var array
     */
    protected $ruleSetFiles;

    /**
     * The loaded instances of our validation files.
     *
     * @var array
     */
    protected $ruleSetInstances = [];

    /**
     * Stores the actual rules that should be run against $data.
     *
     * @var array<array-key, array{label?: string, rules: list<string>}>
     *
     * [
     *     field1 => [
     *         'label' => label,
     *         'rules' => [
     *              rule1, rule2, ...
     *          ],
     *     ],
     * ]
     */
    protected $rules = [];

    /**
     * The data that should be validated,
     * where 'key' is the alias, with value.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The data that was actually validated.
     *
     * @var array
     */
    protected $validated = [];

    /**
     * Any generated errors during validation.
     * 'key' is the alias, 'value' is the message.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Stores custom error message to use
     * during validation. Where 'key' is the alias.
     *
     * @var array
     */
    protected $customErrors = [];

    /**
     * Our configuration.
     *
     * @var object{ruleSets: list<class-string>}
     */
    protected $config;

    /**
     * The view renderer used to render validation messages.
     *
     * @var RendererInterface
     */
    protected $view;

    /**
     * Validation constructor.
     *
     * @param object{ruleSets: list<class-string>} $config
     */
    public function __construct($config, RendererInterface $view)
    {
        $this->ruleSetFiles = $config->ruleSets;

        $this->config = $config;

        $this->view = $view;

        $this->loadRuleSets();
    }

    /**
     * Runs the validation process, returning true/false determining whether
     * validation was successful or not.
     *
     * @param array|null                                 $data    The array of data to validate.
     * @param string|null                                $group   The predefined group of rules to apply.
     * @param array|BaseConnection|non-empty-string|null $dbGroup The database group to use.
     */
    public function run(?array $data = null, ?string $group = null, $dbGroup = null): bool
    {
        if ($data === null) {
            $data = $this->data;
        } else {
            // Store data to validate.
            $this->data = $data;
        }

        // `DBGroup` is a reserved name. For is_unique and is_not_unique
        $data['DBGroup'] = $dbGroup;

        $this->loadRuleGroup($group);

        // If no rules exist, we return false to ensure
        // the developer didn't forget to set the rules.
        if ($this->rules === []) {
            return false;
        }

        // Replace any placeholders (e.g. {id}) in the rules with
        // the value found in $data, if any.
        $this->rules = $this->fillPlaceholders($this->rules, $data);

        // Need this for searching arrays in validation.
        helper('array');

        // Run through each rule. If we have any field set for
        // this rule, then we need to run them through!
        foreach ($this->rules as $field => $setup) {
            //  An array key might be int.
            $field = (string) $field;

            $rules = $setup['rules'];

            if (is_string($rules)) {
                $rules = $this->splitRules($rules);
            }

            if (str_contains($field, '*')) {
                $flattenedArray = array_flatten_with_dots($data);

                $values = array_filter(
                    $flattenedArray,
                    static fn ($key): bool => preg_match(self::getRegex($field), $key) === 1,
                    ARRAY_FILTER_USE_KEY,
                );

                // if keys not found
                $values = $values !== [] ? $values : [$field => null];
            } else {
                $values = dot_array_search($field, $data);
            }

            if ($values === []) {
                // We'll process the values right away if an empty array
                $this->processRules($field, $setup['label'] ?? $field, $values, $rules, $data, $field);

                continue;
            }

            if (str_contains($field, '*')) {
                // Process multiple fields
                foreach ($values as $dotField => $value) {
                    $this->processRules($dotField, $setup['label'] ?? $field, $value, $rules, $data, $field);
                }
            } else {
                // Process single field
                $this->processRules($field, $setup['label'] ?? $field, $values, $rules, $data, $field);
            }
        }

        if ($this->getErrors() === []) {
            // Store data that was actually validated.
            $this->validated = DotArrayFilter::run(
                array_keys($this->rules),
                $this->data,
            );

            return true;
        }

        return false;
    }

    /**
     * Returns regex pattern for key with dot array syntax.
     */
    private static function getRegex(string $field): string
    {
        return '/\A'
            . str_replace(
                ['\.\*', '\*\.'],
                ['\.[^.]+', '[^.]+\.'],
                preg_quote($field, '/'),
            )
            . '\z/';
    }

    /**
     * Runs the validation process, returning true or false determining whether
     * validation was successful or not.
     *
     * @param array|bool|float|int|object|string|null $value   The data to validate.
     * @param array|string                            $rules   The validation rules.
     * @param list<string>                            $errors  The custom error message.
     * @param string|null                             $dbGroup The database group to use.
     */
    public function check($value, $rules, array $errors = [], $dbGroup = null): bool
    {
        $this->reset();

        return $this->setRule(
            'check',
            null,
            $rules,
            $errors,
        )->run(
            ['check' => $value],
            null,
            $dbGroup,
        );
    }

    /**
     * Returns the actual validated data.
     */
    public function getValidated(): array
    {
        return $this->validated;
    }

    /**
     * Runs all of $rules against $field, until one fails, or
     * all of them have been processed. If one fails, it adds
     * the error to $this->errors and moves on to the next,
     * so that we can collect all of the first errors.
     *
     * @param array|string $value
     * @param array        $rules
     * @param array        $data          The array of data to validate, with `DBGroup`.
     * @param string|null  $originalField The original asterisk field name like "foo.*.bar".
     */
    protected function processRules(
        string $field,
        ?string $label,
        $value,
        $rules = null,       // @TODO remove `= null`
        ?array $data = null, // @TODO remove `= null`
        ?string $originalField = null,
    ): bool {
        if ($data === null) {
            throw new InvalidArgumentException('You must supply the parameter: data.');
        }

        $rules = $this->processIfExist($field, $rules, $data);
        if ($rules === true) {
            return true;
        }

        $rules = $this->processPermitEmpty($value, $rules, $data);
        if ($rules === true) {
            return true;
        }

        foreach ($rules as $i => $rule) {
            $isCallable     = is_callable($rule);
            $stringCallable = $isCallable && is_string($rule);
            $arrayCallable  = $isCallable && is_array($rule);

            $passed = false;
            /** @var string|null $param */
            $param = null;

            if (! $isCallable && preg_match('/(.*?)\[(.*)\]/', $rule, $match)) {
                $rule  = $match[1];
                $param = $match[2];
            }

            // Placeholder for custom errors from the rules.
            $error = null;

            // If it's a callable, call and get out of here.
            if ($this->isClosure($rule)) {
                $passed = $rule($value, $data, $error, $field);
            } elseif ($isCallable) {
                $passed = $stringCallable ? $rule($value) : $rule($value, $data, $error, $field);
            } else {
                $found = false;

                // Check in our rulesets
                foreach ($this->ruleSetInstances as $set) {
                    if (! method_exists($set, $rule)) {
                        continue;
                    }

                    $found = true;

                    if ($rule === 'field_exists') {
                        $passed = $set->{$rule}($value, $param, $data, $error, $originalField);
                    } else {
                        $passed = ($param === null)
                            ? $set->{$rule}($value, $error)
                            : $set->{$rule}($value, $param, $data, $error, $field);
                    }

                    break;
                }

                // If the rule wasn't found anywhere, we
                // should throw an exception so the developer can find it.
                if (! $found) {
                    throw ValidationException::forRuleNotFound($rule);
                }
            }

            // Set the error message if we didn't survive.
            if ($passed === false) {
                // if the $value is an array, convert it to as string representation
                if (is_array($value)) {
                    $value = $this->isStringList($value)
                        ? '[' . implode(', ', $value) . ']'
                        : json_encode($value);
                } elseif (is_object($value)) {
                    $value = json_encode($value);
                }

                $fieldForErrors = ($rule === 'field_exists') ? $originalField : $field;

                // @phpstan-ignore-next-line $error may be set by rule methods.
                $this->errors[$fieldForErrors] = $error ?? $this->getErrorMessage(
                    ($this->isClosure($rule) || $arrayCallable) ? (string) $i : $rule,
                    $field,
                    $label,
                    $param,
                    (string) $value,
                    $originalField,
                );

                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data The array of data to validate, with `DBGroup`.
     *
     * @return array|true The modified rules or true if we return early
     */
    private function processIfExist(string $field, array $rules, array $data)
    {
        if (in_array('if_exist', $rules, true)) {
            $flattenedData = array_flatten_with_dots($data);
            $ifExistField  = $field;

            if (str_contains($field, '.*')) {
                // We'll change the dot notation into a PCRE pattern that can be used later
                $ifExistField   = str_replace('\.\*', '\.(?:[^\.]+)', preg_quote($field, '/'));
                $dataIsExisting = false;
                $pattern        = sprintf('/%s/u', $ifExistField);

                foreach (array_keys($flattenedData) as $item) {
                    if (preg_match($pattern, $item) === 1) {
                        $dataIsExisting = true;
                        break;
                    }
                }
            } elseif (str_contains($field, '.')) {
                $dataIsExisting = array_key_exists($ifExistField, $flattenedData);
            } else {
                $dataIsExisting = array_key_exists($ifExistField, $data);
            }

            if (! $dataIsExisting) {
                // we return early if `if_exist` is not satisfied. we have nothing to do here.
                return true;
            }

            // Otherwise remove the if_exist rule and continue the process
            $rules = array_filter($rules, static fn ($rule): bool => $rule instanceof Closure || $rule !== 'if_exist');
        }

        return $rules;
    }

    /**
     * @param array|string $value
     * @param array        $data  The array of data to validate, with `DBGroup`.
     *
     * @return array|true The modified rules or true if we return early
     */
    private function processPermitEmpty($value, array $rules, array $data)
    {
        if (in_array('permit_empty', $rules, true)) {
            if (
                ! in_array('required', $rules, true)
                && (is_array($value) ? $value === [] : trim((string) $value) === '')
            ) {
                $passed = true;

                foreach ($rules as $rule) {
                    if (! $this->isClosure($rule) && preg_match('/(.*?)\[(.*)\]/', $rule, $match)) {
                        $rule  = $match[1];
                        $param = $match[2];

                        if (! in_array($rule, ['required_with', 'required_without'], true)) {
                            continue;
                        }

                        // Check in our rulesets
                        foreach ($this->ruleSetInstances as $set) {
                            if (! method_exists($set, $rule)) {
                                continue;
                            }

                            $passed = $passed && $set->{$rule}($value, $param, $data);
                            break;
                        }
                    }
                }

                if ($passed) {
                    return true;
                }
            }

            $rules = array_filter($rules, static fn ($rule): bool => $rule instanceof Closure || $rule !== 'permit_empty');
        }

        return $rules;
    }

    /**
     * @param Closure(bool|float|int|list<mixed>|object|string|null, bool|float|int|list<mixed>|object|string|null, string|null, string|null): (bool|string) $rule
     */
    private function isClosure($rule): bool
    {
        return $rule instanceof Closure;
    }

    /**
     * Is the array a string list `list<string>`?
     */
    private function isStringList(array $array): bool
    {
        $expectedKey = 0;

        foreach ($array as $key => $val) {
            // Note: also covers PHP array key conversion, e.g. '5' and 5.1 both become 5
            if (! is_int($key)) {
                return false;
            }

            if ($key !== $expectedKey) {
                return false;
            }
            $expectedKey++;

            if (! is_string($val)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Takes a Request object and grabs the input data to use from its
     * array values.
     */
    public function withRequest(RequestInterface $request): ValidationInterface
    {
        /** @var IncomingRequest $request */
        if (str_contains($request->getHeaderLine('Content-Type'), 'application/json')) {
            $this->data = $request->getJSON(true);

            if (! is_array($this->data)) {
                throw HTTPException::forUnsupportedJSONFormat();
            }

            return $this;
        }

        if (in_array($request->getMethod(), [Method::PUT, Method::PATCH, Method::DELETE], true)
            && ! str_contains($request->getHeaderLine('Content-Type'), 'multipart/form-data')
        ) {
            $this->data = $request->getRawInput();
        } else {
            $this->data = $request->getVar() ?? [];
        }

        return $this;
    }

    /**
     * Sets (or adds) an individual rule and custom error messages for a single
     * field.
     *
     * The custom error message should be just the messages that apply to
     * this field, like so:
     *    [
     *        'rule1' => 'message1',
     *        'rule2' => 'message2',
     *    ]
     *
     * @param array|string $rules  The validation rules.
     * @param array        $errors The custom error message.
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setRule(string $field, ?string $label, $rules, array $errors = [])
    {
        if (! is_array($rules) && ! is_string($rules)) {
            throw new InvalidArgumentException('$rules must be of type string|array');
        }

        $ruleSet = [
            $field => [
                'label' => $label,
                'rules' => $rules,
            ],
        ];

        if ($errors !== []) {
            $ruleSet[$field]['errors'] = $errors;
        }

        $this->setRules(array_merge($this->getRules(), $ruleSet), $this->customErrors);

        return $this;
    }

    /**
     * Stores the rules that should be used to validate the items.
     *
     * Rules should be an array formatted like:
     *    [
     *        'field' => 'rule1|rule2'
     *    ]
     *
     * The $errors array should be formatted like:
     *    [
     *        'field' => [
     *            'rule1' => 'message1',
     *            'rule2' => 'message2',
     *        ],
     *    ]
     *
     * @param array $errors An array of custom error messages
     */
    public function setRules(array $rules, array $errors = []): ValidationInterface
    {
        $this->customErrors = $errors;

        foreach ($rules as $field => &$rule) {
            if (is_array($rule)) {
                if (array_key_exists('errors', $rule)) {
                    $this->customErrors[$field] = $rule['errors'];
                    unset($rule['errors']);
                }

                // if $rule is already a rule collection, just move it to "rules"
                // transforming [foo => [required, foobar]] to [foo => [rules => [required, foobar]]]
                if (! array_key_exists('rules', $rule)) {
                    $rule = ['rules' => $rule];
                }
            }

            if (isset($rule['rules']) && is_string($rule['rules'])) {
                $rule['rules'] = $this->splitRules($rule['rules']);
            }

            if (is_string($rule)) {
                $rule = ['rules' => $this->splitRules($rule)];
            }
        }

        $this->rules = $rules;

        return $this;
    }

    /**
     * Returns all of the rules currently defined.
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Checks to see if the rule for key $field has been set or not.
     */
    public function hasRule(string $field): bool
    {
        return array_key_exists($field, $this->rules);
    }

    /**
     * Get rule group.
     *
     * @param string $group Group.
     *
     * @return list<string> Rule group.
     *
     * @throws ValidationException If group not found.
     */
    public function getRuleGroup(string $group): array
    {
        if (! isset($this->config->{$group})) {
            throw ValidationException::forGroupNotFound($group);
        }

        if (! is_array($this->config->{$group})) {
            throw ValidationException::forGroupNotArray($group);
        }

        return $this->config->{$group};
    }

    /**
     * Set rule group.
     *
     * @param string $group Group.
     *
     * @return void
     *
     * @throws ValidationException If group not found.
     */
    public function setRuleGroup(string $group)
    {
        $rules = $this->getRuleGroup($group);
        $this->setRules($rules);

        $errorName = $group . '_errors';
        if (isset($this->config->{$errorName})) {
            $this->customErrors = $this->config->{$errorName};
        }
    }

    /**
     * Returns the rendered HTML of the errors as defined in $template.
     *
     * You can also use validation_list_errors() in Form helper.
     */
    public function listErrors(string $template = 'list'): string
    {
        if (! array_key_exists($template, $this->config->templates)) {
            throw ValidationException::forInvalidTemplate($template);
        }

        return $this->view
            ->setVar('errors', $this->getErrors())
            ->render($this->config->templates[$template]);
    }

    /**
     * Displays a single error in formatted HTML as defined in the $template view.
     *
     * You can also use validation_show_error() in Form helper.
     */
    public function showError(string $field, string $template = 'single'): string
    {
        if (! array_key_exists($field, $this->getErrors())) {
            return '';
        }

        if (! array_key_exists($template, $this->config->templates)) {
            throw ValidationException::forInvalidTemplate($template);
        }

        return $this->view
            ->setVar('error', $this->getError($field))
            ->render($this->config->templates[$template]);
    }

    /**
     * Loads all of the rulesets classes that have been defined in the
     * Config\Validation and stores them locally so we can use them.
     *
     * @return void
     */
    protected function loadRuleSets()
    {
        if ($this->ruleSetFiles === [] || $this->ruleSetFiles === null) {
            throw ValidationException::forNoRuleSets();
        }

        foreach ($this->ruleSetFiles as $file) {
            $this->ruleSetInstances[] = new $file();
        }
    }

    /**
     * Loads custom rule groups (if set) into the current rules.
     *
     * Rules can be pre-defined in Config\Validation and can
     * be any name, but must all still be an array of the
     * same format used with setRules(). Additionally, check
     * for {group}_errors for an array of custom error messages.
     *
     * @param non-empty-string|null $group
     *
     * @return array<int, array> [rules, customErrors]
     *
     * @throws ValidationException
     */
    public function loadRuleGroup(?string $group = null)
    {
        if ($group === null || $group === '') {
            return [];
        }

        if (! isset($this->config->{$group})) {
            throw ValidationException::forGroupNotFound($group);
        }

        if (! is_array($this->config->{$group})) {
            throw ValidationException::forGroupNotArray($group);
        }

        $this->setRules($this->config->{$group});

        // If {group}_errors exists in the config file,
        // then override our custom errors with them.
        $errorName = $group . '_errors';

        if (isset($this->config->{$errorName})) {
            $this->customErrors = $this->config->{$errorName};
        }

        return [$this->rules, $this->customErrors];
    }

    /**
     * Replace any placeholders within the rules with the values that
     * match the 'key' of any properties being set. For example, if
     * we had the following $data array:
     *
     * [ 'id' => 13 ]
     *
     * and the following rule:
     *
     *  'is_unique[users,email,id,{id}]'
     *
     * The value of {id} would be replaced with the actual id in the form data:
     *
     *  'is_unique[users,email,id,13]'
     */
    protected function fillPlaceholders(array $rules, array $data): array
    {
        foreach ($rules as &$rule) {
            $ruleSet = $rule['rules'];

            foreach ($ruleSet as &$row) {
                if (is_string($row)) {
                    $placeholderFields = $this->retrievePlaceholders($row, $data);

                    foreach ($placeholderFields as $field) {
                        $validator ??= service('validation', null, false);
                        assert($validator instanceof Validation);

                        $placeholderRules = $rules[$field]['rules'] ?? null;

                        // Check if the validation rule for the placeholder exists
                        if ($placeholderRules === null) {
                            throw new LogicException(
                                'No validation rules for the placeholder: "' . $field
                                . '". You must set the validation rules for the field.'
                                . ' See <https://codeigniter4.github.io/userguide/libraries/validation.html#validation-placeholders>.',
                            );
                        }

                        // Check if the rule does not have placeholders
                        foreach ($placeholderRules as $placeholderRule) {
                            if ($this->retrievePlaceholders($placeholderRule, $data) !== []) {
                                throw new LogicException(
                                    'The placeholder field cannot use placeholder: ' . $field,
                                );
                            }
                        }

                        // Validate the placeholder field
                        $dbGroup = $data['DBGroup'] ?? null;
                        if (! $validator->check($data[$field], $placeholderRules, [], $dbGroup)) {
                            // if fails, do nothing
                            continue;
                        }

                        // Replace the placeholder in the current rule string
                        if (str_starts_with($row, 'regex_match[')) {
                            $row = str_replace('{{' . $field . '}}', (string) $data[$field], $row);
                        } else {
                            $row = str_replace('{' . $field . '}', (string) $data[$field], $row);
                        }
                    }
                }
            }

            $rule['rules'] = $ruleSet;
        }

        return $rules;
    }

    /**
     * Retrieves valid placeholder fields.
     */
    private function retrievePlaceholders(string $rule, array $data): array
    {
        if (str_starts_with($rule, 'regex_match[')) {
            // For regex_match rules, only look for double-bracket placeholders
            preg_match_all('/\{\{((?:(?![{}]).)+?)\}\}/', $rule, $matches);
        } else {
            // For all other rules, use single-bracket placeholders
            preg_match_all('/{(.+?)}/', $rule, $matches);
        }

        return array_intersect($matches[1], array_keys($data));
    }

    /**
     * Checks to see if an error exists for the given field.
     */
    public function hasError(string $field): bool
    {
        return (bool) preg_grep(self::getRegex($field), array_keys($this->getErrors()));
    }

    /**
     * Returns the error(s) for a specified $field (or empty string if not
     * set).
     */
    public function getError(?string $field = null): string
    {
        if ($field === null && count($this->rules) === 1) {
            $field = array_key_first($this->rules);
        }

        $errors = array_filter(
            $this->getErrors(),
            static fn ($key): bool => preg_match(self::getRegex($field), $key) === 1,
            ARRAY_FILTER_USE_KEY,
        );

        return implode("\n", $errors);
    }

    /**
     * Returns the array of errors that were encountered during
     * a run() call. The array should be in the following format:
     *
     *    [
     *        'field1' => 'error message',
     *        'field2' => 'error message',
     *    ]
     *
     * @return array<string, string>
     *
     * @codeCoverageIgnore
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Sets the error for a specific field. Used by custom validation methods.
     */
    public function setError(string $field, string $error): ValidationInterface
    {
        $this->errors[$field] = $error;

        return $this;
    }

    /**
     * Attempts to find the appropriate error message
     *
     * @param non-empty-string|null $label
     * @param string|null           $value The value that caused the validation to fail.
     */
    protected function getErrorMessage(
        string $rule,
        string $field,
        ?string $label = null,
        ?string $param = null,
        ?string $value = null,
        ?string $originalField = null,
    ): string {
        $param ??= '';

        $args = [
            'field' => ($label === null || $label === '') ? $field : lang($label),
            'param' => isset($this->rules[$param]['label']) ? lang($this->rules[$param]['label']) : $param,
            'value' => $value ?? '',
        ];

        // Check if custom message has been defined by user
        if (isset($this->customErrors[$field][$rule])) {
            return lang($this->customErrors[$field][$rule], $args);
        }
        if (null !== $originalField && isset($this->customErrors[$originalField][$rule])) {
            return lang($this->customErrors[$originalField][$rule], $args);
        }

        // Try to grab a localized version of the message...
        // lang() will return the rule name back if not found,
        // so there will always be a string being returned.
        return lang('Validation.' . $rule, $args);
    }

    /**
     * Split rules string by pipe operator.
     */
    protected function splitRules(string $rules): array
    {
        if (! str_contains($rules, '|')) {
            return [$rules];
        }

        $string = $rules;
        $rules  = [];
        $length = strlen($string);
        $cursor = 0;

        while ($cursor < $length) {
            $pos = strpos($string, '|', $cursor);

            if ($pos === false) {
                // we're in the last rule
                $pos = $length;
            }

            $rule = substr($string, $cursor, $pos - $cursor);

            while (
                (substr_count($rule, '[') - substr_count($rule, '\['))
                !== (substr_count($rule, ']') - substr_count($rule, '\]'))
            ) {
                // the pipe is inside the brackets causing the closing bracket to
                // not be included. so, we adjust the rule to include that portion.
                $pos  = strpos($string, '|', $cursor + strlen($rule) + 1) ?: $length;
                $rule = substr($string, $cursor, $pos - $cursor);
            }

            $rules[] = $rule;
            $cursor += strlen($rule) + 1; // +1 to exclude the pipe
        }

        return array_unique($rules);
    }

    /**
     * Resets the class to a blank slate. Should be called whenever
     * you need to process more than one array.
     */
    public function reset(): ValidationInterface
    {
        $this->data         = [];
        $this->validated    = [];
        $this->rules        = [];
        $this->errors       = [];
        $this->customErrors = [];

        return $this;
    }
}
