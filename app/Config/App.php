<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Base Site URL
	 * --------------------------------------------------------------------------
	 *
	 * URL to your CodeIgniter root. Typically this will be your base URL,
	 * WITH a trailing slash:
	 *
	 *    http://example.com/
	 *
	 * If this is not set then CodeIgniter will try guess the protocol, domain
	 * and path to your installation. However, you should always configure this
	 * explicitly and never rely on auto-guessing, especially in production
	 * environments.
	 *
	 * @var string
	 */
	public $baseURL = 'http://localhost:8080/';

	/**
	 * --------------------------------------------------------------------------
	 * Index File
	 * --------------------------------------------------------------------------
	 *
	 * Typically this will be your index.php file, unless you've renamed it to
	 * something else. If you are using mod_rewrite to remove the page set this
	 * variable so that it is blank.
	 *
	 * @var string
	 */
	public $indexPage = 'index.php';

	/**
	 * --------------------------------------------------------------------------
	 * URI PROTOCOL
	 * --------------------------------------------------------------------------
	 *
	 * This item determines which getServer global should be used to retrieve the
	 * URI string.  The default setting of 'REQUEST_URI' works for most servers.
	 * If your links do not seem to work, try one of the other delicious flavors:
	 *
	 * 'REQUEST_URI'    Uses $_SERVER['REQUEST_URI']
	 * 'QUERY_STRING'   Uses $_SERVER['QUERY_STRING']
	 * 'PATH_INFO'      Uses $_SERVER['PATH_INFO']
	 *
	 * WARNING: If you set this to 'PATH_INFO', URIs will always be URL-decoded!
	 *
	 * @var string
	 */
	public $uriProtocol = 'REQUEST_URI';

	/**
	 * --------------------------------------------------------------------------
	 * Default Locale
	 * --------------------------------------------------------------------------
	 *
	 * The Locale roughly represents the language and location that your visitor
	 * is viewing the site from. It affects the language strings and other
	 * strings (like currency markers, numbers, etc), that your program
	 * should run under for this request.
	 *
	 * @var string
	 */
	public $defaultLocale = 'en';

	/**
	 * --------------------------------------------------------------------------
	 * Negotiate Locale
	 * --------------------------------------------------------------------------
	 *
	 * If true, the current Request object will automatically determine the
	 * language to use based on the value of the Accept-Language header.
	 *
	 * If false, no automatic detection will be performed.
	 *
	 * @var boolean
	 */
	public $negotiateLocale = false;

	/**
	 * --------------------------------------------------------------------------
	 * Supported Locales
	 * --------------------------------------------------------------------------
	 *
	 * If $negotiateLocale is true, this array lists the locales supported
	 * by the application in descending order of priority. If no match is
	 * found, the first locale will be used.
	 *
	 * @var string[]
	 */
	public $supportedLocales = ['en'];

	/**
	 * --------------------------------------------------------------------------
	 * Application Timezone
	 * --------------------------------------------------------------------------
	 *
	 * The default timezone that will be used in your application to display
	 * dates with the date helper, and can be retrieved through app_timezone()
	 *
	 * @var string
	 */
	public $appTimezone = 'America/Chicago';

	/**
	 * --------------------------------------------------------------------------
	 * Default Character Set
	 * --------------------------------------------------------------------------
	 *
	 * This determines which character set is used by default in various methods
	 * that require a character set to be provided.
	 *
	 * @see http://php.net/htmlspecialchars for a list of supported charsets.
	 *
	 * @var string
	 */
	public $charset = 'UTF-8';

	/**
	 * --------------------------------------------------------------------------
	 * URI PROTOCOL
	 * --------------------------------------------------------------------------
	 *
	 * If true, this will force every request made to this application to be
	 * made via a secure connection (HTTPS). If the incoming request is not
	 * secure, the user will be redirected to a secure version of the page
	 * and the HTTP Strict Transport Security header will be set.
	 *
	 * @var boolean
	 */
	public $forceGlobalSecureRequests = false;

	/**
	 * --------------------------------------------------------------------------
	 * Reverse Proxy IPs
	 * --------------------------------------------------------------------------
	 *
	 * If your server is behind a reverse proxy, you must whitelist the proxy
	 * IP addresses from which CodeIgniter should trust headers such as
	 * HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP in order to properly identify
	 * the visitor's IP address.
	 *
	 * You can use both an array or a comma-separated list of proxy addresses,
	 * as well as specifying whole subnets. Here are a few examples:
	 *
	 * Comma-separated:	'10.0.1.200,192.168.5.0/24'
	 * Array: ['10.0.1.200', '192.168.5.0/24']
	 *
	 * @var string|string[]
	 */
	public $proxyIPs = '';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Token Name
	 * --------------------------------------------------------------------------
	 *
	 * The token name.
	 *
	 * @var string
	 */
	public $CSRFTokenName = 'csrf_test_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Header Name
	 * --------------------------------------------------------------------------
	 *
	 * The header name.
	 *
	 * @var string
	 */
	public $CSRFHeaderName = 'X-CSRF-TOKEN';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Cookie Name
	 * --------------------------------------------------------------------------
	 *
	 * The cookie name.
	 *
	 * @var string
	 */
	public $CSRFCookieName = 'csrf_cookie_name';

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Expire
	 * --------------------------------------------------------------------------
	 *
	 * The number in seconds the token should expire.
	 *
	 * @var integer
	 */
	public $CSRFExpire = 7200;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Regenerate
	 * --------------------------------------------------------------------------
	 *
	 * Regenerate token on every submission?
	 *
	 * @var boolean
	 */
	public $CSRFRegenerate = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF Redirect
	 * --------------------------------------------------------------------------
	 *
	 * Redirect to previous page with error on failure?
	 *
	 * @var boolean
	 */
	public $CSRFRedirect = true;

	/**
	 * --------------------------------------------------------------------------
	 * CSRF SameSite
	 * --------------------------------------------------------------------------
	 *
	 * Setting for CSRF SameSite cookie token. Allowed values are:
	 * - None
	 * - Lax
	 * - Strict
	 * - ''
	 *
	 * Defaults to `Lax` as recommended in this link:
	 *
	 * @see https://portswigger.net/web-security/csrf/samesite-cookies
	 *
	 * @var string
	 */
	public $CSRFSameSite = 'Lax';

	/**
	 * --------------------------------------------------------------------------
	 * Content Security Policy
	 * --------------------------------------------------------------------------
	 *
	 * Enables the Response's Content Secure Policy to restrict the sources that
	 * can be used for images, scripts, CSS files, audio, video, etc. If enabled,
	 * the Response object will populate default values for the policy from the
	 * `ContentSecurityPolicy.php` file. Controllers can always add to those
	 * restrictions at run time.
	 *
	 * For a better understanding of CSP, see these documents:
	 *
	 * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
	 * @see http://www.w3.org/TR/CSP/
	 *
	 * @var boolean
	 */
	public $CSPEnabled = false;
}
