<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cookie\Collection;

use CodeIgniter\Cookie\Exceptions\CookieException;
use Config\Cookie as CookieConfig;

abstract class BaseCookie
{
	/**
	 * Configuration Properties
	 *
	 * @var array
	 */
	protected $properties;

	/**
	 * Cookie Prefix
	 *
	 * Set a cookie name prefix to avoid collisions.
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Cookie Expires
	 *
	 * Default expires timestamp for cookie.
	 *
	 * @var integer
	 */
	protected $expires = 0;

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
	 * Setting for cookie samesite.
	 *
	 * Allowed values are: [None - Lax - Strict].
	 *
	 * Defaults: `Lax` as recommended in this link:
	 * @see https://portswigger.net/web-security/csrf/samesite-cookies
	 *
	 * @var string
	 */
	protected $samesite = 'Lax';

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param CookieConfig $config
	 * 
	 * @throws CookieException
	 */
	public function __construct(CookieConfig $config)
	{
		$this->properties = get_object_vars($config);

		foreach (get_object_vars($this) as $key => $value)
		{
			if (isset($this->properties[$key]))
			{
				$this->{$key} = $this->properties[$key];
			}
		}

		if (! in_array(strtolower($this->samesite), ['none', 'lax', 'strict'], true))
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
	public function setSecure(bool $secure)
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
	public function setHTTPOnly(bool $httponly)
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
	 * Set value to the default configuration, if $samesite is invalid.
	 * 
	 * @param string $samesite
	 *
	 * @return $this
	 */
	public function setSamesite(string $samesite)
	{
		if (! in_array(strtolower($samesite), ['none', 'lax', 'strict'], true))
		{
			$samesite = $this->properties['samesite'];
		}

		$this->samesite = ucfirst(strtolower($samesite));

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie samesite.
	 *
	 * @return string
	 */
	public function getSamesite(): string
	{
		return $this->samesite;
	}

	//--------------------------------------------------------------------

	/**
	 * Reset configuration to default.
	 *
	 * @return $this
	 */
	public function reset()
	{
		foreach (get_object_vars($this) as $key => $value)
		{
			if (isset($this->properties[$key]))
			{
				$this->{$key} = $this->properties[$key];
			}
		}

		return $this;
	}
}
