<?php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class AppConfig extends BaseConfig {

	/*
	|--------------------------------------------------------------------------
	| URI PROTOCOL
	|--------------------------------------------------------------------------
	|
	| This item determines which server global should be used to retrieve the
	| URI string.  The default setting of 'REQUEST_URI' works for most servers.
	| If your links do not seem to work, try one of the other delicious flavors:
	|
	| 'REQUEST_URI'    Uses $_SERVER['REQUEST_URI']
	| 'QUERY_STRING'   Uses $_SERVER['QUERY_STRING']
	| 'PATH_INFO'      Uses $_SERVER['PATH_INFO']
	|
	| WARNING: If you set this to 'PATH_INFO', URIs will always be URL-decoded!
	*/
	public $uriProtocol = 'REQUEST_URI';

	/*
	|--------------------------------------------------------------------------
	| Allowed URL Characters
	|--------------------------------------------------------------------------
	|
	| This lets you specify which characters are permitted within your URLs.
	| When someone tries to submit a URL with disallowed characters they will
	| get a warning message.
	|
	| As a security measure you are STRONGLY encouraged to restrict URLs to
	| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
	|
	| Leave blank to allow all characters -- but only if you are insane.
	|
	| The configured value is actually a regular expression character group
	| and it will be executed as: ! preg_match('/^[<permitted_uri_chars>]+$/i
	|
	| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
	|
	*/
	public $permittedURIChars = 'a-z 0-9~%.:_\-';

}
