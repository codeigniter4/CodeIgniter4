<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * URI Factory
 *
 * Part of PSR-17, the URI Factory is necessary for
 * integrating the PSR-7 compliance tests but not
 * otherwise used in the framework yet.
 *
 * @see https://www.php-fig.org/psr/psr-17/
 */
class UriFactory implements UriFactoryInterface
{
	/**
	 * Create a new URI.
	 *
	 * @param string $uri The URI to parse.
	 *
	 * @throws \InvalidArgumentException If the given URI cannot be parsed.
	 */
	public function createUri(string $uri = ''): UriInterface
	{
		return new URI($uri);
	}
}
