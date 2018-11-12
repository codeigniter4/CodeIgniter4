<?php namespace Tests\Support\Validation;

class TestRules {

	public function customError(string $str, string &$error = null)
	{
		$error = 'My lovely error';

		return false;
	}

	//--------------------------------------------------------------------

}
