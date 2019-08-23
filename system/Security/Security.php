<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Security;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;

/**
 * HTTP security handler.
 */
class Security
{

	/**
	 * CSRF Hash
	 *
	 * Random hash for Cross Site Request Forgery protection cookie
	 *
	 * @var string
	 */
	protected $CSRFHash;

	/**
	 * CSRF Expire time
	 *
	 * Expiration time for Cross Site Request Forgery protection cookie.
	 * Defaults to two hours (in seconds).
	 *
	 * @var integer
	 */
	protected $CSRFExpire = 7200;

	/**
	 * CSRF Token name
	 *
	 * Token name for Cross Site Request Forgery protection cookie.
	 *
	 * @var string
	 */
	protected $CSRFTokenName = 'CSRFToken';

	/**
	 * CSRF Cookie name
	 *
	 * Cookie name for Cross Site Request Forgery protection cookie.
	 *
	 * @var string
	 */
	protected $CSRFCookieName = 'CSRFToken';

	/**
	 * CSRF Regenerate
	 *
	 * If true, the CSRF Token will be regenerated on every request.
	 * If false, will stay the same for the life of the cookie.
	 *
	 * @var boolean
	 */
	protected $CSRFRegenerate = true;

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
	 * @var boolean
	 */
	protected $cookieSecure = false;

	/**
	 * List of sanitize filename strings
	 *
	 * @var array
	 */
	public $filenameBadChars = [
		'../',
		'<!--',
		'-->',
		'<',
		'>',
		"'",
		'"',
		'&',
		'$',
		'#',
		'{',
		'}',
		'[',
		']',
		'=',
		';',
		'?',
		'%20',
		'%22',
		'%3c', // <
		'%253c', // <
		'%3e', // >
		'%0e', // >
		'%28', // (
		'%29', // )
		'%2528', // (
		'%26', // &
		'%24', // $
		'%3f', // ?
		'%3b', // ;
		'%3d',       // =
	];

	//--------------------------------------------------------------------

	/**
	 * Security constructor.
	 *
	 * Stores our configuration and fires off the init() method to
	 * setup initial state.
	 *
	 * @param \Config\App $config
	 *
	 * @throws \Exception
	 */
	public function __construct($config)
	{
		// Store our CSRF-related settings
		$this->CSRFExpire     = $config->CSRFExpire;
		$this->CSRFTokenName  = $config->CSRFTokenName;
		$this->CSRFCookieName = $config->CSRFCookieName;
		$this->CSRFRegenerate = $config->CSRFRegenerate;

		if (isset($config->cookiePrefix))
		{
			$this->CSRFCookieName = $config->cookiePrefix . $this->CSRFCookieName;
		}

		// Store cookie-related settings
		$this->cookiePath   = $config->cookiePath;
		$this->cookieDomain = $config->cookieDomain;
		$this->cookieSecure = $config->cookieSecure;

		$this->CSRFSetHash();

		unset($config);
	}

	//--------------------------------------------------------------------

	/**
	 * CSRF Verify
	 *
	 * @param RequestInterface $request
	 *
	 * @return $this|false
	 * @throws \Exception
	 */
	public function CSRFVerify(RequestInterface $request)
	{
		// If it's not a POST request we will set the CSRF cookie
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
		{
			return $this->CSRFSetCookie($request);
		}

		// Do the tokens exist in both the _POST and _COOKIE arrays?
		if (! isset($_POST[$this->CSRFTokenName], $_COOKIE[$this->CSRFCookieName]) || $_POST[$this->CSRFTokenName] !== $_COOKIE[$this->CSRFCookieName]
		) // Do the tokens match?
		{
			throw SecurityException::forDisallowedAction();
		}

		// We kill this since we're done and we don't want to pollute the _POST array
		unset($_POST[$this->CSRFTokenName]);

		// Regenerate on every submission?
		if ($this->CSRFRegenerate)
		{
			// Nothing should last forever
			unset($_COOKIE[$this->CSRFCookieName]);
		}

		$this->CSRFSetHash();
		$this->CSRFSetCookie($request);

		log_message('info', 'CSRF token verified');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * CSRF Set Cookie
	 *
	 * @codeCoverageIgnore
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return Security|false
	 */
	public function CSRFSetCookie(RequestInterface $request)
	{
		$expire        = time() + $this->CSRFExpire;
		$secure_cookie = (bool) $this->cookieSecure;

		if ($secure_cookie && ! $request->isSecure())
		{
			return false;
		}

		setcookie(
				$this->CSRFCookieName, $this->CSRFHash, $expire, $this->cookiePath, $this->cookieDomain, $secure_cookie, true                // Enforce HTTP only cookie for security
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
	public function getCSRFHash(): string
	{
		return $this->CSRFHash;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the CSRF Token Name.
	 *
	 * @return string
	 */
	public function getCSRFTokenName(): string
	{
		return $this->CSRFTokenName;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the CSRF Hash and cookie.
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function CSRFSetHash(): string
	{
		if ($this->CSRFHash === null)
		{
			// If the cookie exists we will use its value.
			// We don't necessarily want to regenerate it with
			// each page load since a page could contain embedded
			// sub-pages causing this feature to fail
			if (isset($_COOKIE[$this->CSRFCookieName]) && is_string($_COOKIE[$this->CSRFCookieName]) && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->CSRFCookieName]) === 1
			)
			{
				return $this->CSRFHash = $_COOKIE[$this->CSRFCookieName];
			}

			$rand           = random_bytes(16);
			$this->CSRFHash = bin2hex($rand);
		}

		return $this->CSRFHash;
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
	 * @param string  $str           Input file name
	 * @param boolean $relative_path Whether to preserve paths
	 *
	 * @return string
	 */
	public function sanitizeFilename(string $str, bool $relative_path = false): string
	{
		$bad = $this->filenameBadChars;

		if (! $relative_path)
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
}
