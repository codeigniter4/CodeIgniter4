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
use CodeIgniter\Cache\CacheInterface;
use Config\Cache;
use Config\Services;
use InvalidArgumentException;

/**
 * Cache Support Trait
 *
 * Provides methods common to both
 * PSR-6 and PSR-16 drivers.
 */
trait SupportTrait
{
	/**
	 * The adapter to use.
	 *
	 * @var CacheInterface
	 */
	private $adapter;

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
	 * Initializes the underlying adapter
	 * from an existing instance or from the
	 * Cache Service (with optional config).
	 *
	 * @param object|null $object
	 *
	 * @throws CacheArgumentException
	 */
	public function __construct($object = null)
	{
		if (is_null($object))
		{
			$this->adapter = Services::cache();
		}
		elseif ($object instanceof Cache)
		{
			$this->adapter = Services::cache($object, false);
		}
		elseif ($object instanceof CacheInterface)
		{
			$this->adapter = $object;
		}
		else
		{
			throw new CacheArgumentException(get_class() . ' constructor only accepts an adapter or configuration');
		}
	}
}
