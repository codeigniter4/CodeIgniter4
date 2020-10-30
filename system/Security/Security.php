<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Security;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\App;
use Exception;

/**
 * Class Security
 *
 * Provides methods that help protect your site against
 * Cross-Site Request Forgery attacks.
 */
class Security
{
	/**
	 * CSRF Hash
	 *
	 * Random hash for Cross Site Request Forgery protection cookie
	 *
	 * @var string|null
	 */
	protected $hash = null;

	/**
	 * CSRF Token Name
	 *
	 * Token name for Cross Site Request Forgery protection cookie.
	 *
	 * @var string
	 */
	protected $tokenName = 'CSRFToken';

	/**
	 * CSRF Header Name
	 *
	 * Token name for Cross Site Request Forgery protection cookie.
	 *
	 * @var string
	 */
	protected $headerName = 'CSRFToken';

	/**
	 * CSRF Cookie Name
	 *
	 * Cookie name for Cross Site Request Forgery protection cookie.
	 *
	 * @var string
	 */
	protected $cookieName = 'CSRFToken';

	/**
	 * CSRF Expires
	 *
	 * Expiration time for Cross Site Request Forgery protection cookie.
	 *
	 * Defaults to two hours (in seconds).
	 *
	 * @var integer
	 */
	protected $expires = 7200;

	/**
	 * CSRF Regenerate
	 *
	 * Regenerate CSRF Token on every request.
	 *
	 * @var boolean
	 */
	protected $regenerate = true;

	/**
	 * CSRF Redirect
	 *
	 * Redirect to previous page with error on failure.
	 *
	 * @var boolean
	 */
	protected $redirect = true;

	/**
	 * CSRF SameSite
	 *
	 * Setting for CSRF SameSite cookie token.
	 *
	 * Allowed values are: None - Lax - Strict - ''.
	 *
	 * Defaults to `Lax` as recommended in this link:
	 * @see https://portswigger.net/web-security/csrf/samesite-cookies
	 *
	 * @var string
	 */
	protected $samesite = 'Lax';

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * Stores our configuration and fires off the init() method to setup
	 * initial state.
	 *
	 * @param App $config
	 *
	 * @throws SecurityException
	 */
	public function __construct($config)
	{
		// Store CSRF-related configurations
		$this->tokenName  = $config->CSRFTokenName ?? $config->tokenName ?? $this->tokenName;
		$this->headerName = $config->CSRFHeaderName ?? $config->headerName ?? $this->headerName;
		$this->cookieName = $config->CSRFCookieName ?? $config->cookieName ?? $this->cookieName;
		$this->expires    = $config->CSRFExpire ?? $config->expires ?? $this->expires;
		$this->regenerate = $config->CSRFRegenerate ?? $config->regenerate ?? $this->regenerate;
		$this->samesite   = $config->CSRFSameSite ?? $config->samesite ?? $this->samesite;

		if (! in_array(strtolower($this->samesite), ['none', 'lax', 'strict', ''], true))
		{
			throw SecurityException::forInvalidSameSite($this->samesite);
		}

		if (isset($config->cookiePrefix))
		{
			$this->cookieName = $config->cookiePrefix . $this->cookieName;
		}

		$this->generateHash();
	}

	//--------------------------------------------------------------------

