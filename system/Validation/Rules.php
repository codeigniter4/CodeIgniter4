<?php namespace CodeIgniter\Validation;

class Rules
{
	/**
	 * Required
	 *
	 * @param	string
	 * @return	bool
	 */
	public function required($str)
	{
		return is_array($str) ? (bool) count($str) : (trim($str) !== '');
	}

	// --------------------------------------------------------------------
}
