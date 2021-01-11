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

use Config\Cookie as CookieConfig;

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
	 * @var string
	 */
	protected $samesite;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param string       $name     The cookie name
	 * @param string       $value    The cookie value
	 * @param integer|null $expires  The cookie expiration time
	 * @param string|null  $path     The cookie path
	 * @param string|null  $domain   The cookie domain
	 * @param boolean|null $secure   Whether to transfer the cookie over a SSL only
	 * @param boolean|null $httponly Whether to access the cookie through HTTP only
	 * @param string|null  $samesite The cookie samesite
	 */
	public function __construct(
		string $name,
		string $value = '',
		int $expires = null,
		string $path = null,
		string $domain = null,
		bool $secure = null,
		bool $httponly = null,
		string $samesite = null
	)
	{
		$this->name 	= $name;
		$this->value 	= $value;
		$this->expires  = $expires ?? self::getDefault('expires');
		$this->path 	= $path ?? self::getDefault('path');
		$this->domain 	= $domain ?? self::getDefault('domain');
		$this->secure 	= $secure ?? self::getDefault('secure');
		$this->httponly = $httponly ?? self::getDefault('httponly');
		$this->samesite = $samesite ?? self::getDefault('samesite');
	}

	/**
	 * Create Cookie object.
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $options
	 *
	 * @return static
	 */
	public static function pump(string $name, string $value = '', array $options = [])
	{
		$options += [
			'expires'  => self::getDefault('expires'),
			'path'     => self::getDefault('path'),
			'domain'   => self::getDefault('domain'),
			'secure'   => self::getDefault('secure'),
			'httponly' => self::getDefault('httponly'),
			'samesite' => self::getDefault('samesite'),
		];

		return new static(
			$name,
			$value,
			$options['expires'],
			$options['path'],
			$options['domain'],
			$options['secure'],
			$options['httponly'],
			$options['samesite']
		);
	}

	/**
	 * Returns Cookie object.
	 *
	 * @return array
	 */
	public function dump(): array
	{
		return [
			'name'     => $this->getName(),
			'value'    => $this->getValue(),
			'expires'  => $this->getExpires(),
			'path'     => $this->getPath(),
			'domain'   => $this->getDomain(),
			'secure'   => $this->isSecure(),
			'httponly' => $this->isHTTPOnly(),
			'samesite' => $this->getSamesite(),
		];
	}

	/**
	 * Get cookie name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get cookie value.
	 *
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * Get cookie expires.
	 *
	 * @return integer
	 */
	public function getExpires(): int
	{
		return $this->expires;
	}

	/**
	 * Get cookie path.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Get cookie domain.
	 *
	 * @return string
	 */
	public function getDomain(): string
	{
		return $this->domain;
	}

	/**
	 * Check cookie secure status.
	 *
	 * @return boolean
	 */
	public function isSecure(): bool
	{
		return $this->secure;
	}

	/**
	 * Check cookie httponly status.
	 *
	 * @return boolean
	 */
	public function isHTTPOnly(): bool
	{
		return $this->httponly;
	}

	/**
	 * Get cookie samesite.
	 *
	 * @return string
	 */
	public function getSamesite(): string
	{
		return $this->samesite;
	}

	/**
	 * Creates a new Cookie with a new "name".
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function withName(string $name): CookieInterface
	{
		$cookie = clone $this;

		$cookie->name = $name;

		return $cookie;
	}

	/**
	 * Creates a new Cookie with a new "value".
	 *
	 * @param string $value
	 *
	 * @return $this
	 */
	public function withValue(string $value): CookieInterface
	{
		$cookie = clone $this;

		$cookie->value = $value;

		return $cookie;
	}

	/**
	 * Creates a new Cookie with a new "expires".
	 *
	 * @param integer $expires
	 *
	 * @return $this
	 */
	public function withExpires(int $expires = 0): CookieInterface
	{
		$cookie = clone $this;

		$cookie->expires = $expires;

		return $cookie;
	}

	/**
	 * Creates a new Cookie with a new "path".
	 *
	 * @param string|null $path
	 *
	 * @return $this
	 */
	public function withPath(?string $path): CookieInterface
	{
		$cookie = clone $this;

		$cookie->path = $path;

		return $cookie;
	}

	/**
	 * Creates a new Cookie with a new "domain".
	 *
	 * @param string|null $domain
	 *
	 * @return $this
	 */
	public function withDomain(?string $domain): CookieInterface
	{
		$cookie = clone $this;

		$cookie->domain = $domain;

		return $cookie;
	}

	/**
	 * Creates a new Cookie with a new "secure".
	 *
	 * @param boolean $secure
	 *
	 * @return $this
	 */
	public function withSecure(bool $secure = true): CookieInterface
	{
		$cookie = clone $this;

		$cookie->secure = $secure;

		return $cookie;
	}

	/**
	 * Creates a new Cookie with a new "httponly".
	 *
	 * @param boolean $httponly
	 *
	 * @return $this
	 */
	public function withHTTPOnly(bool $httponly = true): CookieInterface
	{
		$cookie = clone $this;

		$cookie->httponly = $httponly;

		return $cookie;
	}

	/**
	 * Creates a new Cookie with a new "samesite".
	 *
	 * @param string $samesite
	 *
	 * @return $this
	 */
	public function withSamesite(string $samesite): CookieInterface
	{
		$cookie = clone $this;

		$cookie->samesite = $samesite;

		return $cookie;
	}

	/**
	 * Returns default cookie specific property value.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	protected static function getDefault(string $property)
	{
		$config = new CookieConfig();

		return $config->{$property};
	}
}
