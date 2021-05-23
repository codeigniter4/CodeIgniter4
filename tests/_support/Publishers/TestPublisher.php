<?php

namespace Tests\Support\Publishers;

use CodeIgniter\Publisher\Publisher;

class TestPublisher extends Publisher
{
	/**
	 * Runs the defined Operations.
	 */
	public function publish()
	{
		$this->downloadFromUrls($urls)->mergeToDirectory(FCPATH . 'assets');
	}
}
