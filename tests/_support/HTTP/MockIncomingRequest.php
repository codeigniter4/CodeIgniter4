<?php namespace Tests\Support\HTTP;

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
