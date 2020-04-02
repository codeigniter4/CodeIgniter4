<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Email\Email;

class MockEmail extends Email
{
	public function send($autoClear = true)
	{
		if ($autoClear)
		{
			$this->clear();
		}

		return true;
	}
}
