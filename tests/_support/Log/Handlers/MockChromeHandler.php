<?php namespace Tests\Support\Log\Handlers;

/**
 * Class MockHandler
 *
 * Extends ChromeLoggerHandler, exposing some inner workings
 */

class MockChromeHandler extends \CodeIgniter\Log\Handlers\ChromeLoggerHandler
{

	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
		parent::__construct($config);
	}

	// retrieve the message from the JSON response
	public function peekaboo()
	{
		return $this->json['rows'][0];
	}

}
