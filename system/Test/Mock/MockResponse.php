<?php namespace CodeIgniter\Test\Mock;

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

	// for testing
	public function getPretend()
	{
		return $this->pretend;
	}

	// artificial error for testing
	public function misbehave()
	{
		$this->statusCode = 0;
	}

}
