<?php

namespace CodeIgniter\Test\Mock;

use Config\Security as SecurityConfig;

class MockSecurityConfig extends SecurityConfig
{
    public $protection  = false;
    public $tokenName   = 'csrf_test_name';
    public $headerName  = 'X-CSRF-TOKEN';
    public $cookieName  = 'csrf_cookie_name';
    public $expire      = 7200;
    public $regenerate  = true;
    public $excludeURIs = ['http://example.com'];
    public $redirect    = false;
    public $samesite    = 'Lax';
}
