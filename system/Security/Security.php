<?php namespace CodeIgniter\Security;

use App\Config\AppConfig;
use App\Config\Services;

class Security
{
	/**
	 * CSRF Enabled
	 *
	 * Whether CSRF Protection is enabled.
	 *
	 * @var bool
	 */
	protected $CSRFEnabled = true;

	/**
	 * CSRF Hash
	 *
	 * Random hash for Cross Site Request Forgery protection cookie
	 *
	 * @var	string
	 */
	protected $CSRFHash;

	/**
	 * CSRF Expire time
	 *
	 * Expiration time for Cross Site Request Forgery protection cookie.
	 * Defaults to two hours (in seconds).
	 *
	 * @var	int
	 */
	protected $CSRFExpire =	7200;

	/**
	 * CSRF Token name
	 *
	 * Token name for Cross Site Request Forgery protection cookie.
	 *
	 * @var	string
	 */
	protected $CSRFTokenName =	'CSRFToken';

	/**
	 * CSRF Cookie name
	 *
	 * Cookie name for Cross Site Request Forgery protection cookie.
	 *
	 * @var	string
	 */
	protected $CSRFCookieName =	'CSRFToken';

	/**
	 * CSRF Regenerate
	 *
	 * If true, the CSRF Token will be regenerated on every request.
	 * If false, will stay the same for the life of the cookie.
	 *
	 * @var bool
	 */
	protected $CSRFRegenerate = true;

	/**
	 * CSRF Exclude URIs
	 *
	 * An array of URIs to skip when checking CSRF.
	 *
	 * @var array
	 */
	protected $CSRFExcludeURIs = [];

	/**
	 * Typically will be a forward slash
	 *
	 * @var string
	 */
	protected $cookiePath = '/';

	/**
	 * Set to .your-domain.com for site-wide cookies
	 *
	 * @var string
	 */
	protected $cookieDomain = '';

	/**
	 * Cookie will only be set if a secure HTTPS connection exists.
	 *
	 * @var bool
	 */
	protected $cookieSecure = false;

	//--------------------------------------------------------------------

	/**
	 * Security constructor.
	 *
	 * Stores our configuration and fires off the init() method to
	 * setup initial state.
	 *
	 * @param AppConfig $config
	 */
	public function __construct(AppConfig $config)
	{
		// Store our CSRF-related settings
	    $this->CSRFEnabled     = $config->CSRFProtection;
		$this->CSRFExpire      = $config->CSRFExpire;
		$this->CSRFTokenName   = $config->CSRFTokenName;
		$this->CSRFCookieName  = $config->CSRFCookieName;
		$this->CSRFRegenerate  = $config->CSRFRegenerate;
		$this->CSRFExcludeURIs = $config->CSRFExcludeURIs;

		if (isset($config->cookiePrefix))
		{
			$this->CSRFCookieName = $config->cookiePrefix.$this->CSRFCookieName;
		}

		// Store cookie-related settings
		$this->cookiePath = $config->cookiePath;
		$this->cookieDomain = $config->cookieDomain;
		$this->cookieSecure = $config->cookieSecure;

		$this->CSRFSetHash();

		unset($config);
	}

	//--------------------------------------------------------------------

	/**
	 * CSRF Verify
	 *
	 * @return $this
	 */
	public function CSRFVerify()
	{
		// If it's not a POST request we will set the CSRF cookie
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
		{
			return $this->CSRFSetCookie();
		}

		// Check if URI has been whitelisted from CSRF checks
		if (is_array($this->CSRFExcludeURIs) && count($this->CSRFExcludeURIs))
		{
			global $request;

			foreach ($this->CSRFExcludeURIs as $excluded)
			{
				if (preg_match('#^'.$excluded.'$#i'.(UTF8_ENABLED ? 'u' : ''), $request->uri->getPath()))
				{
					return $this;
				}
			}
		}

		// Do the tokens exist in both the _POST and _COOKIE arrays?
		if ( ! isset($_POST[$this->CSRFTokenName], $_COOKIE[$this->CSRFCookieName])
		     OR $_POST[$this->CSRFTokenName] !== $_COOKIE[$this->CSRFCookieName]) // Do the tokens match?
		{
			throw new \LogicException('The action you requested is not allowed', 403);
		}

		// We kill this since we're done and we don't want to polute the _POST array
		unset($_POST[$this->CSRFTokenName]);

		// Regenerate on every submission?
		if ($this->CSRFRegenerate)
		{
			// Nothing should last forever
			unset($_COOKIE[$this->CSRFCookieName]);
			$this->_csrf_hash = NULL;
		}

		$this->CSRFSetHash();
		$this->CSRFSetCookie();

		log_message('info', 'CSRF token verified');
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * CSRF Set Cookie
	 *
	 * @codeCoverageIgnore
	 * @return	$this
	 */
	public function CSRFSetCookie()
	{
		$expire = time() + $this->CSRFExpire;
		$secure_cookie = (bool)$this->cookieSecure;

		global $request;

		if ($secure_cookie && ! $request->isSecure())
		{
			return FALSE;
		}

		setcookie(
			$this->CSRFCookieName,
			$this->CSRFHash,
			$expire,
			$this->cookiePath,
			$this->cookieDomain,
			$secure_cookie,
			true                // Enforce HTTP only cookie for security
		);

		log_message('info', 'CSRF cookie sent');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current CSRF Hash.
	 *
	 * @return string
	 */
	public function getCSRFHash()
	{
	    return $this->CSRFHash;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Token Name.
	 *
	 * @return string
	 */
	public function getCSRFTokenName()
	{
	    return $this->CSRFTokenName;
	}
	
	//--------------------------------------------------------------------
	
	

	/**
	 * Sets the CSRF Hash and cookie.
	 *
	 * @return string
	 */
	protected function CSRFSetHash()
	{
		if ($this->CSRFHash === null)
		{
			// If the cookie exists we will use its value.
			// We don't necessarily want to regenerate it with
			// each page load since a page could contain embedded
			// sub-pages causing this feature to fail
			if (isset($_COOKIE[$this->CSRFCookieName]) && is_string($_COOKIE[$this->CSRFCookieName])
			    && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->CSRFCookieName]) === 1)
			{
				return $this->CSRFHash = $_COOKIE[$this->CSRFCookieName];
			}

			$rand = random_bytes(16);
			$this->CSRFHash = bin2hex($rand);
		}

		return $this->CSRFHash;
	}

	//--------------------------------------------------------------------

}
