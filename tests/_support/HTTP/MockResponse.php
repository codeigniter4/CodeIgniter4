<?php namespace Tests\Support\HTTP;

use CodeIgniter\HTTP\Response;

/**
 * Class MockResponse
 */
class MockResponse extends Response
{
	/**
	 * If true, will not write output. Useful during testing.
	 *
	 * @var boolean
	 */
	protected $pretend = true;

}
