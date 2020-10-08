<?php

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;

/**
 * Class CORS - Cross-Origin Resource sharing
 *
 * Provides tools for working with the CORS header to help defeat XSS attacks.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 * @see     https://www.html5rocks.com/en/tutorials/file/xhr2/#toc-cors
 * @package CodeIgniter\HTTP
 */

class CORS
{

	/**
	 * Allowed methods
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
	 *
	 * @var array
	 */
	protected $allowedMethods= [
		'GET',
		'HEAD',
		'POST',
		'PUT',
		'DELETE',
		'CONNECT',
		'OPTIONS',
		'TRACE',
		'PATCH'
	];

	/**
	 * Headers to be mounted
	 *
	 * @var array
	 */
	public $headerList= [];

	public function __construct(\Config\CORS $config)
	{
		foreach ($config->headerList as $name=>$value) {
			if ($name === 'Access-Control-Allow-Methods') {
				if (!$this->methods($value)) {
					throw new HTTPException("Method not allowed");
				}
			}
			$this->headerList[$name]= $value;
		}

	}

	/**
	 * Verify if the method is allowed
	 *
	 * @param string $methods
	 * @return bool
	 */
	private function methods(string $methods) : bool
	{
		// gerating array of methods in user config
		$arrayMethods= explode(',', $methods);

		// for each user method test if it is allowed
		foreach ($arrayMethods as $method) {
			// removing white spaces
			$method= trim($method);
			if (!in_array($method, $this->allowedMethods, true)) {
				return false;
			}
		}

		return true;
	}

}
