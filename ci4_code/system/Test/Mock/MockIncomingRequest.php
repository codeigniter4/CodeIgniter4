<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\IncomingRequest;

class MockIncomingRequest extends IncomingRequest
{
	//    public function populateHeaders()
	//    {
	//        // Don't do anything... force the tester to manually set the headers they want.
	//    }

	public function detectURI($protocol, $baseURL)
	{
		// Do nothing...
	}
}
