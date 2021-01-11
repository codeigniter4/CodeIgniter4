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

class MockCookieConfig
{
	public $prefix   = '';
	public $expires  = 0;
	public $path     = '/';
	public $domain   = '';
	public $secure   = false;
	public $httponly = false;
	public $samesite = 'Lax';
}
