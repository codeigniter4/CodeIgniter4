<?php

namespace CodeIgniter\Filters\fixtures;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Multiple1 implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $request->csp = 'http://exampleMultipleCSP.com';

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
