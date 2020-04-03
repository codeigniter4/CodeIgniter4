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
		$this->archive = get_object_vars($this);

		if ($autoClear)
		{
			$this->clear();
		}

		return true;
	}
}
