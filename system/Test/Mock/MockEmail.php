<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\Email\Email;
use CodeIgniter\Events\Events;

class MockEmail extends Email
{
	/**
	 * Value to return from mocked send().
	 *
	 * @var boolean
	 */
	public $returnValue = true;

	public function send($autoClear = true)
	{
		if ($this->returnValue)
		{
			// Determine the correct properties to archive
			$archive = array_merge(get_object_vars($this), $this->archive);
			unset($archive['archive']);

			if ($autoClear)
			{
				$this->clear();
			}

			Events::trigger('email', $archive);
			$this->archive = $archive;
		}

		return $this->returnValue;
	}
}
