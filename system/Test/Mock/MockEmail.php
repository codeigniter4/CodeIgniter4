<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Email\Email;
use CodeIgniter\Events\Events;

class MockEmail extends Email
{
	/**
	 * Record of mock emails sent.
	 *
	 * @var array
	 */
	public $archive = [];

	/**
	 * Value to return from mocked send().
	 *
	 * @var boolean
	 */
	public $returnValue = true;

	public function send($autoClear = true)
	{
		$this->archive = get_object_vars($this);

		if ($autoClear)
		{
			$this->clear();
		}

		if ($this->returnValue)
		{
			Events::trigger('email', $this->archive);
		}

		return $this->returnValue;
	}
}
