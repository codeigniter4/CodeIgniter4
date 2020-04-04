<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Email\Email;

class MockEmail extends Email
{
	/**
	 * Record of mock emails sent.
	 *
	 * @var array
	 */
	public $archive = [];

	public function send($autoClear = true)
	{
		if ($autoClear)
		{
			$this->clear();
		}

		$this->archive = get_object_vars($this);
		return true;
	}
}
