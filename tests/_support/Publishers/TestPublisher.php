<?php

namespace Tests\Support\Publishers;

use CodeIgniter\Publisher\Publisher;

class TestPublisher extends Publisher
{
	/**
	 * Runs the defined Operations.
	 */
	public function publish(): bool
	{
		$this->downloadFromUrls($urls)->mergeToDirectory(FCPATH . 'assets');
	}
}
