<?php

namespace CodeIgniter\Filters\fixtures;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class GoogleEmpty implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return '';
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
