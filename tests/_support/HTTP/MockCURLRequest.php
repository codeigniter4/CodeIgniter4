<?php namespace CodeIgniter\HTTP;

/**
 * Class MockCURLRequest
 *
 * Simply allows us to not actually call cURL during the
 * test runs. Instead, we can set the desired output
 * and get back the set options.
 */
class MockCURLRequest extends CURLRequest
{
	public $curl_options;

	protected $output = '';

	//--------------------------------------------------------------------

	public function setOutput($output)
	{
		$this->output = $output;

		return $this;
	}

	//--------------------------------------------------------------------

	protected function sendRequest(array $curl_options = []): string
	{
		// Save so we can access later.
		$this->curl_options = $curl_options;

		return $this->output;
	}

	//--------------------------------------------------------------------

}
