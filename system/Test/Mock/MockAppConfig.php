<?php namespace CodeIgniter\Test\Mock;

class MockAppConfig
{
	public $baseURL = 'http://example.com/';

	public $uriProtocol = 'REQUEST_URI';

	public $cookiePrefix   = '';
	public $cookieDomain   = '';
	public $cookiePath     = '/';
	public $cookieSecure   = false;
	public $cookieHTTPOnly = false;
	public $cookieSameSite = 'Lax';

	public $proxyIPs = '';

	public $CSPEnabled = false;

	public $defaultLocale    = 'en';
	public $negotiateLocale  = false;
	public $supportedLocales = [
		'en',
		'es',
	];
}
