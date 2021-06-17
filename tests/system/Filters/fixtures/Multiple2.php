<?php

namespace CodeIgniter\Filters\fixtures;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Multiple2 implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $request->url = 'http://exampleMultipleURL.com';

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
