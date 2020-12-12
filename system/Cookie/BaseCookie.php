<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie;

use CodeIgniter\Cookie\Exceptions\CookieException;

abstract class BaseCookie
{
	/**
	 * Cookie Prefix
	 *
	 * Set a cookie name prefix to avoid collisions.
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Cookie Path
	 *
	 * Typically will be a forward slash.
	 *
	 * @var string
	 */
	protected $path = '/';

	/**
	 * Cookie Domain
	 *
	 * Set to `.example-domain.com` for site-wide cookies.
	 *
	 * @var string
	 */
	protected $domain = '';

	/**
	 * Cookie Secure
	 *
	 * Transmit the cookie over a secure HTTPS connection only.
	 *
	 * @var boolean
	 */
	protected $secure = false;

	/**
	 * Cookie HTTP Only
	 *
	 * Make the cookie accessible only through the HTTP protocol (no JavaScript)
	 *
	 * @var boolean
	 */
	protected $httponly = false;

	/**
	 * Cookie SameSite
	 *
	 * Setting for cookie SameSite.
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
	 * @param $config
	 * 
	 * @throws CookieException
	 */
	public function __construct($config)
	{
		$config = get_object_vars($config);

		foreach (get_class_vars(get_class($this)) as $key => $value)
		{
			if (property_exists($this, $key) && isset($config[$key]))
			{
				$this->$key = $config[$key];
			}
		}

		if (! in_array(strtolower($this->samesite), ['none', 'lax', 'strict', ''], true))
		{
			throw CookieException::forInvalidSameSite($this->samesite);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set cookie prefix.
	 *
	 * @param string $prefix
	 *
	 * @return $this
	 */
	public function setPrefix(string $prefix)
	{
		$this->prefix = $prefix;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie prefix.
	 *
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Set cookie path.
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public function setPath(string $path)
	{
		$this->path = $path;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie path.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	//--------------------------------------------------------------------

	/**
	 * Set cookie domain.
	 *
	 * @param string $domain
	 *
	 * @return $this
	 */
	public function setDomain(string $domain)
	{
		$this->domain = $domain;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie domain.
	 *
	 * @return string
	 */
	public function getDomain(): string
	{
		return $this->domain;
	}

	//--------------------------------------------------------------------

	/**
	 * Set cookie secure.
	 *
	 * @param boolean $secure
	 *
	 * @return $this
	 */
	public function setSecure(bool $secure = false)
	{
		$this->secure = $secure;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Check cookie secure status.
	 *
	 * @return boolean
	 */
	public function isSecure(): bool
	{
		return $this->secure;
	}

	//--------------------------------------------------------------------

	/**
	 * Set cookie httponly.
	 *
	 * @param boolean $httponly
	 *
	 * @return $this
	 */
	public function setHTTPOnly(bool $httponly = false)
	{
		$this->httponly = $httponly;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Check cookie httponly status.
	 *
	 * @return boolean
	 */
	public function isHTTPOnly(): bool
	{
		return $this->httponly;
	}

	//--------------------------------------------------------------------

	/**
	 * Set cookie samesite.
	 *
	 * @param string $samesite
	 *
	 * @return $this
	 */
	public function setSameSite(string $samesite)
	{
		$this->samesite = $samesite;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie samesite.
	 *
	 * @return string
	 */
	public function getSameSite(): string
	{
		return $this->samesite;
	}
}
