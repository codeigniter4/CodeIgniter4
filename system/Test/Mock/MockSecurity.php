<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Security;

class MockSecurity extends Security
{
    protected function sendCookie(RequestInterface $request)
    {
        $_COOKIE['csrf_cookie_name'] = $this->hash;

        return $this;
    }
}
