<?php namespace CodeIgniter\Log\Handlers;

/**
 * Class MockHandler
 *
 * Extends ChromeLoggerHandler, exposing some inner workings
 */
class MockChromeHandler extends ChromeLoggerHandler
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
