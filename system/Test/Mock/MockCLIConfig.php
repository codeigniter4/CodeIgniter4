<?php namespace CodeIgniter\Test\Mock;

class MockCLIConfig extends \Config\App
{
	public $baseURL = 'http://example.com';

	public $uriProtocol = 'REQUEST_URI';

	public $cookiePrefix   = '';
	public $cookieDomain   = '';
	public $cookiePath     = '/';
	public $cookieSecure   = false;
	public $cookieHTTPOnly = false;
	public $cookieSameSite = 'Lax';

	public $proxyIPs = '';

	public $CSRFProtection  = false;
	public $CSRFTokenName   = 'csrf_test_name';
	public $CSRFCookieName  = 'csrf_cookie_name';
	public $CSRFExpire      = 7200;
	public $CSRFRegenerate  = true;
	public $CSRFExcludeURIs = ['http://example.com'];
	public $CSRFSameSite    = 'Lax';

	public $CSPEnabled = false;

	public $defaultLocale    = 'en';
	public $negotiateLocale  = false;
	public $supportedLocales = [
		'en',
		'es',
	];
}
