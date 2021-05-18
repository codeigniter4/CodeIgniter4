<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use Closure;
use CodeIgniter\Cache\CacheInterface;
use Exception;
use InvalidArgumentException;

/**
 * Base class for cache handling
 */
abstract class BaseHandler implements CacheInterface
{
	/**
	 * Reserved characters that cannot be used in a key or tag.
	 * From https://github.com/symfony/cache-contracts/blob/c0446463729b89dd4fa62e9aeecc80287323615d/ItemInterface.php#L43
	 */
	public const RESERVED_CHARACTERS = '{}()/\@:';

	/**
	 * Maximum key length.
	 */
	public const MAX_KEY_LENGTH = PHP_INT_MAX;

	/**
	 * Prefix to apply to cache keys.
	 * May not be used by all handlers.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Validates a cache key according to PSR-6.
	 * Keys that exceed MAX_KEY_LENGTH are hashed.
	 * From https://github.com/symfony/cache/blob/7b024c6726af21fd4984ac8d1eae2b9f3d90de88/CacheItem.php#L158
	 *
	 * @param string $key    The key to validate
	 * @param string $prefix Optional prefix to include in length calculations
	 *
	 * @throws InvalidArgumentException When $key is not valid
	 */
	public static function validateKey($key, $prefix = ''): string
	{
		if (! is_string($key))
		{
			throw new InvalidArgumentException('Cache key must be a string');
		}
		if ($key === '')
		{
			throw new InvalidArgumentException('Cache key cannot be empty.');
		}
		if (strpbrk($key, self::RESERVED_CHARACTERS) !== false)
		{
			throw new InvalidArgumentException('Cache key contains reserved characters ' . self::RESERVED_CHARACTERS);
		}

		// If the key with prefix exceeds the length then return the hashed version
		return strlen($prefix . $key) > static::MAX_KEY_LENGTH ? $prefix . md5($key) : $prefix . $key;
	}

	//--------------------------------------------------------------------

	/**
	 * Get an item from the cache, or execute the given Closure and store the result.
	 *
	 * @param string  $key      Cache item name
	 * @param integer $ttl      Time to live
	 * @param Closure $callback Callback return value
	 *
	 * @return mixed
	 */
	public function remember(string $key, int $ttl, Closure $callback)
	{
		$value = $this->get($key);

		if (! is_null($value))
		{
			return $value;
		}

		$this->save($key, $value = $callback(), $ttl);

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes items from the cache store matching a given pattern.
	 *
	 * @param string $pattern Cache items glob-style pattern
	 *
	 * @throws Exception
	 */
	public function deleteMatching(string $pattern)
	{
		throw new Exception('The deleteMatching method is not implemented.');
	}
}
