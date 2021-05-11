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

use InvalidArgumentException;
use Psr\Cache\InvalidArgumentException as CacheException;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheException;

final class CacheArgumentException extends InvalidArgumentException implements CacheException, SimpleCacheException
{
}
