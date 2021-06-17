<?php

namespace CodeIgniter\Test\Mock;

use Config\Security as Security;

/**
 * @deprecated
 *
 * @codeCoverageIgnore
 */
class MockSecurityConfig extends Security
{
    public $tokenName   = 'csrf_test_name';
    public $headerName  = 'X-CSRF-TOKEN';
    public $cookieName  = 'csrf_cookie_name';
    public $expires     = 7200;
    public $regenerate  = true;
    public $redirect    = false;
    public $samesite    = 'Lax';
    public $excludeURIs = ['http://example.com'];
}
