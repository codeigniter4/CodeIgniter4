<?php namespace CodeIgniter\HTTP;

class IncomingRequest extends Request
{
	/*
	 * Request declares:
	 *      protected $method
	 *      public $uri
	 *      protected  $headers
	 *      protected $protocol_version
	 */



	//--------------------------------------------------------------------

	/**
	 * Creates a new request based on the server environment,
	 * and returns the new instance.
	 */
	public static function createFromGlobals()
	{
		$request = self::create(null, $_COOKIE, $_FILES, $_SERVER);

		return $request;
	}

	//--------------------------------------------------------------------

	public static function create($method='GET', array $cookies=[], array $files=[], array $server=[], string $content=null)
	{


	}
	
	//--------------------------------------------------------------------
	
	
}
