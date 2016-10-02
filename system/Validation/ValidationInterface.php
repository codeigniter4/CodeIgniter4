<?php namespace CodeIgniter\Validation;

use CodeIgniter\HTTP\RequestInterface;

interface ValidationInterface
{
	/**
	 * Runs the validation process, returning true/false determing whether
	 * or not validation was successful.
	 *
	 * @param array  $data		The array of data to validate.
	 * @param string $group		The pre-defined group of rules to apply.
	 *
	 * @return bool
	 */
	public function run(array $data, string $group = null): bool;

	//--------------------------------------------------------------------

	/**
	 * Takes a Request object and grabs the data to use from its
	 * POST array values.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 *
	 * @return \CodeIgniter\Validation\Validation
	 */
	public function withRequest(RequestInterface $request): ValidationInterface;

	//--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------

	/**
	 * Stores the rules that should be used to validate the items.
	 *
	 * @param array $rules
	 *
	 * @return \CodeIgniter\Validation\ValidationInterface
	 */
	public function setRules(array $rules): ValidationInterface;

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the rule for key $field has been set or not.
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
	public function hasRule(string $field): bool;

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Errors
	//--------------------------------------------------------------------

	/**
	 * Returns the error for a specified $field (or empty string if not set).
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	public function getError(string $field): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the array of errors that were encountered during
	 * a run() call. The array should be in the followig format:
	 *
	 *    [
	 *        'field1' => 'error message',
	 *        'field2' => 'error message',
	 *    ]
	 *
	 * @return array
	 */
	public function getErrors(): array;

	//--------------------------------------------------------------------

	/**
	 * Sets the error for a specific field. Used by custom validation methods.
	 *
	 * @param string $alias
	 * @param string $error
	 *
	 * @return \CodeIgniter\Validation\Validation
	 */
	public function setError(string $alias, string $error): ValidationInterface;

	//--------------------------------------------------------------------
	// Misc
	//--------------------------------------------------------------------

	/**
	 * Resets the class to a blank slate. Should be called whenever
	 * you need to process more than one array.
	 *
	 * @return mixed
	 */
	public function reset(): ValidationInterface;

	//--------------------------------------------------------------------
}