	/**
	 * CSRF Verify
	 *
	 * @param RequestInterface $request
	 *
	 * @return $this|false
	 * @throws SecurityException
	 *
	 * @deprecated Use `CodeIgniter\Security\Security::verify()` instead of using this method.
	 */
	public function CSRFVerify(RequestInterface $request)
	{
		return $this->verify($request);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Hash.
	 *
	 * @return string|null
	 *
	 * @deprecated Use `CodeIgniter\Security\Security::getHash()` instead of using this method.
	 */
	public function getCSRFHash(): ?string
	{
		return $this->getHash();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Token Name.
	 *
	 * @return string
	 *
	 * @deprecated Use `CodeIgniter\Security\Security::getTokenName()` instead of using this method.
	 */
	public function getCSRFTokenName(): string
	{
		return $this->getTokenName();
	}

	//--------------------------------------------------------------------

	/**
	 * CSRF Verify
	 *
	 * @param RequestInterface $request
	 *
	 * @return $this|false
	 * @throws SecurityException
	 */
	public function verify(RequestInterface $request)
	{
		// If it's not a POST request we will set the CSRF cookie.
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
		{
			return $this->sendCookie($request);
		}

		// Does the token exist in POST, HEADER or optionally php:://input - json data.
		if ($request->hasHeader($this->headerName) && ! empty($request->getHeader($this->headerName)->getValue()))
		{
			$tokenName = $request->getHeader($this->headerName)->getValue();
		}
		else
		{
			$json = json_decode($request->getBody());

			if (! empty($request->getBody()) && ! empty($json) && json_last_error() === JSON_ERROR_NONE)
			{
				$tokenName = $json->{$this->tokenName} ?? null;
			}
			else
			{
				$tokenName = null;
			}
		}

		$token = $_POST[$this->tokenName] ?? $tokenName;

		// Does the tokens exist in both the POST/POSTed JSON and COOKIE arrays and match?
		if (! isset($token, $_COOKIE[$this->cookieName]) || $token !== $_COOKIE[$this->cookieName])
		{
			throw SecurityException::forDisallowedAction();
		}

		if (isset($_POST[$this->tokenName]))
		{
			// We kill this since we're done and we don't want to pollute the POST array.
			unset($_POST[$this->tokenName]);
			$request->setGlobal('post', $_POST);
		}
		elseif (isset($json->{$this->tokenName}))
		{
			// We kill this since we're done and we don't want to pollute the JSON data.
			unset($json->{$this->tokenName});
			$request->setBody(json_encode($json));
		}

		// Regenerate on every submission?
		if ($this->regenerate)
		{
			// Nothing should last forever.
			$this->hash = null;
			unset($_COOKIE[$this->cookieName]);
		}

		$this->generateHash();
		$this->sendCookie($request);

		log_message('info', 'CSRF token verifie.');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Hash.
	 *
	 * @return string|null
	 */
	public function getHash(): ?string
	{
		return $this->hash;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Token Name.
	 *
	 * @return string
	 */
	public function getTokenName(): string
	{
		return $this->tokenName;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Header Name.
	 *
	 * @return string
	 */
	public function getHeaderName(): string
	{
		return $this->headerName;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Cookie Name.
	 *
	 * @return string
	 */
	public function getCookieName(): string
	{
		return $this->cookieName;
	}

	//--------------------------------------------------------------------

	/**
	 * Check if CSRF cookie is expired.
	 *
	 * @return boolean
	 */
	public function isExpired(): bool
	{
		return $this->expires === 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Check if request should be redirect on failure.
	 *
	 * @return boolean
	 */
	public function shouldRedirect(): bool
	{
		return $this->redirect;
	}

	//--------------------------------------------------------------------

	/**
	 * Sanitize Filename
	 *
	 * Tries to sanitize filenames in order to prevent directory traversal attempts
	 * and other security threats, which is particularly useful for files that
	 * were supplied via user input.
	 *
	 * If it is acceptable for the user input to include relative paths,
	 * e.g. file/in/some/approved/folder.txt, you can set the second optional
	 * parameter, $relative_path to TRUE.
	 *
	 * @param string  $str          Input file name
	 * @param boolean $relativePath Whether to preserve paths
	 *
	 * @return string
	 */
	public function sanitizeFilename(string $str, bool $relativePath = false): string
	{
		// List of sanitize filename strings
		$bad = [
			'../', '<!--', '-->', '<', '>', "'", '"', '&', '$', '#', '{', '}', '[', ']', '=', ';', '?',
			'%20', '%22', '%3c', '%253c', '%3e', '%0e', '%28', '%29', '%2528', '%26', '%24', '%3f', '%3b', '%3d',
		];

		if (! $relativePath)
		{
			$bad[] = './';
			$bad[] = '/';
		}

		$str = remove_invisible_characters($str, false);

		do
		{
			$old = $str;
			$str = str_replace($bad, '', $str);
		}
		while ($old !== $str);

		return stripslashes($str);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the CSRF Hash.
	 *
	 * @return string
	 */
	protected function generateHash(): string
	{
		if (is_null($this->hash))
		{
			// If the cookie exists we will use its value.
			// We don't necessarily want to regenerate it with
			// each page load since a page could contain embedded
			// sub-pages causing this feature to fail
			if (isset($_COOKIE[$this->cookieName]) && is_string($_COOKIE[$this->cookieName]) && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->cookieName]) === 1
			)
			{
				return $this->hash = $_COOKIE[$this->cookieName];
			}

			$this->hash = bin2hex(random_bytes(16));
		}

		return $this->hash;
	}

	//--------------------------------------------------------------------

	/**
	 * CSRF Send Cookie
	 *
	 * @param RequestInterface $request
	 *
	 * @return Security|false
	 * @codeCoverageIgnore
	 */
	protected function sendCookie(RequestInterface $request)
	{
		$config = new App();

		$expires = $this->isExpired() ? $this->expires : time() + $this->expires;
		$path    = $config->cookiePath ?? '/';
		$domain  = $config->cookieDomain ?? '';
		$secure  = $config->cookieSecure ?? false;

		if ($secure && ! $request->isSecure())
		{
			return false;
		}

		if (PHP_VERSION_ID < 70300)
		{
			// In PHP < 7.3.0, there is a "hacky" way to set the samesite parameter
			$samesite = '';
			
			if (! empty($this->samesite))
			{
				$samesite = '; samesite=' . $this->samesite;
			}

			setcookie($this->cookieName, $this->hash, $expires, $path . $samesite, $domain, $secure, true);
		}
		else
		{
			// PHP 7.3 adds another function signature allowing setting of samesite
			$params = [
				'expires'  => $expires,
				'path'     => $path,
				'domain'   => $domain,
				'secure'   => $secure,
				'httponly' => true, // Enforce HTTP only cookie for security
			];

			if (! empty($this->samesite))
			{
				$params['samesite'] = $this->samesite;
			}

			setcookie($this->cookieName, $this->hash, $params);
		}

		log_message('info', 'CSRF cookie sent.');

		return $this;
	}
}
