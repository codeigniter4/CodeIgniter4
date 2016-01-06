<?php

class MockAppConfig
{
	public $baseURL = '';

	public $uriProtocol = 'REQUEST_URI';

	public $cookiePrefix = '';
	public $cookieDomain = '';
	public $cookiePath = '/';
	public $cookieSecure = false;
	public $cookieHTTPOnly = false;

	public $proxyIPs = '';

	public $CSRFProtection  = false;
	public $CSRFTokenName   = 'csrf_test_name';
	public $CSRFCookieName  = 'csrf_cookie_name';
	public $CSRFExpire      = 7200;
	public $CSRFRegenerate  = true;
	public $CSRFExcludeURIs = [];

	public $CSPEnabled = false;
}
