<?php namespace CodeIgniter\HTTP;

/**
 * Class MockResponse
 */
class MockResponse extends Response
{
    public function setCookie(
        $name,
        $value = '',
        $expire = '',
        $domain = '',
        $path = '/',
        $prefix = '',
        $secure = false,
        $httponly = false
    )
    {
        $_COOKIE[$name] = $value;

        //TODO: Find a way to use setcookie() without it throwing header issues.
        //setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    //--------------------------------------------------------------------

    public function hasCookie(string $name): bool
    {
        return array_key_exists($name, $_COOKIE);
    }

}
