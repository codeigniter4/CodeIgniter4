<?php

namespace Tests\Support\Config;

use Config\Services as BaseServices;
use Tatter\Https\ServerRequest;

/**
 * Services Class
 *
 * Defines our version of the HTTP services to override
 * the framework defaults.
 */
class Services extends BaseServices
{
	/**
	 * The URI class provides a way to model and manipulate URIs.
	 *
	 * @param string  $uri
	 * @param boolean $getShared
	 *
	 * @return URI
	 */
	public static function uri(string $uri = null, bool $getShared = true)
	{
		if ($uri === 'testCanReplaceFrameworkServices')
		{
			$_SESSION['testCanReplaceFrameworkServices'] = true;
		}

		if ($getShared)
		{
			return static::getSharedInstance('uri', $uri);
		}

		return new URI($uri);
	}
}
