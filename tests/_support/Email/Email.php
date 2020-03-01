<?php
/**
 * Extends \CodeIgniter\Email and implements `__get()` to access protected properties
 */

namespace Tests\Support\Email;

class Email extends \CodeIgniter\Email\Email
{
	public function __get(string $key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}

		return null;
	}
}
