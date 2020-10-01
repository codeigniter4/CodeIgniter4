<?php

namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Security;

class MockSecurity extends Security
{
	public function sendCookie(RequestInterface $request)
	{
		$_COOKIE['csrf_cookie_name'] = $this->hash;

		return $this;
	}
}
