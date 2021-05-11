<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Psr\Cache;

use CodeIgniter\Cache\Handlers\BaseHandler;
use CodeIgniter\I18n\Time;
use DateInterval;
use DateTimeInterface;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

final class Item implements CacheItemInterface
{
	/**
	 * Reserved characters that cannot be used in a key or tag.
	 *
	 * @see https://github.com/symfony/cache-contracts/blob/c0446463729b89dd4fa62e9aeecc80287323615d/ItemInterface.php#L43
	 */
	public const RESERVED_CHARACTERS = '{}()/\@:';

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * Whether this Item was the result
	 * of a cache hit.
	 *
	 * @var boolean
	 */
	protected $hit;

	/**
	 * The expiration time
	 *
	 * @var Time|null
	 */
	protected $expiration;

	/**
	 * Validates a cache key according to PSR-6.
	 *
	 * @param mixed $key The key to validate
	 *
	 * @throws CacheArgumentException When $key is not valid
	 */
	public static function validateKey($key)
	{
		// Use the framework's Cache key validation
		try
		{
			BaseHandler::validateKey($key);
		}
		catch (InvalidArgumentException $e)
		{
			throw new CacheArgumentException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * Stores the Item's details.
	 *
	 * @param string  $key
	 * @param mixed   $value
	 * @param boolean $hit
	 */
	public function __construct(string $key, $value, bool $hit)
	{
		$this->key   = $key;
		$this->value = $value;
		$this->hit   = $hit;
	}

	/**
	 * Returns the key for the current cache item.
	 *
	 * The key is loaded by the Implementing Library, but should be available to
	 * the higher level callers when needed.
	 *
	 * @return string
	 *   The key string for this cache item.
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * Retrieves the value of the item from the cache associated with this object's key.
	 *
	 * The value returned must be identical to the value originally stored by set().
	 *
	 * If isHit() returns false, this method MUST return null. Note that null
	 * is a legitimate cached value, so the isHit() method SHOULD be used to
	 * differentiate between "null value was found" and "no value was found."
	 *
	 * @return mixed
	 *   The value corresponding to this cache item's key, or null if not found.
	 */
	public function get()
	{
		return $this->value;
	}

	/**
	 * Confirms if the cache item lookup resulted in a cache hit.
	 *
	 * Note: This method MUST NOT have a race condition between calling isHit()
	 * and calling get().
	 *
	 * @return boolean
	 *   True if the request resulted in a cache hit. False otherwise.
	 */
	public function isHit(): bool
	{
		return $this->hit;
	}

	/**
	 * Sets the value represented by this cache item.
	 *
	 * The $value argument may be any item that can be serialized by PHP,
	 * although the method of serialization is left up to the Implementing
	 * Library.
	 *
	 * @param mixed $value
	 *   The serializable value to be stored.
	 *
	 * @return static
	 *   The invoked object.
	 */
	public function set($value): self
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * Sets the expiration time for this cache item.
	 *
	 * @param DateTimeInterface|null $expiration
	 *   The point in time after which the item MUST be considered expired.
	 *   If null is passed explicitly, a default value MAY be used. If none is set,
	 *   the value should be stored permanently or for as long as the
	 *   implementation allows.
	 *
	 * @return static
	 *   The called object.
	 */
	public function expiresAt($expiration): self
	{
		if ($expiration === null)
		{
			$this->expiration = null;
		}
		elseif ($expiration instanceof DateTimeInterface)
		{
			$this->expiration = Time::createFromInstance($expiration);
		}
		else
		{
			throw new CacheArgumentException('Expiration date must be a DateTimeInterface or null');
		}

		return $this;
	}

	/**
	 * Sets the expiration time for this cache item.
	 *
	 * @param integer|DateInterval|null $time
	 *   The period of time from the present after which the item MUST be considered
	 *   expired. An integer parameter is understood to be the time in seconds until
	 *   expiration. If null is passed explicitly, a default value MAY be used.
	 *   If none is set, the value should be stored permanently or for as long as the
	 *   implementation allows.
	 *
	 * @return static
	 *   The called object.
	 */
	public function expiresAfter($time): self
	{
		if ($time === null)
		{
			$this->expiration = null;
		}
		elseif ($time instanceof DateInterval)
		{
			$this->expiration = Time::now()->add($time);
		}
		elseif (is_int($time))
		{
			$this->expiration = Time::now()->addSeconds($time);
		}
		else
		{
			throw new CacheArgumentException('Expiration date must be an integer, a DateInterval or null');
		}

		return $this;
	}

	/**
	 * Returns the expiration Time.
	 * This method is not a requirement of PSR-6 but is necessary
	 * to pass "testExpiration".
	 *
	 * @see https://groups.google.com/g/php-fig/c/Qr4OxCf7J5Y
	 *
	 * @return Time|null
	 */
	public function getExpiration(): ?Time
	{
		return $this->expiration;
	}

	/**
	 * Returns whether or not this Item is expired.
	 * This method is not a requirement of PSR-6 but is necessary
	 * to pass "testSavedExpired".
	 *
	 * @see https://groups.google.com/g/php-fig/c/Qr4OxCf7J5Y
	 *
	 * @return boolean True if this Item is expired.
	 */
	public function isExpired(): bool
	{
		if (isset($this->expiration))
		{
			$now = Time::now();
			return $this->expiration->isBefore($now) || $this->expiration->sameAs($now);
		}

		return false;
	}

	/**
	 * Sets the hit value.
	 * This method is not a requirement of PSR-6 but is necessary
	 * to allow deferred items to count as hits.
	 *
	 * @return $this
	 */
	public function setHit(bool $hit): self
	{
		$this->hit = $hit;

		return $this;
	}
}
