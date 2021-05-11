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

use CodeIgniter\Cache\CacheInterface;
use Config\Cache;

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
			$this->adapter = service('cache');
		}
		elseif ($object instanceof Cache)
		{
			$this->adapter = service('cache', $object, false);
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
