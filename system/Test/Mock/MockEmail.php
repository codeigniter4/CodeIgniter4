<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Email\Email;

class MockEmail extends Email
{
	public function send($autoClear = true)
	{
		$this->clear();

		return true;
	}
}
