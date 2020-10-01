<?php

namespace CodeIgniter\Test\Mock;

use Config\Security as SecurityConfig;

class MockSecurityConfig extends SecurityConfig
{
    public $CSRFProtection  = false;
    public $CSRFTokenName   = 'csrf_test_name';
    public $CSRFHeaderName  = 'X-CSRF-TOKEN';
    public $CSRFCookieName  = 'csrf_cookie_name';
    public $CSRFExpire      = 7200;
    public $CSRFRegenerate  = true;
    public $CSRFExcludeURIs = ['http://example.com'];
    public $CSRFRedirect    = false;
    public $CSRFSameSite    = 'Lax';
}
