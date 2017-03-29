<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request)
    {
        $auth = service('auth');

        if (! $auth->isLoggedIn())
        {
            return redirect('login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response)
    {

    }

}
