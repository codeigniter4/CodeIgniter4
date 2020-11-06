<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Filters\Filters;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Mock Filters handler to prevent output during
 * unit testing (especially Feature Tests).
 */
class MockFilters extends Filters
{
	/**
	 * Runs through all of the filters for the specified
	 * uri and position.
	 *
	 * @param string $uri
	 * @param string $position
	 *
	 * @return RequestInterface|ResponseInterface|mixed
	 */
	public function run(string $uri, string $position = 'before')
	{
		$result = parent::run($uri, $position);

		if ($result instanceof ResponseInterface && ! $result instanceof RedirectResponse)
		{
			\ob_start();
		}

		return $result;
	}
}
