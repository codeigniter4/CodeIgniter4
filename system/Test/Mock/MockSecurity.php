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

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Security;

class MockSecurity extends Security
{
	public function CSRFSetCookie(RequestInterface $request)
	{
		$_COOKIE['csrf_cookie_name'] = $this->CSRFHash;

		return $this;
	}

	//--------------------------------------------------------------------

}
