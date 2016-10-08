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
        if (is_array($name))
        {
            foreach
            (
                [
                    'value', 
                    'expire', 
                    'domain', 
                    'path', 
                    'prefix', 
                    'secure', 
                    'httponly', 
                    'name'
                ] as $item
            )
            {
                if (isset($name[$item]))
                {
                    $$item = $name[$item];
                }
            }
        }


        $_COOKIE[$prefix . $name] = $value;

        /*
            TODO: Find a way to use setcookie() 
            without it throwing header issues.
            setcookie
            (
                $prefix.$name, 
                $value, 
                $expire, 
                $path, 
                $domain, 
                $secure, 
                $httponly
            );
        */
    }

    //--------------------------------------------------------------------

    public function hasCookie(string $name): bool
    {
        return array_key_exists($name, $_COOKIE);
    }

    //--------------------------------------------------------------------

    public function deleteCookie
    (
        $name, 
        string $domain = '', 
        string $path   = '/', 
        string $prefix = ''
    )
    {
        $COOKIE[$name] = null;
        unset($COOKIE[$name]);
        
        //set_cookie($name, '', '', $domain, $path, $prefix);
    }

}
