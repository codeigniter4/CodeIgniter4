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

/**
 * Base class for cache handling
 */
abstract class BaseHandler implements CacheInterface
{
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
}
