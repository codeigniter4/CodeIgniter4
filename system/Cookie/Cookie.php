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

class Cookie implements CookieInterface
{
	/**
	 * The cookie name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The cookie name.
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * The cookie expires.
	 *
	 * @var integer
	 */
	protected $expires;

	/**
	 * The cookie path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The cookie domain.
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * The cookie secure.
	 *
	 * @var boolean
	 */
	protected $secure;

	/**
	 * The cookie httponly.
	 *
	 * @var boolean
	 */
	protected $httponly;

	/**
	 * The cookie samesite.
	 *
	 * @var string|null
	 */
	protected $samesite;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param string       $name     The cookie name
	 * @param string       $value    The cookie value
	 * @param integer      $expires  The cookie expiration time
	 * @param string       $path     The cookie path
	 * @param string       $domain   The cookie domain
	 * @param boolean      $secure   Whether to transfer the cookie over a SSL only
	 * @param boolean      $httponly Whether to access the cookie through HTTP only
	 * @param string       $samesite The cookie samesite
	 */
	public function __construct(
		string $name,
		string $value = '',
		int $expires = 0,
		string $path = '/',
		string $domain = '',
		bool $secure = false,
		bool $httponly = false,
		string $samesite = ''
	)
	{
		$this->name 	= $name;
		$this->value 	= $value;
		$this->expires  = $expires;
		$this->path 	= $path;
		$this->domain 	= $domain;
		$this->secure 	= $secure;
		$this->httponly = $httponly;
		$this->samesite = $samesite;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie value.
	 *
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	//--------------------------------------------------------------------

	/**
	 * Get cookie expires.
	 *
	 * @return integer
	 */
	public function getExpires(): int
	{
		return $this->expires;
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
	 * Get cookie samesite.
	 *
	 * @return string
	 */
	public function getSameSite(): string
	{
		return $this->samesite;
	}
}
