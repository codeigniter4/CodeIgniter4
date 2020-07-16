<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Validation;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\View\RendererInterface;

/**
 * Validator
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
	 * Stores the actual rules that should
	 * be ran against $data.
	 *
	 * @var array
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
	 * @var \Config\Validation
	 */
	protected $config;

	/**
	 * The view renderer used to render validation messages.
	 *
	 * @var RendererInterface
	 */
	protected $view;

	//--------------------------------------------------------------------

	/**
	 * Validation constructor.
	 *
	 * @param \Config\Validation $config
	 * @param RendererInterface  $view
	 */
	public function __construct($config, RendererInterface $view)
	{
		$this->ruleSetFiles = $config->ruleSets;

		$this->config = $config;

		$this->view = $view;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs the validation process, returning true/false determining whether
	 * validation was successful or not.
	 *
	 * @param array  $data     The array of data to validate.
	 * @param string $group    The pre-defined group of rules to apply.
	 * @param string $db_group The database group to use.
	 *
	 * @return boolean
	 */
	public function run(array $data = null, string $group = null, string $db_group = null): bool
	{
		$data = $data ?? $this->data;

		// i.e. is_unique
		$data['DBGroup'] = $db_group;

		$this->loadRuleSets();

		$this->loadRuleGroup($group);

		// If no rules exist, we return false to ensure
		// the developer didn't forget to set the rules.
		if (empty($this->rules))
		{
			return false;
		}

		// Replace any placeholders (i.e. {id}) in the rules with
		// the value found in $data, if exists.
		$this->rules = $this->fillPlaceholders($this->rules, $data);

		// Need this for searching arrays in validation.
		helper('array');

		// Run through each rule. If we have any field set for
		// this rule, then we need to run them through!
		foreach ($this->rules as $rField => $rSetup)
		{
			// Blast $rSetup apart, unless it's already an array.
			$rules = $rSetup['rules'] ?? $rSetup;

			if (is_string($rules))
			{
				$rules = $this->splitRules($rules);
			}

			$value          = dot_array_search($rField, $data);
			$fieldNameToken = explode('.', $rField);

			if (is_array($value) && end($fieldNameToken) === '*')
			{
				foreach ($value as $val)
				{
					$this->processRules($rField, $rSetup['label'] ?? $rField, $val ?? null, $rules, $data);
				}
			}
			else
			{
				$this->processRules($rField, $rSetup['label'] ?? $rField, $value ?? null, $rules, $data);
			}
		}

		return ! empty($this->getErrors()) ? false : true;
	}

	//--------------------------------------------------------------------

	/**
	 * Check; runs the validation process, returning true or false
	 * determining whether validation was successful or not.
	 *
	 * @param mixed    $value  Value to validation.
	 * @param string   $rule   Rule.
	 * @param string[] $errors Errors.
	 *
	 * @return boolean True if valid, else false.
	 */
	public function check($value, string $rule, array $errors = []): bool
	{
		$this->reset();
		$this->setRule('check', null, $rule, $errors);

		return $this->run([
			'check' => $value,
		]);
	}

	//--------------------------------------------------------------------

	/**
	 * Runs all of $rules against $field, until one fails, or
	 * all of them have been processed. If one fails, it adds
	 * the error to $this->errors and moves on to the next,
	 * so that we can collect all of the first errors.
	 *
	 * @param string       $field
	 * @param string|null  $label
	 * @param string|array $value Value to be validated, can be a string or an array
	 * @param array|null   $rules
	 * @param array        $data  // All of the fields to check.
	 *
	 * @return boolean
	 */
	protected function processRules(string $field, string $label = null, $value, $rules = null, array $data): bool
	{
		// If the if_exist rule is defined...
		if (in_array('if_exist', $rules))
		{
			// and the current field does not exists in the input data
			// we can return true. Ignoring all other rules to this field.
			if (! array_key_exists($field, $data))
			{
				return true;
			}
			// Otherwise remove the if_exist rule and continue the process
			$rules = array_diff($rules, ['if_exist']);
		}

		if (in_array('permit_empty', $rules))
		{
			if (! in_array('required', $rules) && (is_array($value) ? empty($value) : (trim($value) === '')))
			{
				$passed = true;

				foreach ($rules as $rule)
				{
					if (preg_match('/(.*?)\[(.*)\]/', $rule, $match))
					{
						$rule  = $match[1];
						$param = $match[2];

						if (! in_array($rule, ['required_with', 'required_without']))
						{
							continue;
						}

						// Check in our rulesets
						foreach ($this->ruleSetInstances as $set)
						{
							if (! method_exists($set, $rule))
							{
								continue;
							}

							$passed = $passed && $set->$rule($value, $param, $data);
							break;
						}
					}
				}

				if ($passed === true)
				{
					return true;
				}
			}

			$rules = array_diff($rules, ['permit_empty']);
		}

		foreach ($rules as $rule)
		{
			$callable = is_callable($rule);
			$passed   = false;

			// Rules can contain parameters: max_length[5]
			$param = false;
			if (! $callable && preg_match('/(.*?)\[(.*)\]/', $rule, $match))
			{
				$rule  = $match[1];
				$param = $match[2];
			}

			// Placeholder for custom errors from the rules.
			$error = null;

			// If it's a callable, call and and get out of here.
			if ($callable)
			{
				$passed = $param === false ? $rule($value) : $rule($value, $param, $data);
			}
			else
			{
				$found = false;

				// Check in our rulesets
				foreach ($this->ruleSetInstances as $set)
				{
					if (! method_exists($set, $rule))
					{
						continue;
					}

					$found = true;

					$passed = $param === false ? $set->$rule($value, $error) : $set->$rule($value, $param, $data, $error);
					break;
				}

				// If the rule wasn't found anywhere, we
				// should throw an exception so the developer can find it.
				if (! $found)
				{
					throw ValidationException::forRuleNotFound($rule);
				}
			}

			// Set the error message if we didn't survive.
			if ($passed === false)
			{
				// if the $value is an array, convert it to as string representation
				if (is_array($value))
				{
					$value = '[' . implode(', ', $value) . ']';
				}

				$this->errors[$field] = is_null($error) ? $this->getErrorMessage($rule, $field, $label, $param, $value)
					: $error;

				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a Request object and grabs the input data to use from its
	 * array values.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return \CodeIgniter\Validation\ValidationInterface
	 */
	public function withRequest(RequestInterface $request): ValidationInterface
	{
		if (in_array($request->getMethod(), ['put', 'patch', 'delete']))
		{
			$this->data = $request->getRawInput();
		}
		else
		{
			$this->data = $request->getVar() ?? [];
		}

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------

	/**
	 * Sets an individual rule and custom error messages for a single field.
	 *
	 * The custom error message should be just the messages that apply to
	 * this field, like so:
	 *
	 *    [
	 *        'rule' => 'message',
	 *        'rule' => 'message'
	 *    ]
	 *
	 * @param string      $field
	 * @param string|null $label
	 * @param string      $rules
	 * @param array       $errors
	 *
	 * @return $this
	 */
	public function setRule(string $field, string $label = null, string $rules, array $errors = [])
	{
		$this->rules[$field] = [
			'label' => $label,
			'rules' => $rules,
		];
		$this->customErrors  = array_merge($this->customErrors, [
			$field => $errors,
		]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the rules that should be used to validate the items.
	 * Rules should be an array formatted like:
	 *
	 *    [
	 *        'field' => 'rule1|rule2'
	 *    ]
	 *
	 * The $errors array should be formatted like:
	 *    [
	 *        'field' => [
	 *            'rule' => 'message',
	 *            'rule' => 'message
	 *        ],
	 *    ]
	 *
	 * @param array $rules
	 * @param array $errors // An array of custom error messages
	 *
	 * @return \CodeIgniter\Validation\ValidationInterface
	 */
	public function setRules(array $rules, array $errors = []): ValidationInterface
	{
		$this->customErrors = $errors;

		foreach ($rules as $field => &$rule)
		{
			if (is_array($rule))
			{
				if (array_key_exists('errors', $rule))
				{
					$this->customErrors[$field] = $rule['errors'];
					unset($rule['errors']);
				}
			}
		}

		$this->rules = $rules;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all of the rules currently defined.
	 *
	 * @return array
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the rule for key $field has been set or not.
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public function hasRule(string $field): bool
	{
		return array_key_exists($field, $this->rules);
	}

	//--------------------------------------------------------------------

	/**
	 * Get rule group.
	 *
	 * @param string $group Group.
	 *
	 * @return string[] Rule group.
	 *
	 * @throws \InvalidArgumentException If group not found.
	 */
	public function getRuleGroup(string $group): array
	{
		if (! isset($this->config->$group))
		{
			throw ValidationException::forGroupNotFound($group);
		}

		if (! is_array($this->config->$group))
		{
			throw ValidationException::forGroupNotArray($group);
		}

		return $this->config->$group;
	}

	//--------------------------------------------------------------------

	/**
	 * Set rule group.
	 *
	 * @param string $group Group.
	 *
	 * @throws \InvalidArgumentException If group not found.
	 */
	public function setRuleGroup(string $group)
	{
		$rules = $this->getRuleGroup($group);
		$this->setRules($rules);

		$errorName = $group . '_errors';
		if (isset($this->config->$errorName))
		{
			$this->customErrors = $this->config->$errorName;
		}
	}

	/**
	 * Returns the rendered HTML of the errors as defined in $template.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function listErrors(string $template = 'list'): string
	{
		if (! array_key_exists($template, $this->config->templates))
		{
			throw ValidationException::forInvalidTemplate($template);
		}

		return $this->view->setVar('errors', $this->getErrors())
						->render($this->config->templates[$template]);
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a single error in formatted HTML as defined in the $template view.
	 *
	 * @param string $field
	 * @param string $template
	 *
	 * @return string
	 */
	public function showError(string $field, string $template = 'single'): string
	{
		if (! array_key_exists($field, $this->getErrors()))
		{
			return '';
		}

		if (! array_key_exists($template, $this->config->templates))
		{
			throw ValidationException::forInvalidTemplate($template);
		}

		return $this->view->setVar('error', $this->getError($field))
						->render($this->config->templates[$template]);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads all of the rulesets classes that have been defined in the
	 * Config\Validation and stores them locally so we can use them.
	 */
	protected function loadRuleSets()
	{
		if (empty($this->ruleSetFiles))
		{
			throw ValidationException::forNoRuleSets();
		}

		foreach ($this->ruleSetFiles as $file)
		{
			$this->ruleSetInstances[] = new $file();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Loads custom rule groups (if set) into the current rules.
	 *
	 * Rules can be pre-defined in Config\Validation and can
	 * be any name, but must all still be an array of the
	 * same format used with setRules(). Additionally, check
	 * for {group}_errors for an array of custom error messages.
	 *
	 * @param string|null $group
	 *
	 * @return array|ValidationException|null
	 */
	public function loadRuleGroup(string $group = null)
	{
		if (empty($group))
		{
			return null;
		}

		if (! isset($this->config->$group))
		{
			throw ValidationException::forGroupNotFound($group);
		}

		if (! is_array($this->config->$group))
		{
			throw ValidationException::forGroupNotArray($group);
		}

		$this->setRules($this->config->$group);

		// If {group}_errors exists in the config file,
		// then override our custom errors with them.
		$errorName = $group . '_errors';

		if (isset($this->config->$errorName))
		{
			$this->customErrors = $this->config->$errorName;
		}

		return $this->rules;
	}

	//--------------------------------------------------------------------

	/**
	 * Replace any placeholders within the rules with the values that
	 * match the 'key' of any properties being set. For example, if
	 * we had the following $data array:
	 *
	 * [ 'id' => 13 ]
	 *
	 * and the following rule:
	 *
	 *  'required|is_unique[users,email,id,{id}]'
	 *
	 * The value of {id} would be replaced with the actual id in the form data:
	 *
	 *  'required|is_unique[users,email,id,13]'
	 *
	 * @param array $rules
	 * @param array $data
	 *
	 * @return array
	 */
	protected function fillPlaceholders(array $rules, array $data): array
	{
		$replacements = [];

		foreach ($data as $key => $value)
		{
			$replacements["{{$key}}"] = $value;
		}

		if (! empty($replacements))
		{
			foreach ($rules as &$rule)
			{
				if (is_array($rule))
				{
					foreach ($rule as &$row)
					{
						// Should only be an `errors` array
						// which doesn't take placeholders.
						if (is_array($row))
						{
							continue;
						}

						$row = strtr($row, $replacements);
					}
					continue;
				}

				$rule = strtr($rule, $replacements);
			}
		}

		return $rules;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Errors
	//--------------------------------------------------------------------

	/**
	 * Checks to see if an error exists for the given field.
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public function hasError(string $field): bool
	{
		return array_key_exists($field, $this->getErrors());
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error(s) for a specified $field (or empty string if not
	 * set).
	 *
	 * @param string $field Field.
	 *
	 * @return string Error(s).
	 */
	public function getError(string $field = null): string
	{
		if ($field === null && count($this->rules) === 1)
		{
			reset($this->rules);
			$field = key($this->rules);
		}

		return array_key_exists($field, $this->getErrors()) ? $this->errors[$field] : '';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the array of errors that were encountered during
	 * a run() call. The array should be in the following format:
	 *
	 *    [
	 *        'field1' => 'error message',
	 *        'field2' => 'error message',
	 *    ]
	 *
	 * @return array
	 *
	 * Excluded from code coverage because that it always run as cli
	 *
	 * @codeCoverageIgnore
	 */
	public function getErrors(): array
	{
		// If we already have errors, we'll use those.
		// If we don't, check the session to see if any were
		// passed along from a redirect_with_input request.
		if (empty($this->errors) && ! is_cli())
		{
			if (isset($_SESSION, $_SESSION['_ci_validation_errors']))
			{
				$this->errors = unserialize($_SESSION['_ci_validation_errors']);
			}
		}

		return $this->errors ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the error for a specific field. Used by custom validation methods.
	 *
	 * @param string $field
	 * @param string $error
	 *
	 * @return \CodeIgniter\Validation\ValidationInterface
	 */
	public function setError(string $field, string $error): ValidationInterface
	{
		$this->errors[$field] = $error;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to find the appropriate error message
	 *
	 * @param string      $rule
	 * @param string      $field
	 * @param string|null $label
	 * @param string      $param
	 * @param string      $value The value that caused the validation to fail.
	 *
	 * @return string
	 */
	protected function getErrorMessage(string $rule, string $field, string $label = null, string $param = null, string $value = null): string
	{
		// Check if custom message has been defined by user
		if (isset($this->customErrors[$field][$rule]))
		{
			$message = lang($this->customErrors[$field][$rule]);
		}
		else
		{
			// Try to grab a localized version of the message...
			// lang() will return the rule name back if not found,
			// so there will always be a string being returned.
			$message = lang('Validation.' . $rule);
		}

		$message = str_replace('{field}', empty($label) ? $field : lang($label), $message);
		$message = str_replace('{param}', empty($this->rules[$param]['label']) ? $param : lang($this->rules[$param]['label']), $message);

		return str_replace('{value}', $value, $message);
	}

	/**
	 * Split rules string by pipe operator.
	 *
	 * @param string $rules
	 *
	 * @return array
	 */
	protected function splitRules(string $rules): array
	{
		$non_escape_bracket  = '((?<!\\\\)(?:\\\\\\\\)*[\[\]])';
		$pipe_not_in_bracket = sprintf(
				'/\|(?=(?:[^\[\]]*%s[^\[\]]*%s)*(?![^\[\]]*%s))/',
				$non_escape_bracket,
				$non_escape_bracket,
				$non_escape_bracket
		);

		$_rules = preg_split(
				$pipe_not_in_bracket,
				$rules
		);

		return array_unique($_rules);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Misc
	//--------------------------------------------------------------------

	/**
	 * Resets the class to a blank slate. Should be called whenever
	 * you need to process more than one array.
	 *
	 * @return \CodeIgniter\Validation\ValidationInterface
	 */
	public function reset(): ValidationInterface
	{
		$this->data         = [];
		$this->rules        = [];
		$this->errors       = [];
		$this->customErrors = [];

		return $this;
	}

	//--------------------------------------------------------------------
}
